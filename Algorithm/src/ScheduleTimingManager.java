import java.sql.*;
import java.time.LocalTime;
import java.util.ArrayList;
import java.util.List;

public class ScheduleTimingManager {

    private static class TimingEntry {
        public int scheduleId;
        public LocalTime start;
        public LocalTime end;
        public String label;
    }

    private static final List<TimingEntry> pendingTimings = new ArrayList<>();

    public static void calculateActualTimes(List<Scheduler.ScheduleEntry> scheduleEntries) {
        pendingTimings.clear();

        for (Scheduler.ScheduleEntry entry : scheduleEntries) {
            try {
                // Parse the start time from timeSlotStart
                if (entry.timeSlotStart == null || entry.timeSlotStart.trim().isEmpty()) {
                    System.out.println("⚠️ Invalid start time for: " + entry.subjectCode);
                    continue;
                }

                LocalTime start = LocalTime.parse(entry.timeSlotStart);
                LocalTime end = start.plusMinutes((long)(entry.duration * 60));
                String label = formatDuration(entry.duration);

                TimingEntry timing = new TimingEntry();
                timing.scheduleId = entry.scheduleId;
                timing.start = start;
                timing.end = end;
                timing.label = label;

                pendingTimings.add(timing);
                
                // Store calculated times in the entry for display
                entry.actualStartTime = start.toString();
                entry.actualEndTime = end.toString();

            } catch (Exception e) {
                System.out.println("⚠️ Failed to calculate time for: " + entry.subjectCode);
                e.printStackTrace();
            }
        }
    }

    public static void saveActualTimesToDatabase() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Failed to connect to database.");
                return;
            }

            for (TimingEntry timing : pendingTimings) {
                int actualTimeId = insertActualTime(conn, timing.start, timing.end, timing.label);
                linkToSchedule(conn, timing.scheduleId, actualTimeId);
            }

            System.out.println("💾 Actual times saved to database.");
        } catch (SQLException e) {
            e.printStackTrace();
        }
    }

    private static int insertActualTime(Connection conn, LocalTime start, LocalTime end, String label) throws SQLException {
        String insert = "INSERT INTO Actual_time (ActualStartTime, ActualEndTime, DurationLabel) VALUES (?, ?, ?)";
        PreparedStatement stmt = conn.prepareStatement(insert, Statement.RETURN_GENERATED_KEYS);
        stmt.setTime(1, Time.valueOf(start));
        stmt.setTime(2, Time.valueOf(end));
        stmt.setString(3, label);
        stmt.executeUpdate();

        ResultSet keys = stmt.getGeneratedKeys();
        if (keys.next()) return keys.getInt(1);
        throw new SQLException("Failed to retrieve ActualTimeID.");
    }

    private static void linkToSchedule(Connection conn, int scheduleId, int actualTimeId) throws SQLException {
        String update = "UPDATE schedule SET ActualTimeID = ? WHERE ScheduleID = ?";
        PreparedStatement stmt = conn.prepareStatement(update);
        stmt.setInt(1, actualTimeId);
        stmt.setInt(2, scheduleId);
        stmt.executeUpdate();
    }

    private static String formatDuration(double minutes) {
        int hrs = (int) minutes / 60;
        int mins = (int) minutes % 60;
        return hrs + "h " + mins + "m";
    }
}
