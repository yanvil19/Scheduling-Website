<?php
// db.php
try {
    $db = new SQLite3(__DIR__ . '/Database/courses.db');
} catch (Exception $e) {
    die("❌ Database connection failed: " . $e->getMessage());
}

// Get courses by year and program (excluding GE = 'Y')
function getCoursesByYearAndProgram($db, $year, $program) {
    $stmt = $db->prepare("SELECT Code, Course_Title, Term 
                          FROM coursesTable 
                          WHERE Enrollee_Year = :year 
                            AND Program = :program
                            AND (GE != 'Y' OR GE IS NULL)
                          ORDER BY Term");
    $stmt->bindValue(':year', $year, SQLITE3_INTEGER);
    $stmt->bindValue(':program', $program, SQLITE3_TEXT);

    $result = $stmt->execute();
    $courses = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $courses[] = $row;
    }
    return $courses;
}

function getRooms() {
    

}

?>
