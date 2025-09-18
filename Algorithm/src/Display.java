import java.sql.Connection;
import java.sql.ResultSet;
import java.sql.Statement;

public class Display {

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
}
