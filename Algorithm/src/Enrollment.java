import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;
import java.util.Scanner;

public class Enrollment {
    private static final String[] PROGRAMS = {"BSIT", "BSIS", "BSCS"};
    private static final String[] YEARS = {"1st year", "2nd year", "3rd year", "4th year"};

    public static void handleEnrollment() {
        Scanner scanner = new Scanner(System.in);

        System.out.println("\n--- Term 2 ---");

        try (Connection conn = JDBC.getConnection()) {
            if (conn == null) {
                System.out.println("❌ Database connection failed!");
                return;
            }

            // Clear old records
            conn.createStatement().execute("TRUNCATE TABLE programyearcounts");

            int countId = 1; // Counter for CountID

            for (int programIndex = 0; programIndex < PROGRAMS.length; programIndex++) {
                String program = PROGRAMS[programIndex];
                int programId = programIndex + 1; // Assuming ProgramID starts from 1

                System.out.println("\n*** " + program + " ***");

                // Collect inputs (1st year to 4th year)
                for (int year = 0; year < YEARS.length; year++) {
                    System.out.print("Input " + program + " " + YEARS[year] + " enrollees: ");
                    int enrollees = scanner.nextInt();

                    // Save directly into programyearcounts table
                    try (PreparedStatement stmt = conn.prepareStatement(
                            "INSERT INTO programyearcounts (CountID, ProgramID, YearLevelID, StudentCount) VALUES (?, ?, ?, ?)")) {
                        stmt.setInt(1, countId++);
                        stmt.setInt(2, programId);
                        stmt.setInt(3, year + 1); // YearLevelID (1-4)
                        stmt.setInt(4, enrollees);
                        stmt.executeUpdate();
                    }
                }
            }

            System.out.println("\n✅ Enrollee data saved to programyearcounts table!");

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
