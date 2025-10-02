import java.sql.*;
import java.util.*;

public class Display {

    // ==============================
    // SHOW DATABASE COURSES
    // ==============================
    public void showTerm2Courses() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            Statement stmt = conn.createStatement();
            String sql = "SELECT SubjectID, SubjectCode, SubjectName, IsLab, ProgramID, YearLevelID, TotalTime FROM term2courses";
            ResultSet rs = stmt.executeQuery(sql);

            System.out.println("📚 Term 2 Courses\n");
            System.out.printf("%-4s | %-10s | %-50s | %-4s | %-9s | %-11s | %-9s\n",
                    "ID", "Code", "Name", "Lab", "ProgramID", "YearLevelID", "TotalTime");
            System.out.println("------------------------------------------------------------------------------------------------------------");

            while (rs.next()) {
                int id = rs.getInt("SubjectID");
                String code = rs.getString("SubjectCode");
                String name = rs.getString("SubjectName");
                int isLab = rs.getInt("IsLab");
                int programId = rs.getInt("ProgramID");
                int yearLevelId = rs.getInt("YearLevelID");
                double totalTime = rs.getDouble("TotalTime");

                System.out.printf("%-4d | %-10s | %-50s | %-4d | %-9d | %-11d | %-9.2f\n",
                        id, code, name, isLab, programId, yearLevelId, totalTime);
            }

            rs.close();
            stmt.close();

        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    // ==============================
    // SHOW GENERATED SCHEDULE
    // ==============================
    public void showSchedule(List<Scheduler.ScheduleEntry> schedule, List<String> unscheduled) {
        System.out.println("\n📅 GENERATED SCHEDULE:");

        // Sort schedule entries
        schedule.sort((a, b) -> {
            int sectionComp = a.sectionName.compareTo(b.sectionName);
            if (sectionComp != 0) return sectionComp;

            int dayComp = getDayOrder(a.day) - getDayOrder(b.day);
            if (dayComp != 0) return dayComp;

            return a.timeSlot.compareTo(b.timeSlot);
        });

        String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

        for (String currentDay : days) {
            List<Scheduler.ScheduleEntry> dayEntries = new ArrayList<>();
            for (Scheduler.ScheduleEntry entry : schedule) {
                if (entry.day.equals(currentDay)) {
                    dayEntries.add(entry);
                }
            }

            System.out.println("\n=============================================================================================================================================================");
            System.out.println("                                                                    " + currentDay.toUpperCase());
            System.out.println("=============================================================================================================================================================");

            if (!dayEntries.isEmpty()) {
                System.out.printf("%-12s | %-12s | %-50s | %-4s | %-12s | %-15s | %-8s%n",
                        "Section", "Code", "Subject Name", "Type", "Room", "Time Slot", "Duration");
                System.out.println("=============================================================================================================================================================");
                for (Scheduler.ScheduleEntry entry : dayEntries) {
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
                System.out.println("No classes scheduled for " + currentDay);
                System.out.println("=============================================================================================================================================================");
            }
        }

        // Summary
        System.out.println("\n📊 SUMMARY:");
        System.out.println("Total scheduled entries: " + schedule.size());

        if (!unscheduled.isEmpty()) {
            System.out.println("\n⚠️ UNSCHEDULED COURSES:");
            for (String u : unscheduled) {
                System.out.println("• " + u);
            }
            System.out.println("Total unscheduled: " + unscheduled.size());
        }
    }

    // ==============================
    // SHOW ROOM USAGE BY TIMESLOT
    // ==============================
    public void displayTimeslotUsage() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

            for (String day : days) {
                System.out.println("\n=============================================================================================");
                System.out.println("                              " + day.toUpperCase());
                System.out.println("=============================================================================================\n");

                ResultSet timeslotRs = conn.createStatement().executeQuery(
                        "SELECT TimeSlotID, StartTime, EndTime FROM timeslots ORDER BY StartTime"
                );

                while (timeslotRs.next()) {
                    int timeslotId = timeslotRs.getInt("TimeSlotID");
                    String startTime = timeslotRs.getString("StartTime");
                    String endTime = timeslotRs.getString("EndTime");

                    System.out.println(day + " | " + startTime + " - " + endTime);

                    ResultSet roomRs = conn.createStatement().executeQuery(
                            "SELECT RoomID, RoomName, RoomType FROM rooms WHERE RoomType != 'Online' ORDER BY RoomName"
                    );

                    while (roomRs.next()) {
                        int roomId = roomRs.getInt("RoomID");
                        String roomName = roomRs.getString("RoomName");
                        String roomType = roomRs.getString("RoomType");

                        PreparedStatement checkUsage = conn.prepareStatement(
                                "SELECT sec.SectionName " +
                                        "FROM schedule s " +
                                        "JOIN sections sec ON s.SectionID = sec.SectionID " +
                                        "JOIN days_table d ON s.day_id = d.day_id " +
                                        "WHERE s.RoomID = ? AND s.TimeSlotID = ? AND d.day = ?"
                        );
                        checkUsage.setInt(1, roomId);
                        checkUsage.setInt(2, timeslotId);
                        checkUsage.setString(3, day);

                        ResultSet usageRs = checkUsage.executeQuery();

                        switch (roomType) {
                            case "Lecture":
                                if (usageRs.next()) {
                                    String sectionName = usageRs.getString("SectionName");
                                    System.out.println(roomName + " (" + sectionName + ")");
                                } else {
                                    System.out.println(roomName + " (Not Used)");
                                }
                                break;

                            case "Laboratory":
                                if (usageRs.next()) {
                                    String sectionName = usageRs.getString("SectionName");
                                    System.out.println(roomName + " (" + sectionName + ")");
                                } else {
                                    System.out.println(roomName + " (Not Used)");
                                }
                                break;

                            default:
                                if (usageRs.next()) {
                                    String sectionName = usageRs.getString("SectionName");
                                    System.out.println(roomName + " (" + sectionName + ")");
                                } else {
                                    System.out.println(roomName + " (Not Used)");
                                }
                                break;
                        }

                        usageRs.close();
                        checkUsage.close();
                    }

                    roomRs.close();
                    System.out.println();
                }

                timeslotRs.close();
            }

        } catch (SQLException e) {
            System.out.println("❌ Error displaying timeslot usage:");
            e.printStackTrace();
        }
    }

    // ==============================
    // HELPER
    // ==============================
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