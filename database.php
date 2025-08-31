<?php
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

    // Fetch the first row to get column names
    $firstRow = $result->fetchArray(SQLITE3_ASSOC);
    if ($firstRow) {
        echo "<tr>";
        foreach (array_keys($firstRow) as $column) {
            echo "<th>{$column}</th>";
        }
        echo "</tr>";

        // Print the first row
        echo "<tr>";
        foreach ($firstRow as $value) {
            echo "<td>{$value}</td>";
        }
        echo "</tr>";

        // Print the rest of the rows
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "<tr>";
            foreach ($row as $value) {
                echo "<td>{$value}</td>";
            }
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No courses found.</td></tr>";
    }

    echo "</table>";
}



?>
