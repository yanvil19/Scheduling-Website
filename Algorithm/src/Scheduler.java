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

        // Sort sections by program priority and year level
        sections.sort((a, b) -> {
            int progPriorityA = getProgramPriority(a.programId);
            int progPriorityB = getProgramPriority(b.programId);
            if (progPriorityA != progPriorityB) return progPriorityA - progPriorityB;
            return b.yearLevelId - a.yearLevelId; // Higher year first
        });

        Set<String> used = new HashSet<>(); // room+slot+day usage
        Set<String> sectionSlotDayUsage = new HashSet<>(); // section+slot+day usage

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        for (Section section : sections) {
            for (Subject subject : subjects) {
                if (!Objects.equals(section.programId, subject.programId)) continue;
                if (section.yearLevelId != subject.yearLevelId) continue;

                boolean placed = false;

                // Check if subject needs to be split across partner days
                if (subject.totalTime > 2.0) {
                    // Try to place on partner days
                    placed = tryPlaceOnPartnerDays(section, subject, used, sectionSlotDayUsage, days);
                } else {
                    // Try to place on single day
                    placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days);
                }

                if (!placed) {
                    unscheduled.add(subject.code + " (" + section.name + ") - " + subject.type);
                }
            }
        }

        printSchedule();
    }

    private boolean tryPlaceOnPartnerDays(Section section, Subject subject, Set<String> used,
                                          Set<String> sectionSlotDayUsage, String[] days) {
        double hoursPerSession = subject.totalTime / 2.0;

        for (Timeslot slot : timeslots) {
            for (Room room : rooms) {
                if (!room.typeMatches(subject)) continue;

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
        System.out.println("=============================================================================================================================================================");
        System.out.printf("%-12s | %-12s | %-50s | %-4s | %-12s | %-17s | %-10s | %-8s%n",
                "Section", "Code", "Subject Name", "Type", "Room", "Time Slot", "Day", "Duration (hrs)");
        System.out.println("=============================================================================================================================================================");

        // Sort schedule by section, then by day, then by time
        schedule.sort((a, b) -> {
            int sectionComp = a.sectionName.compareTo(b.sectionName);
            if (sectionComp != 0) return sectionComp;

            int dayComp = getDayOrder(a.day) - getDayOrder(b.day);
            if (dayComp != 0) return dayComp;

            return a.timeSlot.compareTo(b.timeSlot);
        });

        for (ScheduleEntry entry : schedule) {
            System.out.printf("%-12s | %-12s | %-50s | %-4s | %-12s | %-15s | %-10s | %-8.3f%n",
                    entry.sectionName,
                    entry.subjectCode,
                    entry.subjectName,
                    entry.type,
                    entry.roomName,
                    entry.timeSlot,
                    entry.day,
                    entry.duration);
        }

        System.out.println("====================================================================================================================================================");
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