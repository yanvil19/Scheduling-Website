<?php
// core/algorithm.php
require_once __DIR__ . "/../models/courses.php";

/**
 * Grid & Schedule Helper Functions
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

/**
 * Main Scheduling Function
 */
function generateSchedule($postData) {
    // Resources
    $lecture_rooms = ["Room 203", "Room 204"];
    $lab_rooms = ["ComLab 1", "ComLab 5", "ComLab 6"];
    $days = ["Mon","Tue","Wed","Thu","Fri","Sat"];
    $time_blocks = [
        "07:00-09:00","09:00-11:00","11:00-13:00",
        "13:00-15:00","15:00-17:00","17:00-19:00","19:00-21:00"
    ];

    // Grids
    $lectureGrid = initGrid($lecture_rooms, $days, $time_blocks);
    $labGrid = initGrid($lab_rooms, $days, $time_blocks);
    $sectionSchedule = [];
    $assignments = [];

    // Programs & Years
    $courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
    $years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

    foreach ($courses as $progKey => $progLabel) {
        foreach ($years as $yearKey => $yearNumber) {
            $fieldName = "{$yearKey}_{$progKey}_enrollees";
            $enrollees = isset($postData[$fieldName]) ? (int) $postData[$fieldName] : 0;
            $sections = ceil($enrollees / 40);

            for ($s = 1; $s <= $sections; $s++) {
                $sectionName = "{$progLabel}-Y{$yearNumber}-{$s}";
                $courseList = Course::getByYearAndProgram($yearNumber, $progLabel);

                foreach ([1,2,3] as $term) {
                    $termCourses = array_filter($courseList, fn($c) => (int)$c['Term'] === $term);

                    foreach ($termCourses as $c) {
                        $lecHrs = (int)$c['Lec_Hrs'];
                        $labHrs = (int)$c['Lab_Hrs'];
                        $code = $c['Code'];
                        $title = $c['Course_Title'];

                        // Lecture Scheduling
                        if ($lecHrs > 0) {
                            $slots = ceil($lecHrs / 2);
                            $assigned = 0;
                            foreach ($days as $d) {
                                foreach($time_blocks as $t) {
                                    foreach($lecture_rooms as $r) {
                                        if ($assigned >= $slots) break 3;
                                        if(!isRoomFree($lectureGrid, $r, $d, $t)) continue;
                                        if(!isSectionFree($sectionSchedule, $sectionName, $d, $t)) continue;

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
                                        reserveSlot($lectureGrid, $r, $d, $t, $a);
                                        reserveSectionSlot($sectionSchedule, $sectionName, $d, $t, $a);
                                        $assignments[] = $a;
                                        $assigned++;
                                    }
                                }
                            }
                        }

                        // Lab Scheduling
                        if ($labHrs > 0) {
                            $slots = ceil($labHrs / 2);
                            $assigned = 0;
                            foreach ($days as $d) {
                                foreach($time_blocks as $t) {
                                    foreach($lab_rooms as $r) {
                                        if ($assigned >= $slots) break 3;
                                        if(!isRoomFree($labGrid, $r, $d, $t)) continue;
                                        if(!isSectionFree($sectionSchedule, $sectionName, $d, $t)) continue;

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
                                        reserveSlot($labGrid, $r, $d, $t, $a);
                                        reserveSectionSlot($sectionSchedule, $sectionName, $d, $t, $a);
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
    return $assignments;
}
?>
