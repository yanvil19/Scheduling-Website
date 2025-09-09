<?php
require_once "db.php"; // Include database connection and query functions

/**
 * SECTION GENERATION FUNCTION
 * Calculates how many sections are needed based on enrollees
 */

/**
 * Generate sections for a given course/year based on enrollment
 * 
 * @param string $course - Course program (BSIT, BSIS, BSCS)
 * @param int $year - Year level (1-4)
 * @param int $enrollees - Number of enrolled students
 * @param int $maxClassSize - Maximum students per section (default: 40)
 * @return array - Array of section information
 */
function generateSections($course, $year, $enrollees, $maxClassSize = 40) {
    $sections = [];
    // Calculate number of sections needed (round up)
    $sectionCount = ceil($enrollees / $maxClassSize);

    // Create section records
    for ($i = 1; $i <= $sectionCount; $i++) {
        $sections[] = [
            'year' => $year,
            'section' => $i
        ];
    }

    return $sections;
}

// Initialize data storage array
$allData = [];

// ================================================================
// FORM DATA PROCESSING
// Process POST data to generate sections and assign courses
// ================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Program mapping: form field prefix => program name
    $courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
    // Year mapping: form field prefix => year number
    $years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

    // Process each program
    foreach ($courses as $courseKey => $courseLabel) {
        // Process each year level
        foreach ($years as $yearKey => $yearNumber) {
            // Build field name (e.g., "firstyr_it_enrollees")
            $fieldName = "{$yearKey}_{$courseKey}_enrollees";
            
            // Check if this field was submitted
            if (isset($_POST[$fieldName])) {
                $enrollees = (int) $_POST[$fieldName];
                
                // Generate sections for this program/year
                $sections = generateSections($courseLabel, $yearNumber, $enrollees);

                // Initialize data structure if needed
                if (!isset($allData[$courseLabel][$yearNumber])) {
                    $allData[$courseLabel][$yearNumber] = [];
                }

                // Get all courses for this program & year from database
                $coursesList = getCoursesByYearAndProgram($db, $yearNumber, $courseLabel);

                // Group courses by term (1st, 2nd, 3rd semester)
                $groupedByTerm = [1 => [], 2 => [], 3 => []];
                foreach ($coursesList as $course) {
                    $term = (int)$course['Term'];
                    if (isset($groupedByTerm[$term])) {
                        $groupedByTerm[$term][] = $course;
                    }
                }

                // Assign courses to each section
                foreach ($sections as &$sec) {
                    $sec['courses'] = $groupedByTerm;
                }

                // Add sections to the main data array
                $allData[$courseLabel][$yearNumber] = array_merge(
                    $allData[$courseLabel][$yearNumber],
                    $sections
                );
            }
        }
    }
}

// =============================
// SCHEDULING ALGORITHM HELPER FUNCTIONS
// Same functions as in algorithm.php for consistency
// =============================

/**
 * Initialize a 3D grid for room scheduling
 * Creates structure: [room][day][time] = null
 */
function initGrid($rooms, $days, $times) {
    $grid = [];
    foreach ($rooms as $r) {
        foreach ($days as $d) {
            foreach ($times as $t) {
                $grid[$r][$d][$t] = null;
            }
        }
    }
    return $grid;
}

/**
 * Check if a room slot is available
 */
function isRoomFree($grid, $room, $day, $time) {
    return !isset($grid[$room][$day][$time]) || $grid[$room][$day][$time] === null;
}

/**
 * Check if a section is free at given time (no conflicts)
 */
function isSectionFree($sectionSchedule, $section, $day, $time) {
    return !isset($sectionSchedule[$section][$day][$time]);
}

/**
 * Reserve a room slot
 */
function reserveSlot(&$grid, $room, $day, $time, $assignment) {
    $grid[$room][$day][$time] = $assignment;
}

/**
 * Reserve a section's time slot
 */
function reserveSectionSlot(&$sectionSchedule, $section, $day, $time, $assignment) {
    if (!isset($sectionSchedule[$section])) $sectionSchedule[$section] = [];
    if (!isset($sectionSchedule[$section][$day])) $sectionSchedule[$section][$day] = [];
    $sectionSchedule[$section][$day][$time] = $assignment;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Generated Schedule</title>
    <style>
        table { border-collapse: collapse; width: 100%; margin-bottom:20px; }
        th, td { padding: 6px 8px; border: 1px solid #ccc; text-align: left; }
        th { background: #f2f2f2; }
    </style>
</head>
<body>
    <a href="index.html">← Back to Index</a>
    <h1>Generated Schedule</h1>

    <?php if (!empty($allData)) : ?>
        <?php
        // ================================================================
        // SCHEDULING CONFIGURATION
        // Define available resources and constraints
        // ================================================================
        
        $lecture_rooms = ["Room 203", "Room 204"];                    // Available lecture rooms
        $lab_rooms = ["ComLab 1", "ComLab 5", "ComLab 6"];          // Available laboratory rooms
        $days = ["Mon","Tue","Wed","Thu","Fri","Sat"];               // Days of the week
        $time_blocks = [                                              // Time slots (2-hour blocks)
            "07:00-09:00","09:00-11:00","11:00-13:00",
            "13:00-15:00","15:00-17:00","17:00-19:00","19:00-21:00"
        ];
        
        // Initialize scheduling grids and tracking arrays
        $lectureGrid = initGrid($lecture_rooms, $days, $time_blocks);  // Track lecture room usage
        $labGrid = initGrid($lab_rooms, $days, $time_blocks);         // Track lab room usage
        $sectionSchedule = [];                                         // Track each section's schedule
        $assignmentsBySection = [];                                    // Group assignments by section

        // ================================================================
        // MAIN SCHEDULING LOOP
        // Process all programs, years, and sections
        // ================================================================
        
        foreach ($allData as $program => $years) {
            foreach ($years as $year => $sections) {
                foreach ($sections as $sec) {
                    // Generate section name (e.g., "BSIT-Y1-S1")
                    $sectionName = $program . "-Y" . $year . "-S" . $sec['section'];
                    
                    // Process each term (semester)
                    for ($term = 1; $term <= 3; $term++) {
                        // Process each course in the current term
                        foreach ($sec['courses'][$term] as $courseRow) {
                            $lecHrs = (int) $courseRow['Lec_Hrs'];      // Lecture hours per week
                            $labHrs = (int) $courseRow['Lab_Hrs'];      // Lab hours per week
                            $courseCode = $courseRow['Code'];           // Course code
                            $courseTitle = $courseRow['Course_Title'];  // Course title

                            // ================================
                            // LECTURE SCHEDULING
                            // Assign lecture time slots
                            // ================================
                            if ($lecHrs > 0) {
                                // Calculate number of 2-hour slots needed
                                $slotsNeeded = (int) ceil($lecHrs / 2);
                                $slotsAssigned = 0;
                                
                                // Try to assign slots (priority: day -> time -> room)
                                foreach ($days as $day) {
                                    foreach ($time_blocks as $time) {
                                        foreach ($lecture_rooms as $room) {
                                            // Stop when enough slots are assigned
                                            if ($slotsAssigned >= $slotsNeeded) break 3;
                                            
                                            // Check room availability
                                            if (!isRoomFree($lectureGrid,$room,$day,$time)) continue;
                                            
                                            // Check section availability (no conflicts)
                                            if (!isSectionFree($sectionSchedule,$sectionName,$day,$time)) continue;
                                            
                                            // Create assignment record
                                            $a = [
                                                'term' => $term,
                                                'day' => $day,
                                                'course_code' => $courseCode,
                                                'course_title' => $courseTitle,
                                                'section' => $sectionName,
                                                'time' => $time,
                                                'room' => $room,
                                                'part' => 'Lec'
                                            ];
                                            
                                            // Reserve slots in both tracking systems
                                            reserveSlot($lectureGrid,$room,$day,$time,$a);
                                            reserveSectionSlot($sectionSchedule,$sectionName,$day,$time,$a);
                                            
                                            // Store assignment grouped by section
                                            $assignmentsBySection[$sectionName][$term][] = $a;
                                            $slotsAssigned++;
                                        }
                                    }
                                }
                            }

                            // ================================
                            // LABORATORY SCHEDULING
                            // Assign lab time slots
                            // ================================
                            if ($labHrs > 0) {
                                // Calculate number of 2-hour slots needed
                                $slotsNeeded = (int) ceil($labHrs / 2);
                                $slotsAssigned = 0;
                                
                                // Try to assign slots (priority: day -> time -> room)
                                foreach ($days as $day) {
                                    foreach ($time_blocks as $time) {
                                        foreach ($lab_rooms as $room) {
                                            // Stop when enough slots are assigned
                                            if ($slotsAssigned >= $slotsNeeded) break 3;
                                            
                                            // Check room availability
                                            if (!isRoomFree($labGrid,$room,$day,$time)) continue;
                                            
                                            // Check section availability (no conflicts)
                                            if (!isSectionFree($sectionSchedule,$sectionName,$day,$time)) continue;
                                            
                                            // Create assignment record
                                            $a = [
                                                'term' => $term,
                                                'day' => $day,
                                                'course_code' => $courseCode,
                                                'course_title' => $courseTitle,
                                                'section' => $sectionName,
                                                'time' => $time,
                                                'room' => $room,
                                                'part' => 'Lab'
                                            ];
                                            
                                            // Reserve slots in both tracking systems
                                            reserveSlot($labGrid,$room,$day,$time,$a);
                                            reserveSectionSlot($sectionSchedule,$sectionName,$day,$time,$a);
                                            
                                            // Store assignment grouped by section
                                            $assignmentsBySection[$sectionName][$term][] = $a;
                                            $slotsAssigned++;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        ?>

        <?php 
        // ================================================================
        // DISPLAY RESULTS
        // Show schedules organized by section and term
        // ================================================================
        ?>
        
        <?php foreach ($assignmentsBySection as $section => $terms): ?>
            <h2>Schedule for <?php echo htmlspecialchars($section); ?></h2>
            <?php foreach ($terms as $term => $assignments): ?>
                <h3>Term <?php echo $term; ?></h3>
                <table>
                    <tr>
                        <th>Day</th><th>Course</th><th>Time</th><th>Room</th><th>Part</th>
                    </tr>
                    <?php foreach ($assignments as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a['day']); ?></td>
                            <td><?php echo htmlspecialchars($a['course_code']." – ".$a['course_title']); ?></td>
                            <td><?php echo htmlspecialchars($a['time']); ?></td>
                            <td><?php echo htmlspecialchars($a['room']); ?></td>
                            <td><?php echo htmlspecialchars($a['part']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No data submitted. Please go back and enter enrollees.</p>
    <?php endif; ?>
</body>
</html>