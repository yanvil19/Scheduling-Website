import java.util.HashSet;
import java.util.List;
import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        System.out.println("🚀 Starting Program...");

        Display display = new Display();
        Enrollment enroll = new Enrollment();
        Scheduler sched = new Scheduler();
        Scanner scanner = new Scanner(System.in);

        int choice;
        char choice2;

        do {
            System.out.println("\n===== MENU =====");
            System.out.println("1. Display all courses");
            System.out.println("2. Get number of Enrollees");
            System.out.println("3. Generate Sections");
            System.out.println("4. Generate & Display Schedule");
            System.out.println("5. Save Schedule to DB");
            System.out.println("0. Exit");
            System.out.print("Enter your choice: ");

            choice = scanner.nextInt();

            switch (choice) {
                case 1:
                    display.showTerm2Courses();
                    break;

                case 2:
                    enroll.handleEnrollment();
                    break;

                case 3:
                    System.out.print("Are you sure you want to create sections? "
                            + "This will clear existing sections and schedules. (Y/N): ");
                    choice2 = Character.toUpperCase(scanner.next().charAt(0));
                    if (choice2 == 'Y') {
                        SectionCreator.createSections();
                    } else {
                        System.out.println("❌ Canceled section creation!");
                    }
                    break;

                case 4:
                    // Generate schedule then display it
                    sched.generateSchedule();
                    display.showSchedule(sched.schedule, sched.unscheduled);
                    break;

                case 5:
                    // Save schedule that was already generated
                    if (sched.schedule.isEmpty()) {
                        System.out.println("⚠️ No schedule generated yet. Please generate first (option 4).");
                    } else {
                        sched.saveScheduleToDatabase();
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
