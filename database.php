<?php
// Test Run on terminal: & "C:\xampp\php\php.exe" "database.php"
// Database connection
try {
    $db = new SQLite3(__DIR__ . '/Database/courses.db');
    echo "✅ Connected to SQLite successfully!";

} catch (Exception $e) {
    die("❌ Connection failed: " . $e->getMessage());
}

function displayCourses($db) {
    $result = $db->query("SELECT * FROM courses");

    echo "<h2>📘 Course List</h2>";
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Code</th><th>Course Name</th><th>Section</th></tr>";

    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>{$row['field1']}</td>";
        echo "<td>{$row['field2']}</td>";
        echo "<td>{$row['field8']}</td>";
        echo "</tr>";
    }

    echo "</table>";
}


?>
