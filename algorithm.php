<?php
require_once "db.php";

/**
 * GRID INITIALIZATION AND MANAGEMENT FUNCTIONS
 * These functions handle the creation and management of room scheduling grids
 */

/**
 * Initialize a 3D grid structure for room scheduling
 * Creates a nested array: [room][day][time] = null
 * 
 * @param array $rooms - List of available rooms
 * @param array $days - List of days (Mon, Tue, etc.)
 * @param array $times - List of time blocks
 * @return array - Initialized empty grid
 */
function initGrid($rooms, $days, $times) {
    $grid = [];
    foreach ($rooms as $r) {
        foreach ($days as $d) {
            foreach ($times as $t) {
                $grid[$r][$d][$t] = null; // Initialize each slot as empty
            }
        }
    }
    return $grid;
}

/**
 * Check if a specific room slot is available
 * 
 * @param array $grid - The room scheduling grid
 * @param string $room - Room name
 * @param string $day - Day of the week
 * @param string $time - Time block
 * @return bool - True if room is free, false if occupied
 */
function isRoomFree($grid, $room, $day, $time) {
    return !isset($grid[$room][$day][$time]) || $grid[$room][$day][$time] === null;
}

/**
 * Check if a section has a conflict at specific day/time
 * Prevents double-booking of sections
 * 
 * @param array $sectionSchedule - Section's current schedule
 * @param string $section - Section name
 * @param string $day - Day of the week
 * @param string $time - Time block
 * @return bool - True if section is free, false if already scheduled
 */
function isSectionFree($sectionSchedule, $section, $day, $time) {
    return !isset($sectionSchedule[$section][$day][$time]);
}

/**
 * Reserve a room slot by assigning a class to it
 * 
 * @param array &$grid - Reference to the room grid (modified in place)
 * @param string $room - Room name
 * @param string $day - Day of the week
 * @param string $time - Time block
 * @param array $assignment - Class assignment details
 */
function reserveSlot(&$grid, $room, $day, $time, $assignment) {
    $grid[$room][$day][$time] = $assignment;
}

/**
 * Reserve a time slot for a specific section
 * Tracks what each section is doing at each time
 * 
 * @param array &$sectionSchedule - Reference to section schedule (modified in place)
 * @param string $section - Section name
 * @param string $day - Day of the week
 * @param string $time - Time block
 * @param array $assignment - Class assignment details
 */
function reserveSectionSlot(&$sectionSchedule, $section, $day, $time, $assignment) {
    if (!isset($sectionSchedule[$section])) $sectionSchedule[$section] = [];
    if (!isset($sectionSchedule[$section][$day])) $sectionSchedule[$section][$day] = [];
    $sectionSchedule[$section][$day][$time] = $assignment;
}

// ================================================================
// SCHEDULING CONFIGURATION
// Define available resources and time constraints
// ================================================================

$lecture_rooms = ["Room 203", "Room 204"];                    // Rooms for lectures
$lab_rooms = ["ComLab 1", "ComLab 5", "ComLab 6"];          // Rooms for laboratory sessions
$days = ["Mon","Tue","Wed","Thu","Fri","Sat"];               // Available days
$time_blocks = [                                              // Available time slots (2-hour blocks)
    "07:00-09:00","09:00-11:00","11:00-13:00",
    "13:00-15:00","15:00-17:00","17:00-19:00","19:00-21:00"
];

// ================================================================
// INITIALIZE SCHEDULING GRIDS AND TRACKING ARRAYS
// ================================================================

$lectureGrid = initGrid($lecture_rooms, $days, $time_blocks);  // Track lecture room usage
$labGrid = initGrid($lab_rooms, $days, $time_blocks);         // Track lab room usage
$sectionSchedule = [];                                         // Track each section's schedule
$assignments = [];                                             // Store all successful assignments

// ================================================================
// PROGRAM AND YEAR MAPPING
// Maps form field names to actual program codes and year numbers
// ================================================================

$courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
$years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

// ================================================================
// MAIN SCHEDULING ALGORITHM
// Process each program, year, and generate sections based on enrollees
// ================================================================

foreach ($courses as $progKey => $progLabel) {
    foreach ($years as $yearKey => $yearNumber) {
        // Get number of enrollees from POST data
        $fieldName = "{$yearKey}_{$progKey}_enrollees";
        $enrollees = isset($_POST[$fieldName]) ? (int) $_POST[$fieldName] : 0;
        
        // Calculate number of sections needed (40 students per section max)
        $sections = ceil($enrollees / 40);
        
        // Generate sections for this program/year combination
        for ($s = 1; $s <= $sections; $s++) {
            $sectionName = "{$progLabel}-Y{$yearNumber}-{$s}";
            
            // Get all courses for this year and program from database
            $courseList = getCoursesByYearAndProgram($db, $yearNumber, $progLabel);
            
            // Process courses for each term (1st, 2nd, 3rd term)
            foreach ([1,2,3] as $term) {
                // Filter courses for current term
                $termCourses = array_filter($courseList, fn($c) => (int)$c['Term'] === $term);
                
                // Schedule each course in the current term
                foreach ($termCourses as $c) {
                    $lecHrs = (int)$c['Lec_Hrs'];    // Lecture hours per week
                    $labHrs = (int)$c['Lab_Hrs'];    // Lab hours per week
                    $code = $c['Code'];              // Course code
                    $title = $c['Course_Title'];     // Course title
                    
                    // ========================================
                    // LECTURE SCHEDULING
                    // Schedule lecture sessions if course has lecture hours
                    // ========================================
                    if ($lecHrs > 0) {
                        // Calculate slots needed (2 hours per slot, round up)
                        $slots = ceil($lecHrs / 2);
                        $assigned = 0;
                        
                        // Try to assign lecture slots
                        // Priority: Days -> Time blocks -> Rooms
                        foreach ($days as $d) {
                            foreach($time_blocks as $t) {
                                foreach($lecture_rooms as $r) {
                                    // Stop when we've assigned enough slots
                                    if ($assigned >= $slots) break 3;
                                    
                                    // Check if room is available
                                    if(!isRoomFree($lectureGrid, $r, $d, $t)) continue;
                                    
                                    // Check if section is available (no conflicts)
                                    if(!isSectionFree($sectionSchedule, $sectionName, $d, $t)) continue;
                                    
                                    // Create assignment record
                                    $a = [
                                        'term' => $term,
                                        'day' => $d,
                                        'course_code' => $code,
                                        'course_title' => $title,
                                        'section' => $sectionName,
                                        'time' => $t,
                                        'room' => $r,
                                        'part' => 'Lec'
                                    ];
                                    
                                    // Reserve the slot in both grids
                                    reserveSlot($lectureGrid, $r, $d, $t, $a);
                                    reserveSectionSlot($sectionSchedule, $sectionName, $d, $t, $a);
                                    
                                    // Add to assignments list and increment counter
                                    $assignments[] = $a;
                                    $assigned++;
                                }
                            }
                        }
                    }
                    
                    // ========================================
                    // LABORATORY SCHEDULING
                    // Schedule lab sessions if course has lab hours
                    // ========================================
                    if ($labHrs > 0) {
                        // Calculate slots needed (2 hours per slot, round up)
                        $slots = ceil($labHrs / 2);
                        $assigned = 0;
                        
                        // Try to assign lab slots
                        // Priority: Days -> Time blocks -> Rooms
                        foreach ($days as $d) {
                            foreach($time_blocks as $t) {
                                foreach($lab_rooms as $r) {
                                    // Stop when we've assigned enough slots
                                    if ($assigned >= $slots) break 3;
                                    
                                    // Check if room is available
                                    if(!isRoomFree($labGrid, $r, $d, $t)) continue;
                                    
                                    // Check if section is available (no conflicts)
                                    if(!isSectionFree($sectionSchedule, $sectionName, $d, $t)) continue;
                                    
                                    // Create assignment record
                                    $a = [
                                        'term' => $term,
                                        'day' => $d,
                                        'course_code' => $code,
                                        'course_title' => $title,
                                        'section' => $sectionName,
                                        'time' => $t,
                                        'room' => $r,
                                        'part' => 'Lab'
                                    ];
                                    
                                    // Reserve the slot in both grids
                                    reserveSlot($labGrid, $r, $d, $t, $a);
                                    reserveSectionSlot($sectionSchedule, $sectionName, $d, $t, $a);
                                    
                                    // Add to assignments list and increment counter
                                    $assignments[] = $a;
                                    $assigned++;
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
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Generated Schedule - List View</title>
<style>
table{border-collapse:collapse;width:100%}th,td{padding:6px 8px;border:1px solid #ccc;text-align:left}th{background:#f2f2f2}
</style>
</head>
<body>
<a href="index.html">← Back to Index</a>
<h1>Generated Schedule (List View)</h1>

<!-- Display all assignments in a simple table format -->
<table>
<tr><th>Term</th><th>Day</th><th>Course</th><th>Section</th><th>Time</th><th>Room</th><th>Part</th></tr>
<?php foreach($assignments as $a): ?>
<tr>
<td><?=htmlspecialchars($a['term'])?></td>
<td><?=htmlspecialchars($a['day'])?></td>
<td><?=htmlspecialchars($a['course_code'].' – '.$a['course_title'])?></td>
<td><?=htmlspecialchars($a['section'])?></td>
<td><?=htmlspecialchars($a['time'])?></td>
<td><?=htmlspecialchars($a['room'])?></td>
<td><?=htmlspecialchars($a['part'])?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>