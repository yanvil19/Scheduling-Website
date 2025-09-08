<?php
try {
    $db = new SQLite3(__DIR__ . '/Database/courses.db');

    $results = $db->query("SELECT * FROM coursesTable");

    echo "<h1>Available Courses</h1>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Code</th><th>Course Name</th></tr>";

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Code']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Course_Title']) . "</td>";
        echo "</tr>";
    }


    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
