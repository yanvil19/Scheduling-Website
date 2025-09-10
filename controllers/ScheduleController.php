<?php
// controllers/ScheduleController.php
require_once __DIR__ . '/../core/algorithm.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $assignments = generateSchedule($_POST);
    require_once __DIR__ . '/../views/schedule.php';
} else {
    header("Location: ../index.html");
    exit();
}
?>
