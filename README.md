
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
ALGORITHM GenerateSchedule
BEGIN
    // Initialize data structures
    Clear all lists (sections, subjects, rooms, timeslots, schedule, unscheduled)
    
    // Load data from database
    CALL loadData()
    
    // Prioritize sections by program and year level
    Sort sections by:
        1. Program priority (IT=1, IS=2, CS=3)
        2. Year level (higher years first: 4th > 3rd > 2nd > 1st)
    
    // Initialize conflict tracking
    used = empty set                    // tracks "roomID-timeslotID-day" combinations
    sectionSlotDayUsage = empty set     // tracks "sectionID-timeslotID-day" combinations
    days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]
    
    // Main scheduling loop
    FOR each section in sections DO
        FOR each subject in subjects DO
            // Filter: only schedule subjects that match section's program and year
            IF subject.programID ≠ section.programID OR subject.yearLevel ≠ section.yearLevel THEN
                CONTINUE to next subject
            END IF
            
            placed = false
            
            // Determine scheduling strategy based on total time
            IF subject.totalTime > 2.0 THEN
                // Long courses: split across partner days
                placed = tryPlaceOnPartnerDays(section, subject, used, sectionSlotDayUsage, days)
            ELSE
                // Short courses: single day scheduling
                placed = tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days)
            END IF
            
            // Track unscheduled courses
            IF NOT placed THEN
                ADD "subject.code (section.name) - subject.type" to unscheduled
            END IF
        END FOR
    END FOR
    
    // Display results
    CALL printSchedule()
END


🔁 Sub-Algorithm: tryPlacePartnerDays()

ALGORITHM tryPlaceOnPartnerDays(section, subject, used, sectionSlotDayUsage, days)
BEGIN
    hoursPerSession = subject.totalTime / 2.0
    partnerPairs = ["Monday", "Tuesday", "Wednesday"]  // First days of each partner pair
    
    FOR each timeslot in timeslots DO
        FOR each room in rooms DO
            // Room type validation
            IF NOT room.typeMatches(subject) THEN
                CONTINUE to next room
            END IF
            
            FOR each firstDay in partnerPairs DO
                secondDay = getPartnerDay(firstDay)  // Mon↔Thu, Tue↔Fri, Wed↔Sat
                
                // Generate conflict keys
                sectionSlotDay1 = "section.id-timeslot.id-firstDay"
                sectionSlotDay2 = "section.id-timeslot.id-secondDay" 
                roomSlotDay1 = "room.id-timeslot.id-firstDay"
                roomSlotDay2 = "room.id-timeslot.id-secondDay"
                
                // Check availability for both days
                IF sectionSlotDay1 NOT IN sectionSlotDayUsage AND
                   sectionSlotDay2 NOT IN sectionSlotDayUsage AND
                   roomSlotDay1 NOT IN used AND
                   roomSlotDay2 NOT IN used THEN
                   
                    // Create schedule entries for both days
                    entry1 = new ScheduleEntry(section.name, subject.code, subject.name, 
                                             subject.type, room.name, timeslot, firstDay, hoursPerSession)
                    entry2 = new ScheduleEntry(section.name, subject.code, subject.name,
                                             subject.type, room.name, timeslot, secondDay, hoursPerSession)
                    
                    ADD entry1 to schedule
                    ADD entry2 to schedule
                    
                    // Mark resources as used
                    ADD sectionSlotDay1 to sectionSlotDayUsage
                    ADD sectionSlotDay2 to sectionSlotDayUsage
                    ADD roomSlotDay1 to used
                    ADD roomSlotDay2 to used
                    
                    RETURN true  // Successfully placed
                END IF
            END FOR
        END FOR
    END FOR
    
    RETURN false  // Could not place
END


📅 Sub-Algorithm: tryPlaceOnSingleDay()

ALGORITHM tryPlaceOnSingleDay(section, subject, used, sectionSlotDayUsage, days)
BEGIN
    FOR each timeslot in timeslots DO
        FOR each room in rooms DO
            // Room type validation
            IF NOT room.typeMatches(subject) THEN
                CONTINUE to next room
            END IF
            
            FOR each day in days DO
                // Generate conflict keys
                sectionSlotDay = "section.id-timeslot.id-day"
                roomSlotDay = "room.id-timeslot.id-day"
                
                // Check availability
                IF sectionSlotDay NOT IN sectionSlotDayUsage AND
                   roomSlotDay NOT IN used THEN
                   
                    // Create schedule entry
                    entry = new ScheduleEntry(section.name, subject.code, subject.name,
                                            subject.type, room.name, timeslot, day, subject.totalTime)
                    
                    ADD entry to schedule
                    
                    // Mark resources as used
                    ADD sectionSlotDay to sectionSlotDayUsage
                    ADD roomSlotDay to used
                    
                    RETURN true  // Successfully placed
                END IF
            END FOR
        END FOR
    END FOR
    
    RETURN false  // Could not place
END
