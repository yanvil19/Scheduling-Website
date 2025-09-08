<?php
// db.php
try {
    $db = new SQLite3(__DIR__ . '/Database/courses.db');
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

function greedyAlgo() {

}

// Generate Schedule
function getSchedule($db, $year, $program) {
    
}
?>