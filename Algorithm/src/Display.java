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
    // SHOW ROOM USAGE BY TIMESLOT (TABLE FORMAT)
    // ==============================
    public void displayTimeslotUsage() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            String[] days = {"Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"};

            // Get all rooms (excluding online)
            List<RoomInfo> rooms = new ArrayList<>();
            ResultSet roomRs = conn.createStatement().executeQuery(
                    "SELECT RoomID, RoomName FROM rooms WHERE RoomType != 'Online' ORDER BY RoomName"
            );
            while (roomRs.next()) {
                rooms.add(new RoomInfo(roomRs.getInt("RoomID"), roomRs.getString("RoomName")));
            }
            roomRs.close();

            // Total counters for all days
            int totalUsedSlots = 0;
            int totalNotUsedSlots = 0;

            for (String day : days) {
                System.out.println("\n═══════════════════════════════════════════════════════════════════════════════════════════");
                System.out.println("                                        " + day.toUpperCase());
                System.out.println("═══════════════════════════════════════════════════════════════════════════════════════════");

                // Print header
                System.out.printf("%-20s", "Time Slot");
                for (RoomInfo room : rooms) {
                    System.out.printf("| %-18s", room.name);
                }
                System.out.println("|");

                // Print separator
                System.out.print("─────────────────────");
                for (int i = 0; i < rooms.size(); i++) {
                    System.out.print("─────────────────────");
                }
                System.out.println();

                // Counters for this day
                int dayUsedSlots = 0;
                int dayNotUsedSlots = 0;

                // Get all timeslots
                ResultSet timeslotRs = conn.createStatement().executeQuery(
                        "SELECT TimeSlotID, StartTime, EndTime FROM timeslots ORDER BY StartTime"
                );

                while (timeslotRs.next()) {
                    int timeslotId = timeslotRs.getInt("TimeSlotID");
                    String startTime = timeslotRs.getString("StartTime");
                    String endTime = timeslotRs.getString("EndTime");

                    // Format time slot (remove seconds for cleaner display)
                    String timeSlot = startTime.substring(0, 5) + "-" + endTime.substring(0, 5);
                    System.out.printf("%-20s", timeSlot);

                    // Check each room's usage for this timeslot
                    for (RoomInfo room : rooms) {
                        PreparedStatement checkUsage = conn.prepareStatement(
                                "SELECT sec.SectionName, c.IsLab " +
                                        "FROM schedule s " +
                                        "JOIN sections sec ON s.SectionID = sec.SectionID " +
                                        "JOIN term2courses c ON s.SubjectID = c.SubjectID " +
                                        "JOIN days_table d ON s.day_id = d.day_id " +
                                        "WHERE s.RoomID = ? AND s.TimeSlotID = ? AND d.day = ?"
                        );
                        checkUsage.setInt(1, room.id);
                        checkUsage.setInt(2, timeslotId);
                        checkUsage.setString(3, day);

                        ResultSet usageRs = checkUsage.executeQuery();

                        if (usageRs.next()) {
                            String sectionName = usageRs.getString("SectionName");
                            boolean isLab = usageRs.getBoolean("IsLab");
                            String type = isLab ? "Lab" : "Lec";
                            String cellContent = sectionName + "-" + type;

                            // Truncate if too long
                            if (cellContent.length() > 18) {
                                cellContent = cellContent.substring(0, 15) + "...";
                            }
                            System.out.printf("| %-18s", cellContent);
                            dayUsedSlots++;
                        } else {
                            System.out.printf("| %-18s", "---");
                            dayNotUsedSlots++;
                        }

                        usageRs.close();
                        checkUsage.close();
                    }
                    System.out.println("|");
                }

                timeslotRs.close();

                // Print day summary
                System.out.println("\n" + day + " Summary:");
                System.out.println("  Used slots: " + dayUsedSlots);
                System.out.println("  Not used slots: " + dayNotUsedSlots);
                System.out.println("  Total slots: " + (dayUsedSlots + dayNotUsedSlots));
                double dayUtilization = (dayUsedSlots + dayNotUsedSlots) > 0
                        ? (dayUsedSlots * 100.0 / (dayUsedSlots + dayNotUsedSlots))
                        : 0;
                System.out.printf("  Utilization: %.2f%%\n", dayUtilization);

                // Add to total counters
                totalUsedSlots += dayUsedSlots;
                totalNotUsedSlots += dayNotUsedSlots;
            }

            // Print overall summary
            System.out.println("\n═══════════════════════════════════════════════════════════════════════════════════════════");
            System.out.println("                                    OVERALL SUMMARY");
            System.out.println("═══════════════════════════════════════════════════════════════════════════════════════════");
            System.out.println("Total used slots (all days): " + totalUsedSlots);
            System.out.println("Total not used slots (all days): " + totalNotUsedSlots);
            System.out.println("Total slots (all days): " + (totalUsedSlots + totalNotUsedSlots));
            double totalUtilization = (totalUsedSlots + totalNotUsedSlots) > 0
                    ? (totalUsedSlots * 100.0 / (totalUsedSlots + totalNotUsedSlots))
                    : 0;
            System.out.printf("Overall utilization: %.2f%%\n", totalUtilization);

        } catch (SQLException e) {
            System.out.println("❌ Error displaying timeslot usage:");
            e.printStackTrace();
        }
    }

    // Helper class for room information
    private static class RoomInfo {
        int id;
        String name;

        RoomInfo(int id, String name) {
            this.id = id;
            this.name = name;
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