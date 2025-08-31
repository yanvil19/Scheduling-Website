<?php
// SQLite connection
try {
    $db = new SQLite3(__DIR__ . '/Database/courses.db');
} catch (Exception $e) {
    die("❌ SQLite Connection failed: " . $e->getMessage());
}

?>
