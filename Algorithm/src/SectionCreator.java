import java.sql.*;
import java.time.Year;

public class SectionCreator {

    public static void main(String[] args) {
        createSections();
    }

    public static void createSections() {
        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            // Clear schedules and sections safely
            Statement stmt = conn.createStatement();
            stmt.execute("SET FOREIGN_KEY_CHECKS = 0");
            stmt.execute("TRUNCATE TABLE schedule");
            stmt.execute("TRUNCATE TABLE Sections");
            stmt.execute("SET FOREIGN_KEY_CHECKS = 1");
            System.out.println("🗑️ Old schedules and sections cleared.");


            // Fetch program + year counts
            String sql = """
                SELECT pyc.ProgramID, pyc.YearLevelID, pyc.StudentCount, p.Code
                FROM programyearcounts pyc
                JOIN Programs p ON pyc.ProgramID = p.ProgramID
            """;

            PreparedStatement ps = conn.prepareStatement(sql);
            ResultSet rs = ps.executeQuery();

            int totalSections = 0;
            int currentYear = Year.now().getValue(); // system year

            while (rs.next()) {
                int programId = rs.getInt("ProgramID");
                int yearLevelId = rs.getInt("YearLevelID");
                int studentCount = rs.getInt("StudentCount");
                String code = rs.getString("Code");

                // enrollment year = currentYear - yearLevelId + 1
                int enrollmentYear = currentYear - yearLevelId + 1;
                String yy = String.valueOf(enrollmentYear).substring(2); // last two digits

                // Calculate number of sections (40 per section)
                int numSections = (int) Math.ceil(studentCount / 40.0);

                for (int i = 1; i <= numSections; i++) {
                    char sectionLetter = (char) ('A' + i - 1);

                    // Format: BS + code + YY + "1" + Letter
                    String sectionName = "BS" + code + yy + "1" + sectionLetter;

                    PreparedStatement insert = conn.prepareStatement(
                        "INSERT INTO Sections (SectionName, ProgramID, YearLevelID) VALUES (?,?,?)"
                    );
                    insert.setString(1, sectionName);
                    insert.setInt(2, programId);
                    insert.setInt(3, yearLevelId);
                    insert.executeUpdate();

                    System.out.println("✅ Created Section: " + sectionName + " (" + studentCount + " students)");
                    totalSections++;
                }
            }

            System.out.println("\n🎉 Section creation complete! Total sections created: " + totalSections);

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
