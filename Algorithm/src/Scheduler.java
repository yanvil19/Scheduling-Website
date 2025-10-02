import java.sql.*;
import java.util.*;

public class Scheduler {
    public List<Section> sections = new ArrayList<>();
    public List<Subject> subjects = new ArrayList<>();
    public List<Room> rooms = new ArrayList<>();
    public List<Timeslot> timeslots = new ArrayList<>();

    List<ScheduleEntry> schedule = new ArrayList<>();
    List<String> unscheduled = new ArrayList<>();

    // Timer fields
    private long scheduleGenerationTimeNanos = 0;

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
        // Start timer
        long startTime = System.nanoTime();

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

        // End timer and store result
        long endTime = System.nanoTime();
        scheduleGenerationTimeNanos = endTime - startTime;
    }

    // Getter for schedule generation time
    public long getScheduleGenerationTimeNanos() {
        return scheduleGenerationTimeNanos;
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
                try {
                    int sectionId = getSectionId(conn, entry.sectionName);
                    int subjectId = getSubjectId(conn, entry.subjectCode, entry.type);
                    int timeSlotId = getTimeSlotId(conn, entry.timeSlot);
                    int durationId = getDurationId(conn, entry.duration);
                    int dayId = getDayId(conn, entry.day);

                    // For online classes, use RoomID = 6 (Virtual Room)
                    int roomId;
                    if (entry.roomName.equals("Online")) {
                        roomId = 6; // Virtual Room ID
                    } else {
                        roomId = getRoomId(conn, entry.roomName);
                        if (roomId == -1) {
                            System.out.println("⚠️  Invalid room for: " + entry.sectionName + " - " + entry.subjectCode);
                            continue;
                        }
                    }

                    if (sectionId == -1 || subjectId == -1 || timeSlotId == -1 ||
                            durationId == -1 || dayId == -1) {
                        System.out.println("⚠️  Missing data for: " + entry.sectionName + " - " + entry.subjectCode);
                        continue;
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

            // Print timing information here
            System.out.println("\n⏱️  Schedule Generation Time:");
            System.out.println("   Nanoseconds: " + scheduleGenerationTimeNanos + " ns");
            System.out.println("   Milliseconds: " + (scheduleGenerationTimeNanos / 1_000_000.0) + " ms");
            System.out.println("   Seconds: " + (scheduleGenerationTimeNanos / 1_000_000_000.0) + " s");

        } catch (SQLException e) {
            System.out.println("❌ Error saving schedule to database:");
            e.printStackTrace();
        }
    }

    // ### Core Scheduling Logic ###
    // Basically pag mahaba yung time like 4hrs per week
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

    // Basically pag mahaba yung time like 4hrs per week PERO 1st year ka ahhaha
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

    // Pag 2hrs pababa ang time edi ito gagamitin
    private boolean tryPlaceOnSingleDay(Section section, Subject subject, Set<String> used,
                                        Set<String> sectionSlotDayUsage, String[] days) {
        boolean isYear1 = section.yearLevelId == 1;

        for (Timeslot slot : timeslots) {
            for (String day : days) {
                String sectionSlotDay = section.id + "-" + slot.id + "-" + day;

                if (sectionSlotDayUsage.contains(sectionSlotDay)) continue;

                // ✅ Case 2: Year 1 + LAB + ≤2 hrs → Face-to-Face (room)
                if (isYear1 && subject.totalTime <= 2.0 && subject.type.equalsIgnoreCase("LAB")) {
                    for (Room room : rooms) {
                        if (!room.typeMatches(subject)) continue;

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

    /**
     * Attempts to schedule previously unscheduled courses by ignoring room type restrictions.
     * This method looks for any available room slots and places courses there regardless of
     * whether it's a lecture room for a lab or vice versa.
     */
    public void rescheduleUnscheduled() {
        if (unscheduled.isEmpty()) {
            System.out.println("✅ No unscheduled courses to reschedule!");
            return;
        }

        System.out.println("\n🔄 Attempting to reschedule " + unscheduled.size() + " unscheduled courses...");
        System.out.println("⚠️  WARNING: Ignoring room type restrictions for flexibility\n");

        // Track what was successfully rescheduled
        List<String> successfullyScheduled = new ArrayList<>();
        List<String> stillUnscheduled = new ArrayList<>();

        // Rebuild tracking sets from current schedule
        Set<String> used = new HashSet<>();
        Set<String> sectionSlotDayUsage = new HashSet<>();

        for (ScheduleEntry entry : schedule) {
            // Get IDs for tracking
            try (Connection conn = JDBC.getConnection()) {
                int sectionId = getSectionId(conn, entry.sectionName);
                int roomId = entry.roomName.equals("Online") ? 6 : getRoomId(conn, entry.roomName);
                int timeslotId = getTimeSlotId(conn, entry.timeSlot);

                if (sectionId != -1 && timeslotId != -1) {
                    sectionSlotDayUsage.add(sectionId + "-" + timeslotId + "-" + entry.day);
                }
                if (roomId != -1 && timeslotId != -1) {
                    used.add(roomId + "-" + timeslotId + "-" + entry.day);
                }
            } catch (SQLException e) {
                e.printStackTrace();
            }
        }

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        // Parse unscheduled list to get section and subject info
        for (String unscheduledItem : unscheduled) {
            // Parse format: "SubjectCode (SectionName) - Type"
            String[] parts = unscheduledItem.split(" \\(");
            if (parts.length < 2) continue;

            String subjectCode = parts[0].trim();
            String remaining = parts[1];
            String[] parts2 = remaining.split("\\) - ");
            if (parts2.length < 2) continue;

            String sectionName = parts2[0].trim();
            String type = parts2[1].trim();

            // Find the section and subject objects
            Section targetSection = null;
            Subject targetSubject = null;

            for (Section sec : sections) {
                if (sec.name.equals(sectionName)) {
                    targetSection = sec;
                    break;
                }
            }

            for (Subject sub : subjects) {
                if (sub.code.equals(subjectCode) && sub.type.equals(type)) {
                    targetSubject = sub;
                    break;
                }
            }

            if (targetSection == null || targetSubject == null) {
                System.out.println("⚠️  Could not find section/subject for: " + unscheduledItem);
                stillUnscheduled.add(unscheduledItem);
                continue;
            }

            // Try to place the course ANYWHERE available
            boolean placed = tryPlaceAnywhere(targetSection, targetSubject, used, sectionSlotDayUsage, days);

            if (placed) {
                successfullyScheduled.add(unscheduledItem);
                System.out.println("✅ Rescheduled: " + unscheduledItem);
            } else {
                stillUnscheduled.add(unscheduledItem);
                System.out.println("❌ Still cannot schedule: " + unscheduledItem);
            }
        }

        // Update the unscheduled list
        unscheduled = stillUnscheduled;

        // Print summary
        System.out.println("\n📊 RESCHEDULE SUMMARY:");
        System.out.println("Successfully rescheduled: " + successfullyScheduled.size());
        System.out.println("Still unscheduled: " + stillUnscheduled.size());
    }

    /**
     * Tries to place a course in ANY available room, ignoring room type restrictions.
     * Uses single day placement for ≤2 hours, split session for >2 hours.
     */
    private boolean tryPlaceAnywhere(Section section, Subject subject, Set<String> used,
                                     Set<String> sectionSlotDayUsage, String[] days) {

        if (subject.totalTime <= 2.0) {
            return tryPlaceAnywhereSingleDay(section, subject, used, sectionSlotDayUsage, days);
        } else {
            return tryPlaceAnywhereSplitSession(section, subject, used, sectionSlotDayUsage);
        }
    }

    /**
     * Places a ≤2 hour course in any available room on a single day.
     */
    private boolean tryPlaceAnywhereSingleDay(Section section, Subject subject, Set<String> used,
                                              Set<String> sectionSlotDayUsage, String[] days) {

        for (Timeslot slot : timeslots) {
            for (String day : days) {
                String sectionSlotDay = section.id + "-" + slot.id + "-" + day;

                if (sectionSlotDayUsage.contains(sectionSlotDay)) continue;

                // Try ANY room (ignoring type restrictions)
                for (Room room : rooms) {
                    // Skip virtual/online rooms - we want physical rooms
                    if (room.type.equalsIgnoreCase("Online")) continue;

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

    /**
     * Places a >2 hour course in any available rooms on partner days.
     */
    private boolean tryPlaceAnywhereSplitSession(Section section, Subject subject, Set<String> used,
                                                 Set<String> sectionSlotDayUsage) {

        double hoursPerSession = subject.totalTime / 2.0;

        for (Timeslot slot : timeslots) {
            String[] partnerPairs = {"Monday", "Tuesday", "Wednesday"};

            for (String firstDay : partnerPairs) {
                String secondDay = PARTNER_DAYS.get(firstDay);

                String sectionSlotDay1 = section.id + "-" + slot.id + "-" + firstDay;
                String sectionSlotDay2 = section.id + "-" + slot.id + "-" + secondDay;

                if (sectionSlotDayUsage.contains(sectionSlotDay1) ||
                        sectionSlotDayUsage.contains(sectionSlotDay2)) continue;

                // Find ANY two available rooms (ignoring type)
                Room room1 = findAnyAvailableRoom(slot, firstDay, used);
                Room room2 = findAnyAvailableRoom(slot, secondDay, used);

                if (room1 == null || room2 == null) continue;

                String roomSlotDay1 = room1.id + "-" + slot.id + "-" + firstDay;
                String roomSlotDay2 = room2.id + "-" + slot.id + "-" + secondDay;

                if (used.contains(roomSlotDay1) || used.contains(roomSlotDay2)) continue;

                ScheduleEntry entry1 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                        room1.name, slot.start + "-" + slot.end, firstDay, hoursPerSession);
                ScheduleEntry entry2 = new ScheduleEntry(section.name, subject.code, subject.name, subject.type,
                        room2.name, slot.start + "-" + slot.end, secondDay, hoursPerSession);

                schedule.add(entry1);
                schedule.add(entry2);

                sectionSlotDayUsage.add(sectionSlotDay1);
                sectionSlotDayUsage.add(sectionSlotDay2);
                used.add(roomSlotDay1);
                used.add(roomSlotDay2);

                return true;
            }
        }

        return false;
    }

    /**
     * Finds ANY available physical room regardless of type.
     */
    private Room findAnyAvailableRoom(Timeslot slot, String day, Set<String> used) {
        for (Room room : rooms) {
            // Skip virtual/online rooms
            if (room.type.equalsIgnoreCase("Online")) continue;

            String roomSlotDay = room.id + "-" + slot.id + "-" + day;
            if (!used.contains(roomSlotDay)) {
                return room;
            }
        }
        return null;
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