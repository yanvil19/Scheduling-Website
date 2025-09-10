<?php
// models/Course.php
require_once "db.php";

class Course {
    public static function getByYearAndProgram($year, $program) {
        global $db;
        $stmt = $db->prepare("SELECT Code, Course_Title, Term, Lec_Hrs, Lab_Hrs
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
}
?>
