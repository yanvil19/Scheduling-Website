<?php
include 'db.php';

$selectedProgram = $_POST['program'] ?? "";
$selectedYear    = $_POST['year'] ?? "";
$selectedSection = $_POST['section'] ?? "";

// Query parts
$whereClauses = [];
$params = [];
$types = "";

if ($selectedProgram) {
    $whereClauses[] = "p.ProgramID = ?";
    $params[] = $selectedProgram;
    $types .= "i";
}
if ($selectedYear) {
    $whereClauses[] = "y.YearLevelID = ?";
    $params[] = $selectedYear;
    $types .= "i";
}
if ($selectedSection) {
    $whereClauses[] = "s.SectionID = ?";
    $params[] = $selectedSection;
    $types .= "i";
}

$whereSQL = "";
if (!empty($whereClauses)) {
    $whereSQL = "WHERE " . implode(" AND ", $whereClauses);
}

$query = "
    SELECT 
        p.Code AS ProgramCode,
        y.Level AS YearLevel,
        s.SectionName,
        c.SubjectCode,
        c.SubjectName,
        CASE WHEN c.IsLab = 1 THEN 'LAB' ELSE 'LEC' END AS Type,
        r.RoomName,
        CONCAT(t.StartTime, ' - ', t.EndTime) AS TimeSlot,
        d.day,
        dur.duration
    FROM schedule sch
    JOIN sections s ON sch.SectionID = s.SectionID
    JOIN programs p ON s.ProgramID = p.ProgramID
    JOIN yearlevels y ON s.YearLevelID = y.YearLevelID
    JOIN term2courses c ON sch.SubjectID = c.SubjectID
    JOIN rooms r ON sch.RoomID = r.RoomID
    JOIN timeslots t ON sch.TimeSlotID = t.TimeSlotID
    JOIN days_table d ON sch.day_id = d.day_id
    JOIN duration_table dur ON sch.duration_id = dur.duration_id
    $whereSQL
    ORDER BY p.Code, y.Level, s.SectionName, d.day_id, t.StartTime
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$schedule = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($schedule);
