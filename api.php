<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
include "config.php";

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'get_schedule':
        $program = $_POST['program'] ?? '';
        $year    = $_POST['year'] ?? '';
        $section = $_POST['section'] ?? '';

        $sql = "
            SELECT 
                p.Name AS ProgramName,
                sec.SectionName,
                c.SubjectCode,
                GROUP_CONCAT(DISTINCT c.SubjectName SEPARATOR ' / ') AS SubjectName,
                GROUP_CONCAT(DISTINCT IF(c.IsLab = 1, 'LAB', 'LEC') SEPARATOR ' / ') AS Type,
                r.RoomName,
                t.StartTime,
                t.EndTime,
                d.day AS DayOfWeek,
                dur.duration AS Duration,
                at.ActualStartTime,
                at.ActualEndTime
            FROM schedule s
            JOIN sections sec ON s.SectionID = sec.SectionID
            JOIN programs p ON sec.ProgramID = p.ProgramID
            JOIN term2courses c ON s.SubjectID = c.SubjectID
            JOIN rooms r ON s.RoomID = r.RoomID
            JOIN timeslots t ON s.TimeSlotID = t.TimeSlotID
            JOIN days_table d ON s.day_id = d.day_id
            JOIN duration_table dur ON s.duration_id = dur.duration_id
            LEFT JOIN actual_time at ON s.ActualTimeID = at.ActualTimeID
            WHERE (? = '' OR p.Code = ?)
            AND (? = '' OR sec.YearLevelID = ?)
            AND (? = '' OR sec.SectionName = ?)
            GROUP BY 
                p.Name, sec.SectionName, c.SubjectCode, 
                r.RoomName, t.StartTime, t.EndTime, d.day, dur.duration,
                at.ActualStartTime, at.ActualEndTime
            ORDER BY sec.SectionName, d.day_id, t.StartTime
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["error" => $conn->error]);
            exit;
        }

        $stmt->bind_param(
            "ssssss",
            $program, $program,
            $year, $year,
            $section, $section
        );
        $stmt->execute();
        $result = $stmt->get_result();

        $schedules = [];
        while ($row = $result->fetch_assoc()) {
            $row['StartTime'] = date("g:i A", strtotime($row['StartTime']));
            $row['EndTime']   = date("g:i A", strtotime($row['EndTime']));

            if ($row['ActualStartTime']) {
                $row['ActualStartTime'] = date("g:i A", strtotime($row['ActualStartTime']));
            }
            if ($row['ActualEndTime']) {
                $row['ActualEndTime'] = date("g:i A", strtotime($row['ActualEndTime']));
            }

            $schedules[] = $row;
        }

        echo json_encode([
            "success" => true,
            "data" => $schedules
        ]);
        break;


    // Get sections by program + year
    case 'get_sections':
        $program = $_POST['program'] ?? '';
        $year    = $_POST['year'] ?? '';

        $sql = "
            SELECT sec.SectionID, sec.SectionName
            FROM sections sec
            JOIN programs p ON sec.ProgramID = p.ProgramID
            WHERE (? = '' OR p.Code = ?)
              AND (? = '' OR sec.YearLevelID = ?)
            ORDER BY sec.SectionName
        ";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(["error" => $conn->error]);
            exit;
        }

        $stmt->bind_param("ssss", $program, $program, $year, $year);
        $stmt->execute();
        $result = $stmt->get_result();

        $sections = [];
        while ($row = $result->fetch_assoc()) {
            $sections[] = [
                "id" => $row['SectionID'],
                "name" => $row['SectionName']
            ];
        }

        echo json_encode($sections);
        break;

    default:
        echo json_encode(["error" => "Invalid action"]);


    case 'get_rooms':
    // Get all rooms
    $sql = "SELECT DISTINCT RoomName FROM rooms ORDER BY RoomName";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(["error" => $conn->error]);
        exit;
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row['RoomName'];
    }
    
    echo json_encode(["success" => true, "data" => $rooms]);
    break;

case 'get_room_usage':
    $roomName = $_POST['room'] ?? '';
    
    if (empty($roomName)) {
        echo json_encode(["error" => "Room name is required"]);
        exit;
    }
    
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
            dur.duration AS Duration,
            at.ActualStartTime,
            at.ActualEndTime
        FROM schedule s
        JOIN sections sec ON s.SectionID = sec.SectionID
        JOIN programs p ON sec.ProgramID = p.ProgramID
        JOIN term2courses c ON s.SubjectID = c.SubjectID
        JOIN rooms r ON s.RoomID = r.RoomID
        JOIN timeslots t ON s.TimeSlotID = t.TimeSlotID
        JOIN days_table d ON s.day_id = d.day_id
        JOIN duration_table dur ON s.duration_id = dur.duration_id
        LEFT JOIN actual_time at ON s.ActualTimeID = at.ActualTimeID
        WHERE r.RoomName = ?
        ORDER BY d.day_id, t.StartTime, sec.SectionName
    ";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(["error" => $conn->error]);
        exit;
    }
    
    $stmt->bind_param("s", $roomName);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $roomUsage = [];
    while ($row = $result->fetch_assoc()) {
        $row['StartTime'] = date("g:i A", strtotime($row['StartTime']));
        $row['EndTime'] = date("g:i A", strtotime($row['EndTime']));
        
        // Format actual times if they exist
        if ($row['ActualStartTime']) {
            $row['ActualStartTime'] = date("g:i A", strtotime($row['ActualStartTime']));
        }
        if ($row['ActualEndTime']) {
            $row['ActualEndTime'] = date("g:i A", strtotime($row['ActualEndTime']));
        }
        
        $roomUsage[] = $row;
    }
    
    echo json_encode(["success" => true, "data" => $roomUsage, "room" => $roomName]);
    break;

}

$conn->close();
?>
