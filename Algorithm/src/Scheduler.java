import java.sql.*;
import java.util.*;

public class Scheduler {
    // Data containers loaded from DB
    public List<Section> sections = new ArrayList<>();
    public List<Subject> subjects = new ArrayList<>();
    public List<Room> rooms = new ArrayList<>();
    public List<Timeslot> timeslots = new ArrayList<>();

    // Results
    List<ScheduleEntry> schedule = new ArrayList<>();
    List<String> unscheduled = new ArrayList<>();

    // Timer
    private long scheduleGenerationTimeNanos = 0;

    // Day pairs for split sessions
    private static final Map<String, String> PARTNER_DAYS = new HashMap<>();
    static {
        PARTNER_DAYS.put("Monday", "Thursday");
        PARTNER_DAYS.put("Thursday", "Monday");
        PARTNER_DAYS.put("Tuesday", "Friday");
        PARTNER_DAYS.put("Friday", "Tuesday");
        PARTNER_DAYS.put("Wednesday", "Saturday");
        PARTNER_DAYS.put("Saturday", "Wednesday");
    }

    // Represents a scheduled subject entry
    class ScheduleEntry {
        String sectionName, subjectCode, subjectName, type, roomName, timeSlot, day;
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

    // ===== CONFLICT CHECKING METHODS =====

    // Check if a room is available at a specific timeslot and day
    private boolean isRoomAvailable(Room room, Timeslot slot, String day, Set<String> used) {
        String roomSlotDay = room.id + "-" + slot.id + "-" + day;
        return !used.contains(roomSlotDay);
    }

    // Check if a section is free at a specific timeslot and day
    private boolean isSectionAvailable(Section section, Timeslot slot, String day,
                                       Set<String> sectionSlotDayUsage) {
        String sectionSlotDay = section.id + "-" + slot.id + "-" + day;
        return !sectionSlotDayUsage.contains(sectionSlotDay);
    }

    // Check if both days in a split session are available for the section
    private boolean isSplitSessionAvailable(Section section, Timeslot slot,
                                            String firstDay, String secondDay,
                                            Set<String> sectionSlotDayUsage) {
        return isSectionAvailable(section, slot, firstDay, sectionSlotDayUsage) &&
                isSectionAvailable(section, slot, secondDay, sectionSlotDayUsage);
    }

    // Mark a room as occupied at a specific time and day
    private void markRoomUsed(Room room, Timeslot slot, String day, Set<String> used) {
        String roomSlotDay = room.id + "-" + slot.id + "-" + day;
        used.add(roomSlotDay);
    }

    // Mark a section as occupied at a specific time and day
    private void markSectionUsed(Section section, Timeslot slot, String day,
                                 Set<String> sectionSlotDayUsage) {
        String sectionSlotDay = section.id + "-" + slot.id + "-" + day;
        sectionSlotDayUsage.add(sectionSlotDay);
    }

    // Mark both days of a split session as used for a section
    private void markSplitSessionUsed(Section section, Timeslot slot,
                                      String firstDay, String secondDay,
                                      Set<String> sectionSlotDayUsage) {
        markSectionUsed(section, slot, firstDay, sectionSlotDayUsage);
        markSectionUsed(section, slot, secondDay, sectionSlotDayUsage);
    }

    // Mark both rooms as used for a split session
    private void markBothRoomsUsed(Room room1, Room room2, Timeslot slot,
                                   String firstDay, String secondDay, Set<String> used) {
        markRoomUsed(room1, slot, firstDay, used);
        markRoomUsed(room2, slot, secondDay, used);
    }

    // Load all necessary data from DB
    public void loadData() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            // Sections
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

            // Subjects
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

            // Rooms
            rs = conn.createStatement().executeQuery("SELECT * FROM rooms");
            while (rs.next()) {
                Room r = new Room(
                        rs.getInt("RoomID"),
                        rs.getString("RoomName"),
                        rs.getString("RoomType")
                );
                rooms.add(r);
            }

            // Timeslots
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

    // Main schedule generation logic
    public void generateSchedule() {
        long startTime = System.nanoTime(); // start timer

        // Reset previous runs
        sections.clear();
        subjects.clear();
        rooms.clear();
        timeslots.clear();
        schedule.clear();
        unscheduled.clear();

        loadData(); // fetch latest data

        // Sort: Year level first, then program priority
        sections.sort((a, b) -> {
            if (a.yearLevelId != b.yearLevelId) {
                return a.yearLevelId - b.yearLevelId;
            }
            int progPriorityA = getProgramPriority(a.programId);
            int progPriorityB = getProgramPriority(b.programId);
            return progPriorityA - progPriorityB;
        });

        // Trackers for conflicts
        Set<String> used = new HashSet<>(); // room-slot-day usage
        Set<String> sectionSlotDayUsage = new HashSet<>(); // section-slot-day usage

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        // Shuffle days to randomize starting point
        List<String> daysList = new ArrayList<>(Arrays.asList(days));
        Collections.shuffle(daysList);
        days = daysList.toArray(new String[0]);

        // For every section-subject pair
        for (Section section : sections) {
            for (Subject subject : subjects) {
                if (!Objects.equals(section.programId, subject.programId)) continue;
                if (section.yearLevelId != subject.yearLevelId) continue;

                boolean placed;
                double totalTime = subject.totalTime;
                int year = section.yearLevelId;

                // Year 1 has special handling for long courses
                if (year == 1) {
                    if (totalTime <= 2.0) {
                        placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                    } else {
                        placed = tryPlaceYear1SplitHybrid(section, subject, used, sectionSlotDayUsage);
                    }
                } else { // Year 2–4
                    if (totalTime <= 2.0) {
                        placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                    } else {
                        placed = tryPlaceSplitSession(section, subject, used, sectionSlotDayUsage);
                    }
                }

                // Add to unscheduled if no placement found
                if (!placed) {
                    unscheduled.add(subject.code + " (" + section.name + ") - " + subject.type);
                }
            }
        }

        // Stop timer
        long endTime = System.nanoTime();
        scheduleGenerationTimeNanos = endTime - startTime;
        System.out.println("\n⏱️  Schedule Generation Time: " + (scheduleGenerationTimeNanos / 1_000_000.0) + " ms");
    }

    // Save generated schedule back to DB
    public void saveScheduleToDatabase() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed! Cannot save schedule.");
                return;
            }

            conn.createStatement().executeUpdate("DELETE FROM schedule"); // clear old schedule

            String insertQuery = """
            INSERT INTO schedule (SectionID, SubjectID, RoomID, TimeSlotID, duration_id, day_id) 
            VALUES (?, ?, ?, ?, ?, ?)
            """;

            PreparedStatement ps = conn.prepareStatement(insertQuery);
            int savedCount = 0;

            for (ScheduleEntry entry : schedule) {
                try {
                    // Look up IDs
                    int sectionId = getSectionId(conn, entry.sectionName);
                    int subjectId = getSubjectId(conn, entry.subjectCode, entry.type);
                    int timeSlotId = getTimeSlotId(conn, entry.timeSlot);
                    int durationId = getDurationId(conn, entry.duration);
                    int dayId = getDayId(conn, entry.day);

                    // Online → always RoomID = 6
                    int roomId = entry.roomName.equals("Online") ? 6 : getRoomId(conn, entry.roomName);

                    if (roomId == -1 || sectionId == -1 || subjectId == -1 ||
                            timeSlotId == -1 || durationId == -1 || dayId == -1) {
                        System.out.println("⚠️  Missing data for: " + entry.sectionName + " - " + entry.subjectCode);
                        continue;
                    }

                    // Save
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
            System.out.println("\n⏱️  Schedule Generation Time: " +
                    (scheduleGenerationTimeNanos / 1_000_000.0) + " ms");

        } catch (SQLException e) {
            System.out.println("❌ Error saving schedule to database:");
            e.printStackTrace();
        }
    }

    // ===== Core placement strategies =====

    // >2 hrs (Year 2–4) → split across partner days, both F2F
    private boolean tryPlaceSplitSession(Section section, Subject subject, Set<String> used,
                                         Set<String> sectionSlotDayUsage) {
        double hoursPerSession = subject.totalTime / 2.0;
        boolean isYear1 = section.yearLevelId == 1;

        for (Timeslot slot : timeslots) {
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};
            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                // Check if section is available on both days
                if (!isSplitSessionAvailable(section, slot, firstDay, secondDay, sectionSlotDayUsage)) {
                    continue;
                }

                ScheduleEntry entry1, entry2;

                if (isYear1) {
                    // Year1 special: Online split
                    entry1 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            "Online", slot.start + "-" + slot.end, firstDay, hoursPerSession);
                    entry2 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            "Online", slot.start + "-" + slot.end, secondDay, hoursPerSession);
                } else {
                    // Find available physical rooms
                    Room room1 = findAvailablePhysicalRoom(subject, slot, firstDay, used);
                    Room room2 = findAvailablePhysicalRoom(subject, slot, secondDay, used);
                    if (room1 == null || room2 == null) continue;

                    entry1 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            room1.name, slot.start + "-" + slot.end, firstDay, hoursPerSession);
                    entry2 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            room2.name, slot.start + "-" + slot.end, secondDay, hoursPerSession);

                    // Mark rooms as used
                    markBothRoomsUsed(room1, room2, slot, firstDay, secondDay, used);
                }

                // Commit schedule
                schedule.add(entry1);
                schedule.add(entry2);
                markSplitSessionUsed(section, slot, firstDay, secondDay, sectionSlotDayUsage);
                return true;
            }
        }
        return false;
    }

    // >2 hrs Year 1 → 1 F2F + 1 Online
    private boolean tryPlaceYear1SplitHybrid(Section section, Subject subject,
                                             Set<String> used, Set<String> sectionSlotDayUsage) {
        double hoursPerSession = subject.totalTime / 2.0;

        for (Timeslot slot : timeslots) {
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};
            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                // Check if section is available on both days
                if (!isSplitSessionAvailable(section, slot, firstDay, secondDay, sectionSlotDayUsage)) {
                    continue;
                }

                // Assign 1 F2F + 1 Online
                for (Room room : rooms) {
                    if (!room.typeMatches(subject)) continue;
                    if (room.type.equalsIgnoreCase("Online")) continue;

                    // Check if room is available
                    if (!isRoomAvailable(room, slot, firstDay, used)) continue;

                    ScheduleEntry f2fEntry = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            room.name, slot.start + "-" + slot.end, firstDay, hoursPerSession);
                    ScheduleEntry onlineEntry = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            "Online", slot.start + "-" + slot.end, secondDay, hoursPerSession);

                    schedule.add(f2fEntry);
                    schedule.add(onlineEntry);

                    // Mark usage
                    markSplitSessionUsed(section, slot, firstDay, secondDay, sectionSlotDayUsage);
                    markRoomUsed(room, slot, firstDay, used);
                    return true;
                }
            }
        }
        return false;
    }

    // ≤2 hrs → single day placement
    private boolean tryPlaceOnSingleDay(Section section, Subject subject, Set<String> used,
                                        Set<String> sectionSlotDayUsage, String[] days) {
        boolean isYear1 = section.yearLevelId == 1;

        for (Timeslot slot : timeslots) {
            for (String day : days) {
                // Check if section is available
                if (!isSectionAvailable(section, slot, day, sectionSlotDayUsage)) continue;

                // Year1 LAB → F2F
                if (isYear1 && subject.totalTime <= 2.0 && subject.type.equalsIgnoreCase("LAB")) {
                    for (Room room : rooms) {
                        if (!room.typeMatches(subject)) continue;

                        // Check if room is available
                        if (!isRoomAvailable(room, slot, day, used)) continue;

                        schedule.add(new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                                room.name, slot.start + "-" + slot.end, day, subject.totalTime));
                        markSectionUsed(section, slot, day, sectionSlotDayUsage);
                        markRoomUsed(room, slot, day, used);
                        return true;
                    }
                }

                // Year1 LEC → Online
                if (isYear1) {
                    schedule.add(new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            "Online", slot.start + "-" + slot.end, day, subject.totalTime));
                    markSectionUsed(section, slot, day, sectionSlotDayUsage);
                    return true;
                }

                // Year2+ → assign to physical room
                for (Room room : rooms) {
                    if (!room.typeMatches(subject)) continue;
                    if ((section.yearLevelId >= 3) && room.type.equalsIgnoreCase("Online")) continue;

                    // Check if room is available
                    if (!isRoomAvailable(room, slot, day, used)) continue;

                    schedule.add(new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                            room.name, slot.start + "-" + slot.end, day, subject.totalTime));
                    markSectionUsed(section, slot, day, sectionSlotDayUsage);
                    markRoomUsed(room, slot, day, used);
                    return true;
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

            if (isRoomAvailable(room, slot, day, used)) {
                return room;
            }
        }
        return null;
    }

    // ### Database Helper Methods ###
    private int getDayId(Connection conn, String day) throws SQLException {
        PreparedStatement ps = conn.prepareStatement("SELECT day_id FROM days_table WHERE day = ?");
        ps.setString(1, day);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) {
            return rs.getInt("day_id");
        }
        return -1;
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

    private int getDurationId(Connection conn, double duration) throws SQLException {
        PreparedStatement ps = conn.prepareStatement("SELECT duration_id FROM duration_table WHERE duration = ?");
        ps.setDouble(1, duration);
        ResultSet rs = ps.executeQuery();
        if (rs.next()) {
            return rs.getInt("duration_id");
        }
        return -1;
    }

}