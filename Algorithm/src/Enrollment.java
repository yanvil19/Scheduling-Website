import java.util.Scanner;

public class Enrollment {
    private static final int MAX_SECTION_SIZE = 40;
    private static final String[] PROGRAMS = {"BSIT", "BSIS", "BSCS"};
    private static final String[] YEARS = {"1st year", "2nd year", "3rd year", "4th year"};

    public void handleEnrollment() {
        Scanner scanner = new Scanner(System.in);

        System.out.println("\n--- Term 2 ---");

        // Programs priority order: BSIT -> BSIS -> BSCS
        for (String program : PROGRAMS) {
            System.out.println("\n*** " + program + " ***");

            // Years priority: 4th year -> 3rd year -> 2nd year -> 1st year
            for (int year = YEARS.length - 1; year >= 0; year--) {
                System.out.print("Input " + program + " " + YEARS[year] + " enrollees (40 per section): ");
                int enrollees = scanner.nextInt();

                int sections = enrollees / MAX_SECTION_SIZE;
                if (enrollees % MAX_SECTION_SIZE != 0) {
                    sections++;
                }

                System.out.println("  → Sections: " + sections + " (" + enrollees + " enrollees)");
            }
        }
    }
}
