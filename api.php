<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include "config.php";

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ✅ Fetch schedules (filtered by program, year, section)
    case 'get_schedule':
        $program = $_POST['program'] ?? '';
        $year = $_POST['year'] ?? '';
        $section = $_POST['section'] ?? '';

        $sql = "
            SELECT 
                p.Name AS ProgramName,
                sec.SectionName,
                c.SubjectCode,
                c.SubjectName,
                IF(c.IsLab = 1, 'LAB', 'LEC') AS Type,
                r.RoomName,
                t.StartTime,
                t.EndTime,
                d.day AS DayOfWeek,
                dur.duration AS Duration
            FROM schedule s
            JOIN sections sec ON s.SectionID = sec.SectionID
            JOIN programs p ON sec.ProgramID = p.ProgramID
            JOIN term2courses c ON s.SubjectID = c.SubjectID
            JOIN rooms r ON s.RoomID = r.RoomID
            JOIN timeslots t ON s.TimeSlotID = t.TimeSlotID
            JOIN days_table d ON s.day_id = d.day_id
            JOIN duration_table dur ON s.duration_id = dur.duration_id
            WHERE 1=1
        ";

        if (!empty($program)) {
            $sql .= " AND p.Code = '" . $conn->real_escape_string($program) . "'";
        }
        if (!empty($year)) {
            $sql .= " AND sec.YearLevelID = '" . $conn->real_escape_string($year) . "'";
        }
        if (!empty($section)) {
            $sql .= " AND sec.SectionName = '" . $conn->real_escape_string($section) . "'";
        }

        $sql .= " ORDER BY sec.SectionName, d.day_id, t.StartTime";

        $result = $conn->query($sql);

        if (!$result) {
            echo json_encode(["error" => $conn->error]);
            exit;
        }

        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $row['StartTime'] = date("g:i A", strtotime($row['StartTime']));
            $row['EndTime']   = date("g:i A", strtotime($row['EndTime']));
            $schedules[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $schedules
        ]);
        break;

    // ✅ Fetch sections based on program + year
    case 'get_sections':
        $program = $_POST['program'] ?? '';
        $year = $_POST['year'] ?? '';

        if (empty($program) || empty($year)) {
            echo json_encode([]);
            exit;
        }

        $sql = "
            SELECT sec.SectionID, sec.SectionName
            FROM sections sec
            JOIN programs p ON sec.ProgramID = p.ProgramID
            WHERE p.Code = '" . $conn->real_escape_string($program) . "'
              AND sec.YearLevelID = '" . $conn->real_escape_string($year) . "'
            ORDER BY sec.SectionName
        ";

        $result = $conn->query($sql);

        $sections = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sections[] = [
                    "id" => $row['SectionID'],
                    "name" => $row['SectionName']
                ];
            }
        }

        echo json_encode($sections);
        break;

    default:
        echo json_encode(["error" => "Invalid action"]);
}
$conn->close();
?>