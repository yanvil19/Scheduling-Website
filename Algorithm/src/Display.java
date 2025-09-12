import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;

public class Display {

    public void displayAllCourses() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn != null) {
                String sql = "SELECT Code, `Course Title`, Term, `Enrollee Year`, Program, `Lec hrs`, `Lab Hrs` FROM term2Table";
                Statement stmt = conn.createStatement();
                ResultSet rs = stmt.executeQuery(sql);

                System.out.println("\n--- All Courses ---");

                // Header row
                System.out.printf("%-10s %-30s %-6s %-12s %-10s %-8s %-8s%n",
                        "Code", "Course Title", "Term", "Year", "Program", "Lec", "Lab");
                System.out.println("------------------------------------------------------------------------------------------");

                // Data rows
                while (rs.next()) {
                    System.out.printf("%-10s %-30s %-6d %-12d %-10s %-8.1f %-8d%n",
                            rs.getString("Code"),
                            rs.getString("Course Title"),
                            rs.getInt("Term"),
                            rs.getInt("Enrollee Year"),
                            rs.getString("Program"),
                            rs.getDouble("Lec hrs"),
                            rs.getInt("Lab Hrs")
                    );
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
