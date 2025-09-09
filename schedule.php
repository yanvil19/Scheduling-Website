<?php
require_once "db.php"; // Include database connection and query functions

/**
 * SECTION GENERATION FUNCTION
 * Calculates how many sections are needed based on enrollees
 */
function generateSections($course, $year, $enrollees, $maxClassSize = 40) {
    $sections = [];
    $sectionCount = ceil($enrollees / $maxClassSize);

    for ($i = 1; $i <= $sectionCount; $i++) {
        $sections[] = [
            'year' => $year,
            'section' => $i
        ];
    }

    return $sections;
}

// SCHEDULING ALGORITHM HELPER FUNCTIONS
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

function isRoomFree($grid, $room, $day, $time) {
    return !isset($grid[$room][$day][$time]) || $grid[$room][$day][$time] === null;
}

function isSectionFree($sectionSchedule, $section, $day, $time) {
    return !isset($sectionSchedule[$section][$day][$time]);
}

function reserveSlot(&$grid, $room, $day, $time, $assignment) {
    $grid[$room][$day][$time] = $assignment;
}

function reserveSectionSlot(&$sectionSchedule, $section, $day, $time, $assignment) {
    if (!isset($sectionSchedule[$section])) $sectionSchedule[$section] = [];
    if (!isset($sectionSchedule[$section][$day])) $sectionSchedule[$section][$day] = [];
    $sectionSchedule[$section][$day][$time] = $assignment;
}

// Initialize data storage array
$allData = [];
$debugInfo = []; // ADD: Debug information array

// FORM DATA PROCESSING
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
    $years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

    // ADD: Debug POST data
    $debugInfo['post_data'] = $_POST;
    
    foreach ($courses as $courseKey => $courseLabel) {
        foreach ($years as $yearKey => $yearNumber) {
            $fieldName = "{$yearKey}_{$courseKey}_enrollees";
            
            // ADD: Debug field processing
            $debugInfo['processed_fields'][] = [
                'field_name' => $fieldName,
                'exists' => isset($_POST[$fieldName]),
                'value' => isset($_POST[$fieldName]) ? $_POST[$fieldName] : 'NOT SET'
            ];
            
            if (isset($_POST[$fieldName]) && $_POST[$fieldName] > 0) { // ADD: Check for > 0
                $enrollees = (int) $_POST[$fieldName];
                $sections = generateSections($courseLabel, $yearNumber, $enrollees);

                if (!isset($allData[$courseLabel][$yearNumber])) {
                    $allData[$courseLabel][$yearNumber] = [];
                }

                // Get courses from database
                $coursesList = getCoursesByYearAndProgram($db, $yearNumber, $courseLabel);
                
                // ADD: Debug database results
                $debugInfo['database_queries'][] = [
                    'program' => $courseLabel,
                    'year' => $yearNumber,
                    'courses_found' => count($coursesList),
                    'courses' => $coursesList
                ];

                // Group courses by term - FIX: Ensure all terms are initialized
                $groupedByTerm = [1 => [], 2 => [], 3 => []];
                foreach ($coursesList as $course) {
                    $term = (int)$course['Term'];
                    if ($term >= 1 && $term <= 3) { // ADD: Validate term range
                        $groupedByTerm[$term][] = $course;
                    }
                }
                
                // ADD: Debug term grouping
                $debugInfo['term_grouping'][] = [
                    'program' => $courseLabel,
                    'year' => $yearNumber,
                    'term_1_courses' => count($groupedByTerm[1]),
                    'term_2_courses' => count($groupedByTerm[2]),
                    'term_3_courses' => count($groupedByTerm[3])
                ];

                foreach ($sections as &$sec) {
                    $sec['courses'] = $groupedByTerm;
                }

                $allData[$courseLabel][$yearNumber] = array_merge(
                    $allData[$courseLabel][$yearNumber],
                    $sections
                );
            }
        }
    }
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
        .debug { background: #fff3cd; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .error { background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px; }
    </style>
</head>
<body>
    <a href="index.html">← Back to Index</a>
    <h1>Generated Schedule</h1>

    <!-- ADD: Debug Information Section -->
    <?php if (!empty($debugInfo)): ?>
    <div class="debug">
        <h3>🔍 Debug Information</h3>
        
        <h4>Form Fields Processed:</h4>
        <table style="font-size: 12px;">
            <tr><th>Field Name</th><th>Exists</th><th>Value</th></tr>
            <?php foreach ($debugInfo['processed_fields'] as $field): ?>
            <tr>
                <td><?= $field['field_name'] ?></td>
                <td><?= $field['exists'] ? '✅' : '❌' ?></td>
                <td><?= $field['value'] ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <?php if (isset($debugInfo['database_queries'])): ?>
        <h4>Database Queries:</h4>
        <?php foreach ($debugInfo['database_queries'] as $query): ?>
            <p><strong><?= $query['program'] ?> Year <?= $query['year'] ?>:</strong> 
               Found <?= $query['courses_found'] ?> courses</p>
        <?php endforeach; ?>
        <?php endif; ?>

        <?php if (isset($debugInfo['term_grouping'])): ?>
        <h4>Courses by Term:</h4>
        <?php foreach ($debugInfo['term_grouping'] as $group): ?>
            <p><strong><?= $group['program'] ?> Year <?= $group['year'] ?>:</strong> 
               Term 1: <?= $group['term_1_courses'] ?>, 
               Term 2: <?= $group['term_2_courses'] ?>, 
               Term 3: <?= $group['term_3_courses'] ?></p>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($allData)) : ?>
        <?php
        // SCHEDULING CONFIGURATION
        $lecture_rooms = ["Room 203", "Room 204"];
        $lab_rooms = ["ComLab 1", "ComLab 5", "ComLab 6"];
        $days = ["Mon","Tue","Wed","Thu","Fri","Sat"];
        $time_blocks = [
            "07:00-09:00","09:00-11:00","11:00-13:00",
            "13:00-15:00","15:00-17:00","17:00-19:00","19:00-21:00"
        ];
        
        $lectureGrid = initGrid($lecture_rooms, $days, $time_blocks);
        $labGrid = initGrid($lab_rooms, $days, $time_blocks);
        $sectionSchedule = [];
        $assignmentsBySection = [];
        $schedulingDebug = []; // ADD: Scheduling debug info

        // MAIN SCHEDULING LOOP
        foreach ($allData as $program => $years) {
            $schedulingDebug[] = "Processing program: $program";
            
            foreach ($years as $year => $sections) {
                $schedulingDebug[] = "  Processing year: $year (" . count($sections) . " sections)";
                
                foreach ($sections as $sec) {
                    $sectionName = $program . "-Y" . $year . "-S" . $sec['section'];
                    $schedulingDebug[] = "    Processing section: $sectionName";
                    
                    // Process each term
                    for ($term = 1; $term <= 3; $term++) {
                        $schedulingDebug[] = "      Processing term: $term";
                        
                        // FIX: Check if courses exist for this term
                        if (!isset($sec['courses'][$term]) || empty($sec['courses'][$term])) {
                            $schedulingDebug[] = "        ⚠️ No courses found for term $term";
                            continue;
                        }
                        
                        $coursesInTerm = count($sec['courses'][$term]);
                        $schedulingDebug[] = "        Found $coursesInTerm courses in term $term";
                        
                        foreach ($sec['courses'][$term] as $courseRow) {
                            $lecHrs = (int) $courseRow['Lec_Hrs'];
                            $labHrs = (int) $courseRow['Lab_Hrs'];
                            $courseCode = $courseRow['Code'];
                            $courseTitle = $courseRow['Course_Title'];
                            
                            $schedulingDebug[] = "          Processing course: $courseCode ($lecHrs lec, $labHrs lab hours)";

                            // LECTURE SCHEDULING
                            if ($lecHrs > 0) {
                                $slotsNeeded = (int) ceil($lecHrs / 2);
                                $slotsAssigned = 0;
                                
                                foreach ($days as $day) {
                                    foreach ($time_blocks as $time) {
                                        foreach ($lecture_rooms as $room) {
                                            if ($slotsAssigned >= $slotsNeeded) break 3;
                                            
                                            if (!isRoomFree($lectureGrid,$room,$day,$time)) continue;
                                            if (!isSectionFree($sectionSchedule,$sectionName,$day,$time)) continue;
                                            
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
                                            
                                            reserveSlot($lectureGrid,$room,$day,$time,$a);
                                            reserveSectionSlot($sectionSchedule,$sectionName,$day,$time,$a);
                                            
                                            // FIX: Initialize arrays properly
                                            if (!isset($assignmentsBySection[$sectionName])) {
                                                $assignmentsBySection[$sectionName] = [];
                                            }
                                            if (!isset($assignmentsBySection[$sectionName][$term])) {
                                                $assignmentsBySection[$sectionName][$term] = [];
                                            }
                                            
                                            $assignmentsBySection[$sectionName][$term][] = $a;
                                            $slotsAssigned++;
                                        }
                                    }
                                }
                                $schedulingDebug[] = "            Assigned $slotsAssigned/$slotsNeeded lecture slots";
                            }

                            // LAB SCHEDULING
                            if ($labHrs > 0) {
                                $slotsNeeded = (int) ceil($labHrs / 2);
                                $slotsAssigned = 0;
                                
                                foreach ($days as $day) {
                                    foreach ($time_blocks as $time) {
                                        foreach ($lab_rooms as $room) {
                                            if ($slotsAssigned >= $slotsNeeded) break 3;
                                            
                                            if (!isRoomFree($labGrid,$room,$day,$time)) continue;
                                            if (!isSectionFree($sectionSchedule,$sectionName,$day,$time)) continue;
                                            
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
                                            
                                            reserveSlot($labGrid,$room,$day,$time,$a);
                                            reserveSectionSlot($sectionSchedule,$sectionName,$day,$time,$a);
                                            
                                            // FIX: Initialize arrays properly
                                            if (!isset($assignmentsBySection[$sectionName])) {
                                                $assignmentsBySection[$sectionName] = [];
                                            }
                                            if (!isset($assignmentsBySection[$sectionName][$term])) {
                                                $assignmentsBySection[$sectionName][$term] = [];
                                            }
                                            
                                            $assignmentsBySection[$sectionName][$term] = $assignmentsBySection[$sectionName][$term] ?? [];
                                            $assignmentsBySection[$sectionName][$term][] = $a;
                                            $slotsAssigned++;
                                        }
                                    }
                                }
                                $schedulingDebug[] = "            Assigned $slotsAssigned/$slotsNeeded lab slots";
                            }
                        }
                    }
                }
            }
        }
        ?>

        <!-- ADD: Scheduling Debug Info -->
        <div class="debug">
            <h3>📅 Scheduling Debug</h3>
            <textarea style="width: 100%; height: 200px; font-family: monospace; font-size: 11px;"><?php 
                foreach ($schedulingDebug as $log) {
                    echo $log . "\n";
                }
            ?></textarea>
        </div>

        <!-- DISPLAY RESULTS -->
        <?php if (empty($assignmentsBySection)): ?>
            <div class="error">
                <h3>❌ No Schedules Generated</h3>
                <p>Possible issues:</p>
                <ul>
                    <li>No enrollees entered (must be > 0)</li>
                    <li>No courses found in database for selected programs/years</li>
                    <li>Database connection issues</li>
                    <li>Course data missing Term information</li>
                </ul>
            </div>
        <?php else: ?>
            <p><strong>✅ Generated schedules for <?= count($assignmentsBySection) ?> sections</strong></p>
        <?php endif; ?>
        
        <?php foreach ($assignmentsBySection as $section => $terms): ?>
            <h2>Schedule for <?php echo htmlspecialchars($section); ?></h2>
            <?php 
            // Sort terms to display in order
            ksort($terms);
            foreach ($terms as $term => $assignments): 
            ?>
                <h3>Term <?php echo $term; ?> (<?= count($assignments) ?> classes)</h3>
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
        <div class="error">
            <h3>❌ No Data Submitted</h3>
            <p>Please go back and enter the number of enrollees for each program/year.</p>
            <p><strong>Make sure to enter values greater than 0.</strong></p>
        </div>
    <?php endif; ?>
</body>
</html>