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
            conn.createStatement().execute("TRUNCATE TABLE enrolleesTable");

            for (String program : PROGRAMS) {
                System.out.println("\n*** " + program + " ***");

                // Collect inputs (store raw data only)
                for (int year = YEARS.length - 1; year >= 0; year--) {
                    System.out.print("Input " + program + " " + YEARS[year] + " enrollees: ");
                    int enrollees = scanner.nextInt();

                    // Save directly into table
                    try (PreparedStatement stmt = conn.prepareStatement(
                            "INSERT INTO enrolleesTable (program, yearLevel, enrollees) VALUES (?, ?, ?)")) {
                        stmt.setString(1, program);
                        stmt.setInt(2, year + 1);
                        stmt.setInt(3, enrollees);
                        stmt.executeUpdate();
                    }
                }
            }

            System.out.println("\n✅ Enrollee data saved to enrolleesTable!");

        } catch (SQLException e) {
            e.printStackTrace();
        }
    }
}
