import java.sql.*;
import java.util.*;

public class Scheduler {
    public List<Section> sections = new ArrayList<>();
    public List<Subject> subjects = new ArrayList<>();
    public List<Room> rooms = new ArrayList<>();
    public List<Timeslot> timeslots = new ArrayList<>();

    List<ScheduleEntry> schedule = new ArrayList<>();
    List<String> unscheduled = new ArrayList<>();

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

    // ### Main Entry Points ###
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

        // Trackers for handling conflicts
        Set<String> used = new HashSet<>(); // room+slot+day usage
        Set<String> sectionSlotDayUsage = new HashSet<>(); // section+slot+day usage

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        for (Section section : sections) {
            for (Subject subject : subjects) {
                // Match section and subject by program and year level
                if (!Objects.equals(section.programId, subject.programId)) continue;
                if (section.yearLevelId != subject.yearLevelId) continue;

                boolean placed;
                double totalTime = subject.totalTime;
                int year = section.yearLevelId;

                if (year == 1) {
                    if (totalTime <= 2.0) {
                        placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                    } else {
                        placed = tryPlaceYear1SplitHybrid(section, subject, used, sectionSlotDayUsage);
                    }
                } else {
                    if (totalTime <= 2.0) {
                        placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                    } else {
                        placed = tryPlaceSplitSession(section, subject, used, sectionSlotDayUsage);
                    }
                }

                if (!placed) {
                    unscheduled.add(subject.code + " (" + section.name + ") - " + subject.type);
                }
            }
        }
    }

    public void saveScheduleToDatabase() {
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

    // ### Core Scheduling Logic ###
    private boolean tryPlaceSplitSession(Section section, Subject subject, Set<String> used,
                                         Set<String> sectionSlotDayUsage) {
        double hoursPerSession = subject.totalTime / 2.0;
        boolean isYear1 = section.yearLevelId == 1;

        for (Timeslot slot : timeslots) {
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};

            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                String sectionSlotDay1 = section.id + "-" + slot.id + "-" + firstDay;
                String sectionSlotDay2 = section.id + "-" + slot.id + "-" + secondDay;

                if (!sectionSlotDayUsage.contains(sectionSlotDay1) &&
                        !sectionSlotDayUsage.contains(sectionSlotDay2)) {

                    ScheduleEntry entry1, entry2;

                    if (isYear1) {
                        entry1 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                                "Online", slot.start + "-" + slot.end, firstDay, hoursPerSession);
                        entry2 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                                "Online", slot.start + "-" + slot.end, secondDay, hoursPerSession);
                    } else {
                        Room room1 = findAvailablePhysicalRoom(subject, slot, firstDay, used);
                        Room room2 = findAvailablePhysicalRoom(subject, slot, secondDay, used);
                        if (room1 == null || room2 == null) continue;

                        String roomSlotDay1 = room1.id + "-" + slot.id + "-" + firstDay;
                        String roomSlotDay2 = room2.id + "-" + slot.id + "-" + secondDay;
                        if (used.contains(roomSlotDay1) || used.contains(roomSlotDay2)) continue;

                        entry1 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                                room1.name, slot.start + "-" + slot.end, firstDay, hoursPerSession);
                        entry2 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                                room2.name, slot.start + "-" + slot.end, secondDay, hoursPerSession);

                        used.add(roomSlotDay1);
                        used.add(roomSlotDay2);
                    }

                    schedule.add(entry1);
                    schedule.add(entry2);

                    sectionSlotDayUsage.add(sectionSlotDay1);
                    sectionSlotDayUsage.add(sectionSlotDay2);

                    return true;
                }
            }
        }
        return false;
    }

    private boolean tryPlaceYear1SplitHybrid(Section section, Subject subject,
                                             Set<String> used, Set<String> sectionSlotDayUsage) {
        double hoursPerSession = subject.totalTime / 2.0;

        for (Timeslot slot : timeslots) {
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};

            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                String sectionSlotDay1 = section.id + "-" + slot.id + "-" + firstDay;
                String sectionSlotDay2 = section.id + "-" + slot.id + "-" + secondDay;

                if (sectionSlotDayUsage.contains(sectionSlotDay1) || sectionSlotDayUsage.contains(sectionSlotDay2)) continue;

                for (Room room : rooms) {
                    if (!room.typeMatches(subject)) continue;
                    if (room.type.equalsIgnoreCase("Online")) continue;

                    String roomSlotDay1 = room.id + "-" + slot.id + "-" + firstDay;
                    if (used.contains(roomSlotDay1)) continue;

                    ScheduleEntry f2fEntry = new ScheduleEntry(
                            section.name, subject.code, subject.name, subject.type,
                            room.name, slot.start + "-" + slot.end, firstDay, hoursPerSession
                    );

                    ScheduleEntry onlineEntry = new ScheduleEntry(
                            section.name, subject.code, subject.name, subject.type,
                            "Online", slot.start + "-" + slot.end, secondDay, hoursPerSession
                    );

                    schedule.add(f2fEntry);
                    schedule.add(onlineEntry);

                    sectionSlotDayUsage.add(sectionSlotDay1);
                    sectionSlotDayUsage.add(sectionSlotDay2);
                    used.add(roomSlotDay1);

                    return true;
                }
            }
        }
        return false;
    }

    private boolean tryPlaceOnSingleDay(Section section, Subject subject, Set<String> used,
                                        Set<String> sectionSlotDayUsage, String[] days) {
        boolean isYear1 = section.yearLevelId == 1;

        for (Timeslot slot : timeslots) {
            for (String day : days) {
                String sectionSlotDay = section.id + "-" + slot.id + "-" + day;

                if (sectionSlotDayUsage.contains(sectionSlotDay)) continue;

                if (isYear1) {
                    ScheduleEntry entry = new ScheduleEntry(
                            section.name, subject.code, subject.name, subject.type,
                            "Online", slot.start + "-" + slot.end, day, subject.totalTime
                    );

                    schedule.add(entry);
                    sectionSlotDayUsage.add(sectionSlotDay);
                    return true;
                }

                for (Room room : rooms) {
                    if (!room.typeMatches(subject)) continue;
                    if ((section.yearLevelId >= 3) && room.type.equalsIgnoreCase("Online")) continue;

                    String roomSlotDay = room.id + "-" + slot.id + "-" + day;
                    if (used.contains(roomSlotDay)) continue;

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