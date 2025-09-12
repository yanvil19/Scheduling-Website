import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;

public class Display {

    public void displayAllCourses() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn != null) {
                String sql = "SELECT Code, `Course_Title`, Term, `Enrollee_Year`, Program, `Lec_hrs`, `Lab_Hrs` FROM term2Table";
                Statement stmt = conn.createStatement();
                ResultSet rs = stmt.executeQuery(sql);

                System.out.println("\n--- All Courses ---");

                // Header row
                System.out.printf("%-10s %-45s %-6s %-6s %-10s %-8s %-8s%n",
                        "Code", "Course_Title", "Term", "Year", "Program", "Lec", "Lab");
                System.out.println("------------------------------------------------------------------------------------------");

                // Data rows
                while (rs.next()) {
                    System.out.printf("%-10s %-45s %-6d %-6d %-10s %-8.1f %-8d%n",
                            rs.getString("Code"),
                            rs.getString("Course_Title"),
                            rs.getInt("Term"),
                            rs.getInt("Enrollee_Year"),
                            rs.getString("Program"),
                            rs.getDouble("Lec_hrs"),
                            rs.getInt("Lab_Hrs")
                    );
                }
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
    }
}
