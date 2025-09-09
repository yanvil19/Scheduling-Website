<?php
require_once "db.php";

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
$assignments = [];

$courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
$years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

foreach ($courses as $progKey => $progLabel) {
    foreach ($years as $yearKey => $yearNumber) {
        $fieldName = "{$yearKey}_{$progKey}_enrollees";
        $enrollees = isset($_POST[$fieldName]) ? (int) $_POST[$fieldName] : 0;
        $sections = ceil($enrollees / 40);
        for ($s = 1; $s <= $sections; $s++) {
            $sectionName = "{$progLabel}-Y{$yearNumber}-{$s}";
            $courseList = getCoursesByYearAndProgram($db,$yearNumber,$progLabel);
            foreach ([1,2,3] as $term) {
                $termCourses = array_filter($courseList,fn($c)=> (int)$c['Term']===$term);
                foreach ($termCourses as $c) {
                    $lecHrs=(int)$c['Lec_Hrs']; $labHrs=(int)$c['Lab_Hrs'];
                    $code=$c['Code']; $title=$c['Course_Title'];
                    if ($lecHrs>0){
                        $slots=ceil($lecHrs/2); $assigned=0;
                        foreach ($days as $d){ foreach($time_blocks as $t){ foreach($lecture_rooms as $r){
                            if ($assigned>=$slots) break 3;
                            if(!isRoomFree($lectureGrid,$r,$d,$t))continue;
                            if(!isSectionFree($sectionSchedule,$sectionName,$d,$t))continue;
                            $a=['term'=>$term,'day'=>$d,'course_code'=>$code,'course_title'=>$title,
                                'section'=>$sectionName,'time'=>$t,'room'=>$r,'part'=>'Lec'];
                            reserveSlot($lectureGrid,$r,$d,$t,$a);
                            reserveSectionSlot($sectionSchedule,$sectionName,$d,$t,$a);
                            $assignments[]=$a; $assigned++;
                        }}}
                    }
                    if ($labHrs>0){
                        $slots=ceil($labHrs/2); $assigned=0;
                        foreach ($days as $d){ foreach($time_blocks as $t){ foreach($lab_rooms as $r){
                            if ($assigned>=$slots) break 3;
                            if(!isRoomFree($labGrid,$r,$d,$t))continue;
                            if(!isSectionFree($sectionSchedule,$sectionName,$d,$t))continue;
                            $a=['term'=>$term,'day'=>$d,'course_code'=>$code,'course_title'=>$title,
                                'section'=>$sectionName,'time'=>$t,'room'=>$r,'part'=>'Lab'];
                            reserveSlot($labGrid,$r,$d,$t,$a);
                            reserveSectionSlot($sectionSchedule,$sectionName,$d,$t,$a);
                            $assignments[]=$a; $assigned++;
                        }}}
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
<a href="index.html">⬅ Back to Index</a>
<h1>Generated Schedule (List View)</h1>
<table>
<tr><th>Term</th><th>Day</th><th>Course</th><th>Section</th><th>Time</th><th>Room</th><th>Part</th></tr>
<?php foreach($assignments as $a): ?>
<tr>
<td><?=htmlspecialchars($a['term'])?></td>
<td><?=htmlspecialchars($a['day'])?></td>
<td><?=htmlspecialchars($a['course_code'].' — '.$a['course_title'])?></td>
<td><?=htmlspecialchars($a['section'])?></td>
<td><?=htmlspecialchars($a['time'])?></td>
<td><?=htmlspecialchars($a['room'])?></td>
<td><?=htmlspecialchars($a['part'])?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
