import java.util.Scanner;

public class Main {
    public static void main(String[] args) {
        System.out.println("🚀 Starting Program...");

        Display display = new Display();
        Enrollment enroll = new Enrollment();
        Scheduler sched = new Scheduler();
        ScheduleTimingManager scheduleTimingManager = new ScheduleTimingManager();
        Scanner scanner = new Scanner(System.in);

        int choice;
        char choice2;

        do {
            System.out.println("\n==============================");
            System.out.println("       SCHEDULING SYSTEM       ");
            System.out.println("==============================");
            System.out.println("1. Display all courses");
            System.out.println("2. Get number of Enrollees");
            System.out.println("3. Generate Sections");
            System.out.println("4. Generate & Display Schedule");
            System.out.println("5. Save Schedule to DB");
            System.out.println("6. Display Room Usage by Timeslot");
            System.out.println("0. Exit");
            System.out.println("==============================");
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
                    ScheduleTimingManager.calculateActualTimes(sched.schedule);
                    display.showSchedule(sched.schedule, sched.unscheduled);
                    break;

                case 5:
                    if (sched.schedule.isEmpty()) {
                        System.out.println("⚠️ No schedule generated yet. Please generate first (option 4).");
                    } else {
                        System.out.print("Save schedule to database? (Y/N): ");
                        choice2 = Character.toUpperCase(scanner.next().charAt(0));
                        if (choice2 == 'Y') {
                            // First save schedule to get IDs
                            sched.saveScheduleToDatabase();
                            
                            // Then process actual times
                            ScheduleTimingManager.calculateActualTimes(sched.schedule);
                            ScheduleTimingManager.saveActualTimesToDatabase();
                            
                            System.out.println("✅ Schedule and actual times saved successfully!");
                        }
                    }
                    break;

                case 6:
                    // Display room usage by timeslot (reads from saved schedule in DB)
                    display.displayTimeslotUsage();
                    break;

                case 0:
                    System.out.println("👋 Exiting program...");
                    System.out.println("Thank you for using the Scheduling System!");
                    break;

                default:
                    System.out.println("❌ Invalid choice, try again!");
            }

        } while (choice != 0);

        scanner.close();
    }
}