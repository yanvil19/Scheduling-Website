
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


🧩 Main Pseudo Algorithm: generateSchedule()

ALGORITHM GenerateSchedule
BEGIN
1. Initialize Data Structures
   - Clear lists: sections, subjects, rooms, timeslots, schedule, unscheduled

2. Load Data
   - CALL loadData()

3. Sort Sections by Priority
   - Program: IT (1) → IS (2) → CS (3)
   - Year Level: 4th → 3rd → 2nd → 1st

4. Initialize Conflict Tracking
   - used = {} → "roomID-timeslotID-day"
   - sectionSlotDayUsage = {} → "sectionID-timeslotID-day"
   - days = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat"]

5. Main Scheduling Loop
   - For each section
     - For each subject
       - Skip if program/year mismatch
       - If subject.totalTime > 2.0 → tryPlaceOnPartnerDays()
       - Else → tryPlaceOnSingleDay()
       - If placement fails → add to unscheduled

6. Display Results
   - CALL printSchedule()

END


🔀 Sub-Algorithm: tryPlaceOnPartnerDays()

ALGORITHM tryPlaceOnPartnerDays(section, subject, used, sectionSlotDayUsage, days)
BEGIN
    hoursPerSession = subject.totalTime ÷ 2
    partnerPairs = ["Monday", "Tuesday", "Wednesday"]

    For each timeslot in timeslots
        For each room in rooms
            If room.type ≠ subject.type → continue

            For each firstDay in partnerPairs
                secondDay = getPartnerDay(firstDay)

                Build conflict keys:
                    sectionSlotDay1, sectionSlotDay2
                    roomSlotDay1, roomSlotDay2

                If all free →
                    Create 2 schedule entries (firstDay + secondDay)
                    Mark resources as used
                    RETURN true

RETURN false
END


📅 Sub-Algorithm: tryPlaceOnSingleDay()

ALGORITHM tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days)
BEGIN
    For each timeslot in timeslots
        For each room in rooms
            If room.type ≠ subject.type → continue

            For each day in days
                Build conflict keys:
                    sectionSlotDay, roomSlotDay

                If free →
                    Create schedule entry
                    Mark resources as used
                    RETURN true

RETURN false
END

