import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        System.out.println("🚀 Starting Program...");

        Display display = new Display();
        Enrollment enroll = new Enrollment();
        Algorithm algo = new Algorithm();
        Scanner scanner = new Scanner(System.in);

        int choice;
        char choice2;

        do {
            System.out.println("\n===== MENU =====");
            System.out.println("1. Display all courses");
            System.out.println("2. Get number of Enrollees");
            System.out.println("3. Run scheduling algorithm (placeholder)");
            System.out.println("4. Create Sections");
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
                case 4: // 👈 added case
                    System.out.print("Are you sure you want to create sections? This will clear existing sections and schedules. (Y/N): ");
                    choice2 = Character.toUpperCase(scanner.next().charAt(0));
                    if(choice2 == 'Y'){
                        SectionCreator.createSections();
                    }
                    else{
                        System.out.println("❌ Canceled section creation!");
                    }
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
