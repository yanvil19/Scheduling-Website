import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;

public class Display {

    public void displayAllCourses() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn != null) {
                String sql = "SELECT SubjectID, `SubjectCode`, `SubjectName`, `IsLab`, `ProgramID`, `YearLevelID` FROM term2courses";
                Statement stmt = conn.createStatement();
                ResultSet rs = stmt.executeQuery(sql);

                System.out.println("\n--- All Courses ---");

                // Header row
                System.out.printf("%-10s %-15s %-50s %-6s %-10s %-12s%n",
                        "SubjectID", "SubjectCode", "SubjectName", "IsLab", "ProgramID", "YearLevelID");
                System.out.println("-".repeat(105));

                // Data rows
                while (rs.next()) {
                    System.out.printf("%-10s %-15s %-50s %-6s %-10s %-12s%n",
                            rs.getString("SubjectID"),
                            rs.getString("SubjectCode"),
                            rs.getString("SubjectName"),
                            rs.getString("IsLab"),
                            rs.getString("ProgramID"),
                            rs.getString("YearLevelID")
                    );
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}