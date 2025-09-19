
---

## ⚙️ Requirements

- **Java 17+** (or your installed JDK version)
- **IntelliJ IDEA** (or any Java IDE)
- **MySQL Server** (accessible through phpMyAdmin)
- **MySQL Connector/J** (JDBC driver)

---

## 🗄 Database Setup

1. Open **phpMyAdmin**.
2. Create a database (if not already created):
   ```sql
   CREATE DATABASE schedulingdb;
3. Import schedulingdb.sql into schedulingdb


## Commands

**Branch Switching**
- git fetch origin
- git checkout WithOnlineClassBranch
- git pull origin WithOnlineClassBranch

- git fetch origin
- git checkout Main
- git pull origin Main

**Class Running**

- **Be in the Algorithm/src directory**javac -d ..\..\bin *.java
- **Be in the root directory**java -cp "bin;mysql-connector-j-9.4.0.jar" Main


## Algorithm
🧩 Main Pseudo Algorithm: generateSchedule()

ALGORITHM GenerateSchedule
BEGIN
  1. Initialize Data Structures
     - Clear lists: sections, subjects, rooms, timeslots, schedule, unscheduled

  2. Load Data
     - CALL loadData()

  3. Sort Sections by Priority
     - Program Priority: IT (1) → IS (2) → CS (3)
     - Year Level Priority: 4th → 3rd → 2nd → 1st

  4. Initialize Conflict Tracking
     - used = {} → "roomID-timeslotID-day"
     - sectionSlotDayUsage = {} → "sectionID-timeslotID-day"
     - days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]

  5. Main Scheduling Loop
     FOR each section
       FOR each subject
         - Skip if program/year mismatch
         - IF subject.totalTime > 2.0 THEN
             → CALL tryPlaceOnPartnerDays()
           ELSE
             → CALL tryPlaceOnSingleDay()
         - IF placement fails THEN
             → Add subject to unscheduled list

  6. Display Results
     - CALL printSchedule()

END


🔁 Sub-Algorithm: tryPlacePartnerDays()

ALGORITHM tryPlacePartnerDays(section, subject, used, sectionSlotDayUsage, days)
BEGIN
  1. Compute Session Duration
     - hoursPerSession = subject.totalTime ÷ 2
     - partnerPairs = ["Monday", "Tuesday", "Wednesday"]

  2. Loop Through Resources
     FOR each timeslot IN timeslots
       FOR each room IN rooms
         - IF room.type ≠ subject.type THEN
             → CONTINUE

         FOR each firstDay IN partnerPairs
           - secondDay = getPartnerDay(firstDay)

           - Build Conflict Keys:
               sectionSlotDay1 = sectionID-timeslotID-firstDay
               sectionSlotDay2 = sectionID-timeslotID-secondDay
               roomSlotDay1 = roomID-timeslotID-firstDay
               roomSlotDay2 = roomID-timeslotID-secondDay

           - IF all keys are free THEN
               → Create 2 schedule entries (firstDay + secondDay)
               → Mark resources as used
               → RETURN true

  3. Placement Failed
     - RETURN false
END


📅 Sub-Algorithm: tryPlaceOnSingleDay()

ALGORITHM tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days)
BEGIN
  1. Loop Through Resources
     FOR each timeslot IN timeslots
       FOR each room IN rooms
         - IF room.type ≠ subject.type THEN
             → CONTINUE

         FOR each day IN days
           - Build Conflict Keys:
               sectionSlotDay = sectionID-timeslotID-day
               roomSlotDay = roomID-timeslotID-day

           - IF keys are free THEN
               → Create schedule entry
               → Mark resources as used
               → RETURN true

  2. Placement Failed
     - RETURN false
END





