import java.sql.*;
import java.util.*;

public class Scheduler {
    private List<Section> sections = new ArrayList<>();
    private List<Subject> subjects = new ArrayList<>();
    private List<Room> rooms = new ArrayList<>();
    private List<Timeslot> timeslots = new ArrayList<>();

    private List<ScheduleEntry> schedule = new ArrayList<>();
    private List<String> unscheduled = new ArrayList<>();

    // Partner day mappings
    private static final Map<String, String> PARTNER_DAYS = new HashMap<>();
    static {
        PARTNER_DAYS.put("Monday", "Thursday");
        PARTNER_DAYS.put("Thursday", "Monday");
        PARTNER_DAYS.put("Tuesday", "Friday");
        PARTNER_DAYS.put("Friday", "Tuesday");
        PARTNER_DAYS.put("Wednesday", "Saturday");
        PARTNER_DAYS.put("Saturday", "Wednesday");
    }

    // Inner class to represent a schedule entry
    class ScheduleEntry {
        String sectionName;
        String subjectCode;
        String subjectName;
        String type;
        String roomName;
        String timeSlot;
        String day;
        double duration;

        public ScheduleEntry(String sectionName, String subjectCode, String subjectName,
                             String type, String roomName, String timeSlot, String day, double duration) {
            this.sectionName = sectionName;
            this.subjectCode = subjectCode;
            this.subjectName = subjectName;
            this.type = type;
            this.roomName = roomName;
            this.timeSlot = timeSlot;
            this.day = day;
            this.duration = duration;
        }
    }

    public void loadData() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            // Load Sections
            ResultSet rs = conn.createStatement().executeQuery("SELECT * FROM sections");
            while (rs.next()) {
                Section s = new Section(
                        rs.getInt("SectionID"),
                        rs.getString("SectionName"),
                        rs.getInt("ProgramID"),
                        rs.getInt("YearLevelID")
                );
                sections.add(s);
            }

            // Load Subjects
            rs = conn.createStatement().executeQuery("SELECT * FROM term2courses");
            while (rs.next()) {
                Subject sub = new Subject(
                        rs.getInt("SubjectID"),
                        rs.getString("SubjectCode"),
                        rs.getString("SubjectName"),
                        rs.getBoolean("IsLab") ? "LAB" : "LEC",
                        rs.getDouble("TotalTime"),
                        rs.getInt("YearLevelID"),
                        rs.getInt("ProgramID")
                );
                subjects.add(sub);
            }

            // Load Rooms
            rs = conn.createStatement().executeQuery("SELECT * FROM rooms");
            while (rs.next()) {
                Room r = new Room(
                        rs.getInt("RoomID"),
                        rs.getString("RoomName"),
                        rs.getString("RoomType")
                );
                rooms.add(r);
            }

            // Load Timeslots
            rs = conn.createStatement().executeQuery("SELECT * FROM timeslots");
            while (rs.next()) {
                Timeslot t = new Timeslot(
                        rs.getInt("TimeSlotID"),
                        rs.getString("StartTime"),
                        rs.getString("EndTime")
                );
                timeslots.add(t);
            }

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    public void generateSchedule() {
        // Clear previous data
        sections.clear();
        subjects.clear();
        rooms.clear();
        timeslots.clear();
        schedule.clear();
        unscheduled.clear();

        loadData();

        // Sort sections by year level first (1st to 4th), then by program priority
        sections.sort((a, b) -> {
            // Primary sort: Year level (1st > 2nd > 3rd > 4th)
            if (a.yearLevelId != b.yearLevelId) {
                return a.yearLevelId - b.yearLevelId; // Lower year first
            }
            // Secondary sort: Program priority within same year level
            int progPriorityA = getProgramPriority(a.programId);
            int progPriorityB = getProgramPriority(b.programId);
            return progPriorityA - progPriorityB;
        });

        Set<String> used = new HashSet<>(); // room+slot+day usage
        Set<String> sectionSlotDayUsage = new HashSet<>(); // section+slot+day usage

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        for (Section section : sections) {
            for (Subject subject : subjects) {
                if (!Objects.equals(section.programId, subject.programId)) continue;
                if (section.yearLevelId != subject.yearLevelId) continue;

                boolean placed = false;

                // Special handling for Year 1 courses
                if (section.yearLevelId == 1) {
                    if (subject.totalTime < 2.0) {
                        // Hybrid scheduling: one physical session + one online session on partner days
                        placed = tryPlaceHybridSchedule(section, subject, used, sectionSlotDayUsage, days);
                    } else if (subject.totalTime > 2.0) {
                        // Split across partner days: physical + virtual
                        placed = tryPlaceYear1PartnerDays(section, subject, used, sectionSlotDayUsage, days);
                    } else {
                        // Regular single day scheduling (totalTime == 2.0)
                        placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                    }
                }
                // Special handling for Year 2 courses with totalTime < 2
                else if (section.yearLevelId == 2 && subject.totalTime < 2.0) {
                    // Hybrid scheduling: one physical session + one online session on partner days
                    placed = tryPlaceHybridSchedule(section, subject, used, sectionSlotDayUsage, days);
                }
                // Regular scheduling for all other cases
                else if (subject.totalTime > 2.0) {
                    // Long courses: split across partner days (both physical)
                    placed = tryPlaceOnPartnerDays(section, subject, used, sectionSlotDayUsage, days);
                } else {
                    // Regular single day scheduling (physical only)
                    placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                }

                if (!placed) {
                    unscheduled.add(subject.code + " (" + section.name + ") - " + subject.type);
                }
            }
        }

        // Save schedule to database
        saveScheduleToDatabase();

        printSchedule();
    }

    private void saveScheduleToDatabase() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed! Cannot save schedule.");
                return;
            }

            // Clear existing schedule data
            conn.createStatement().executeUpdate("DELETE FROM schedule");

            // Prepare insert statement with duration_id and day_id
            String insertQuery = """
            INSERT INTO schedule (SectionID, SubjectID, RoomID, TimeSlotID, duration_id, day_id) 
            VALUES (?, ?, ?, ?, ?, ?)
            """;

            PreparedStatement ps = conn.prepareStatement(insertQuery);
            int savedCount = 0;

            for (ScheduleEntry entry : schedule) {
                if (entry.roomName.equals("Online")) continue; // skip virtual sessions

                try {
                    int sectionId = getSectionId(conn, entry.sectionName);
                    int subjectId = getSubjectId(conn, entry.subjectCode, entry.type);
                    int roomId = getRoomId(conn, entry.roomName);
                    int timeSlotId = getTimeSlotId(conn, entry.timeSlot);
                    int durationId = getDurationId(conn, entry.duration);
                    int dayId = getDayId(conn, entry.day);

                    if (sectionId == -1 || subjectId == -1 || roomId == -1 ||
                            timeSlotId == -1 || durationId == -1 || dayId == -1) {
                        continue; // skip invalid
                    }

                    ps.setInt(1, sectionId);
                    ps.setInt(2, subjectId);
                    ps.setInt(3, roomId);
                    ps.setInt(4, timeSlotId);
                    ps.setInt(5, durationId);
                    ps.setInt(6, dayId);

                    ps.executeUpdate();
                    savedCount++;

                } catch (SQLException e) {
                    System.out.println("Failed to save entry: " + entry.sectionName + " - " + entry.subjectCode);
                    e.printStackTrace();
                }
            }

            System.out.println("✅ Successfully saved " + savedCount + " schedule entries to database.");

        } catch (SQLException e) {
            System.out.println("❌ Error saving schedule to database:");
            e.printStackTrace();
        }
    }

    private int getDurationId(Connection conn, double duration) throws SQLException {
        PreparedStatement ps = conn.prepareStatement("SELECT duration_id FROM duration_table WHERE duration = ?");
        ps.setDouble(1, duration);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) {
            return rs.getInt("duration_id");
        }
        return -1;
    }

    private int getDayId(Connection conn, String day) throws SQLException {
        PreparedStatement ps = conn.prepareStatement("SELECT day_id FROM days_table WHERE day = ?");
        ps.setString(1, day);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) {
            return rs.getInt("day_id");
        }
        return -1;
    }


    private int getSectionId(Connection conn, String sectionName) throws SQLException {
        PreparedStatement ps = conn.prepareStatement("SELECT SectionID FROM sections WHERE SectionName = ?");
        ps.setString(1, sectionName);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            return rs.getInt("SectionID");
        }
        return -1;
    }

    private int getSubjectId(Connection conn, String subjectCode, String type) throws SQLException {
        String query = "SELECT SubjectID FROM term2courses WHERE SubjectCode = ? AND (IsLab = ? OR IsLab = ?)";
        PreparedStatement ps = conn.prepareStatement(query);
        ps.setString(1, subjectCode);

        // Handle type matching
        if (type.equals("LAB")) {
            ps.setBoolean(2, true);
            ps.setBoolean(3, true);
        } else {
            ps.setBoolean(2, false);
            ps.setBoolean(3, false);
        }

        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            return rs.getInt("SubjectID");
        }
        return -1;
    }

    private int getRoomId(Connection conn, String roomName) throws SQLException {
        PreparedStatement ps = conn.prepareStatement("SELECT RoomID FROM rooms WHERE RoomName = ?");
        ps.setString(1, roomName);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            return rs.getInt("RoomID");
        }
        return -1;
    }

    private int getTimeSlotId(Connection conn, String timeSlot) throws SQLException {
        // Parse timeSlot format "HH:MM:SS-HH:MM:SS"
        String[] times = timeSlot.split("-");
        if (times.length != 2) return -1;

        String startTime = times[0];
        String endTime = times[1];

        PreparedStatement ps = conn.prepareStatement(
                "SELECT TimeSlotID FROM timeslots WHERE StartTime = ? AND EndTime = ?"
        );
        ps.setString(1, startTime);
        ps.setString(2, endTime);
        ResultSet rs = ps.executeQuery();

        if (rs.next()) {
            return rs.getInt("TimeSlotID");
        }
        return -1;
    }

    private boolean tryPlaceHybridSchedule(Section section, Subject subject, Set<String> used,
                                           Set<String> sectionSlotDayUsage, String[] days) {
        // For 1st and 2nd year courses with totalTime < 2:
        // One physical session + one online session on partner days

        for (Timeslot slot : timeslots) {
            // Try each partner day pair
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};

            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                String sectionSlotDay1 = section.id + "-" + slot.id + "-" + firstDay;
                String sectionSlotDay2 = section.id + "-" + slot.id + "-" + secondDay;

                // Check if both days are available for the section
                if (!sectionSlotDayUsage.contains(sectionSlotDay1) &&
                        !sectionSlotDayUsage.contains(sectionSlotDay2)) {

                    // Try to place physical session first
                    Room physicalRoom = findAvailablePhysicalRoom(subject, slot, firstDay, used);

                    if (physicalRoom != null) {
                        String roomSlotDay1 = physicalRoom.id + "-" + slot.id + "-" + firstDay;

                        // Create physical session entry
                        ScheduleEntry physicalEntry = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                physicalRoom.name, slot.start + "-" + slot.end, firstDay, subject.totalTime
                        );

                        // Create online session entry for partner day
                        ScheduleEntry onlineEntry = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                "Online", slot.start + "-" + slot.end, secondDay, subject.totalTime
                        );

                        schedule.add(physicalEntry);
                        schedule.add(onlineEntry);

                        // Mark resources as used
                        sectionSlotDayUsage.add(sectionSlotDay1);
                        sectionSlotDayUsage.add(sectionSlotDay2);
                        used.add(roomSlotDay1);
                        // Online sessions don't consume physical room resources

                        return true;
                    }
                }
            }
        }
        return false;
    }

    private boolean tryPlaceYear1PartnerDays(Section section, Subject subject, Set<String> used,
                                             Set<String> sectionSlotDayUsage, String[] days) {
        // For Year 1 courses with totalTime > 2:
        // Split in half: physical session + virtual session on partner days
        double hoursPerSession = subject.totalTime / 2.0;

        for (Timeslot slot : timeslots) {
            // Try each partner day pair
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};

            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                String sectionSlotDay1 = section.id + "-" + slot.id + "-" + firstDay;
                String sectionSlotDay2 = section.id + "-" + slot.id + "-" + secondDay;

                // Check if both days are available for the section
                if (!sectionSlotDayUsage.contains(sectionSlotDay1) &&
                        !sectionSlotDayUsage.contains(sectionSlotDay2)) {

                    // Try to place physical session first
                    Room physicalRoom = findAvailablePhysicalRoom(subject, slot, firstDay, used);

                    if (physicalRoom != null) {
                        String roomSlotDay1 = physicalRoom.id + "-" + slot.id + "-" + firstDay;

                        // Create physical session entry (first half)
                        ScheduleEntry physicalEntry = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                physicalRoom.name, slot.start + "-" + slot.end, firstDay, hoursPerSession
                        );

                        // Create virtual session entry for partner day (second half)
                        ScheduleEntry virtualEntry = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                "Online", slot.start + "-" + slot.end, secondDay, hoursPerSession
                        );

                        schedule.add(physicalEntry);
                        schedule.add(virtualEntry);

                        // Mark resources as used
                        sectionSlotDayUsage.add(sectionSlotDay1);
                        sectionSlotDayUsage.add(sectionSlotDay2);
                        used.add(roomSlotDay1);
                        // Virtual sessions don't consume physical room resources

                        return true;
                    }
                }
            }
        }
        return false;
    }

    private boolean tryPlaceOnPartnerDays(Section section, Subject subject, Set<String> used,
                                          Set<String> sectionSlotDayUsage, String[] days) {
        double hoursPerSession = subject.totalTime / 2.0;

        for (Timeslot slot : timeslots) {
            for (Room room : rooms) {
                if (!room.typeMatches(subject)) continue;

                // 3rd and 4th year sections cannot use Online room types
                if ((section.yearLevelId == 3 || section.yearLevelId == 4) &&
                        room.type.equalsIgnoreCase("Online")) {
                    continue;
                }

                // Try each partner day pair
                String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};

                for (String firstDay : partnerPairs) {
                    String secondDay = PARTNER_DAYS.get(firstDay);

                    String sectionSlotDay1 = section.id + "-" + slot.id + "-" + firstDay;
                    String sectionSlotDay2 = section.id + "-" + slot.id + "-" + secondDay;
                    String roomSlotDay1 = room.id + "-" + slot.id + "-" + firstDay;
                    String roomSlotDay2 = room.id + "-" + slot.id + "-" + secondDay;

                    // Check if both days are available
                    if (!sectionSlotDayUsage.contains(sectionSlotDay1) &&
                            !sectionSlotDayUsage.contains(sectionSlotDay2) &&
                            !used.contains(roomSlotDay1) &&
                            !used.contains(roomSlotDay2)) {

                        // Create entries for both days
                        ScheduleEntry entry1 = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                room.name, slot.start + "-" + slot.end, firstDay, hoursPerSession
                        );

                        ScheduleEntry entry2 = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                room.name, slot.start + "-" + slot.end, secondDay, hoursPerSession
                        );

                        schedule.add(entry1);
                        schedule.add(entry2);

                        // Mark as used
                        sectionSlotDayUsage.add(sectionSlotDay1);
                        sectionSlotDayUsage.add(sectionSlotDay2);
                        used.add(roomSlotDay1);
                        used.add(roomSlotDay2);

                        return true;
                    }
                }
            }
        }
        return false;
    }

    private boolean tryPlaceOnSingleDay(Section section, Subject subject, Set<String> used,
                                        Set<String> sectionSlotDayUsage, String[] days) {
        for (Timeslot slot : timeslots) {
            for (Room room : rooms) {
                if (!room.typeMatches(subject)) continue;

                // 3rd and 4th year sections cannot use Online room types
                if ((section.yearLevelId == 3 || section.yearLevelId == 4) &&
                        room.type.equalsIgnoreCase("Online")) {
                    continue;
                }

                for (String day : days) {
                    String sectionSlotDay = section.id + "-" + slot.id + "-" + day;
                    String roomSlotDay = room.id + "-" + slot.id + "-" + day;

                    if (!sectionSlotDayUsage.contains(sectionSlotDay) && !used.contains(roomSlotDay)) {
                        ScheduleEntry entry = new ScheduleEntry(
                                section.name, subject.code, subject.name, subject.type,
                                room.name, slot.start + "-" + slot.end, day, subject.totalTime
                        );

                        schedule.add(entry);
                        sectionSlotDayUsage.add(sectionSlotDay);
                        used.add(roomSlotDay);

                        return true;
                    }
                }
            }
        }
        return false;
    }

    private Room findAvailablePhysicalRoom(Subject subject, Timeslot slot, String day, Set<String> used) {
        for (Room room : rooms) {
            if (!room.typeMatches(subject)) continue;

            // Only use physical rooms (not virtual/online ones)
            if (room.type.equalsIgnoreCase("Online")) continue;

            String roomSlotDay = room.id + "-" + slot.id + "-" + day;
            if (!used.contains(roomSlotDay)) {
                return room;
            }
        }
        return null;
    }

    private int getProgramPriority(int programId) {
        try (Connection conn = JDBC.getConnection()) {
            PreparedStatement ps = conn.prepareStatement("SELECT Code FROM programs WHERE ProgramID=?");
            ps.setInt(1, programId);
            ResultSet rs = ps.executeQuery();
            if (rs.next()) {
                String code = rs.getString("Code");
                switch (code) {
                    case "IT": return 1;
                    case "IS": return 2;
                    case "CS": return 3;
                }
            }
        } catch (SQLException e) {
            e.printStackTrace();
        }
        return 99; // fallback (lowest priority)
    }

    private void printSchedule() {
        System.out.println("\n📅 GENERATED SCHEDULE:");

        // Sort schedule by section, then by day, then by time
        schedule.sort((a, b) -> {
            int sectionComp = a.sectionName.compareTo(b.sectionName);
            if (sectionComp != 0) return sectionComp;

            int dayComp = getDayOrder(a.day) - getDayOrder(b.day);
            if (dayComp != 0) return dayComp;

            return a.timeSlot.compareTo(b.timeSlot);
        });

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        for (String currentDay : days) {
            // Filter entries for current day
            List<ScheduleEntry> dayEntries = new ArrayList<>();
            for (ScheduleEntry entry : schedule) {
                if (entry.day.equals(currentDay)) {
                    dayEntries.add(entry);
                }
            }

            if (!dayEntries.isEmpty()) {
                System.out.println("\n=============================================================================================================================================================");
                System.out.println("                                                                    " + currentDay.toUpperCase());
                System.out.println("=============================================================================================================================================================");
                System.out.printf("%-12s | %-12s | %-50s | %-4s | %-12s | %-15s | %-8s%n",
                        "Section", "Code", "Subject Name", "Type", "Room", "Time Slot", "Duration");
                System.out.println("=============================================================================================================================================================");

                for (ScheduleEntry entry : dayEntries) {
                    System.out.printf("%-12s | %-12s | %-50s | %-4s | %-12s | %-15s | %-8.3f%n",
                            entry.sectionName,
                            entry.subjectCode,
                            entry.subjectName,
                            entry.type,
                            entry.roomName,
                            entry.timeSlot,
                            entry.duration);
                }
                System.out.println("=============================================================================================================================================================");
                System.out.println(currentDay + " total entries: " + dayEntries.size());
            } else {
                System.out.println("\n=============================================================================================================================================================");
                System.out.println("                                                                    " + currentDay.toUpperCase());
                System.out.println("=============================================================================================================================================================");
                System.out.println("No classes scheduled for " + currentDay);
                System.out.println("=============================================================================================================================================================");
            }
        }

        System.out.println("\n📊 SUMMARY:");
        System.out.println("Total scheduled entries: " + schedule.size());

        if (!unscheduled.isEmpty()) {
            System.out.println("\n⚠️ UNSCHEDULED COURSES:");
            System.out.println("----------------------------------------");
            for (String u : unscheduled) {
                System.out.println("• " + u);
            }
            System.out.println("Total unscheduled: " + unscheduled.size());
        }
    }

    private int getDayOrder(String day) {
        switch (day) {
            case "Monday": return 1;
            case "Tuesday": return 2;
            case "Wednesday": return 3;
            case "Thursday": return 4;
            case "Friday": return 5;
            case "Saturday": return 6;
            default: return 7;
        }
    }
}