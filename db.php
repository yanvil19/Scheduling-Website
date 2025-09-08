<?php
try {
    $db = new SQLite3(__DIR__ . '/Database/courses.db');

   $results = $db->query("SELECT * FROM coursesTable WHERE GE IS NULL");

    echo "<h1>Available Courses</h1>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Code</th><th>Course Name</th><th>GE</th><th>Term</th><th>Enrollee Year</th><th>Program</th><th>Lec Hrs</th><th>Lab Hrs</th></tr>";

    while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Code']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Course_Title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['GE']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Term']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Enrollee_Year']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Program']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Lec_Hrs']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Lab_Hrs']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
