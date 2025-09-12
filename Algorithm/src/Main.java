import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        System.out.println("🚀 Starting Program...");

        Display display = new Display();
        Enrollment enroll = new Enrollment();
        Algorithm algo = new Algorithm();
        Scanner scanner = new Scanner(System.in);

        int choice;

        do {
            System.out.println("\n===== MENU =====");
            System.out.println("1. Display all courses");
            System.out.println("2. Get number of Enrollees");
            System.out.println("3. Run scheduling algorithm (placeholder)");
            System.out.println("0. Exit");
            System.out.print("Enter your choice: ");

            choice = scanner.nextInt();

            switch (choice) {
                case 1:
                    display.displayAllCourses();
                    break;
                case 2:
                    enroll.handleEnrollment();
                    break;
                case 3:
//                    algo.runScheduler();
                    break;
                case 0:
                    System.out.println("👋 Exiting...");
                    break;
                default:
                    System.out.println("❌ Invalid choice, try again!");
            }

        } while (choice != 0);

        scanner.close();
    }
}
