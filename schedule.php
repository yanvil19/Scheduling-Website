<?php
require_once "db.php"; // ✅ Include database + queries

// Function to generate sections
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

$allData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
    $years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

    foreach ($courses as $courseKey => $courseLabel) {
        foreach ($years as $yearKey => $yearNumber) {
            $fieldName = "{$yearKey}_{$courseKey}_enrollees";
            if (isset($_POST[$fieldName])) {
                $enrollees = (int) $_POST[$fieldName];
                $sections = generateSections($courseLabel, $yearNumber, $enrollees);

                if (!isset($allData[$courseLabel][$yearNumber])) {
                    $allData[$courseLabel][$yearNumber] = [];
                }

                // ✅ Get courses for this program & year from db.php
                $coursesList = getCoursesByYearAndProgram($db, $yearNumber, $courseLabel);

                // Group courses by term
                $groupedByTerm = [1 => [], 2 => [], 3 => []];
                foreach ($coursesList as $course) {
                    $term = (int)$course['Term'];
                    if (isset($groupedByTerm[$term])) {
                        $groupedByTerm[$term][] = $course;
                    }
                }

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

// =============================
// Scheduling Algorithm Helpers
// =============================
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
    <a href="index.html">⬅ Back to Index</a>
    <h1>Generated Schedule</h1>

    <?php if (!empty($allData)) : ?>
        <?php
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

        foreach ($allData as $program => $years) {
            foreach ($years as $year => $sections) {
                foreach ($sections as $sec) {
                    $sectionName = $program . "-Y" . $year . "-S" . $sec['section'];
                    for ($term = 1; $term <= 3; $term++) {
                        foreach ($sec['courses'][$term] as $courseRow) {
                            $lecHrs = (int) $courseRow['Lec_Hrs'];
                            $labHrs = (int) $courseRow['Lab_Hrs'];
                            $courseCode = $courseRow['Code'];
                            $courseTitle = $courseRow['Course_Title'];

                            // Lecture scheduling
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
                                                'term'=>$term,'day'=>$day,'course_code'=>$courseCode,
                                                'course_title'=>$courseTitle,'section'=>$sectionName,
                                                'time'=>$time,'room'=>$room,'part'=>'Lec'
                                            ];
                                            reserveSlot($lectureGrid,$room,$day,$time,$a);
                                            reserveSectionSlot($sectionSchedule,$sectionName,$day,$time,$a);
                                            $assignmentsBySection[$sectionName][$term][]=$a;
                                            $slotsAssigned++;
                                        }
                                    }
                                }
                            }

                            // Lab scheduling
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
                                                'term'=>$term,'day'=>$day,'course_code'=>$courseCode,
                                                'course_title'=>$courseTitle,'section'=>$sectionName,
                                                'time'=>$time,'room'=>$room,'part'=>'Lab'
                                            ];
                                            reserveSlot($labGrid,$room,$day,$time,$a);
                                            reserveSectionSlot($sectionSchedule,$sectionName,$day,$time,$a);
                                            $assignmentsBySection[$sectionName][$term][]=$a;
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
                            <td><?php echo htmlspecialchars($a['course_code']." — ".$a['course_title']); ?></td>
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
