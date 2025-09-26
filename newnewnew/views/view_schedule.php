<?php
// --- Database Connection ---
$conn = new mysqli("localhost", "root", "", "schedulingdb", 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// --- Fetch Schedule Data ---
$schedule_entries = [];
$sql = "
    SELECT 
        sec.SectionName,
        sub.SubjectCode,
        sub.SubjectName,
        IF(sub.IsLab = 1, 'LAB', 'LEC') AS Type,
        r.RoomName,
        t.StartTime,
        t.EndTime,
        sub.TotalTime AS Duration
    FROM schedule sch
    JOIN sections sec ON sch.SectionID = sec.SectionID
    JOIN term2courses sub ON sch.SubjectID = sub.SubjectID
    JOIN rooms r ON sch.RoomID = r.RoomID
    JOIN timeslots t ON sch.TimeSlotID = t.TimeSlotID
    ORDER BY t.StartTime, sec.SectionName;
";

$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $schedule_entries[] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Schedule</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Audiowide&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; padding: 0; }
        .container { max-width: 95%; margin: 20px auto; padding: 20px; }
        .schedule-table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-size: 0.9em; }
        .schedule-table th, .schedule-table td { padding: 10px 12px; border: 1px solid #ddd; text-align: left; white-space: nowrap; }
        .schedule-table th { background-color: #041133; color: white; font-weight: 600; }
        .schedule-table td:nth-child(3) { white-space: normal; min-width: 250px; } /* Subject Name */
        .no-classes { padding: 20px; text-align: center; color: #777; }
        .upper-upper-home { background-color: #041133; padding: 10px 20px; }
        .upper-home { display: flex; align-items: center; max-width: 1200px; margin: 0 auto; }
        .logo { height: 50px; }
        .name { color: white; font-family: 'Audiowide', cursive; font-size: 2rem; margin-left: 15px; }
        h1 { text-align: center; color: #333; }
    </style>
</head>
<body>

    <div class="upper-upper-home">
        <div class="upper-home">
            <img src="../assets/logo-1-10.png" alt="Logo" class="logo">
            <span class="name">SigmaScheds</span>
        </div>
    </div>

    <div class="container">
        <h1>Generated Class Schedule</h1>

        <?php if (!empty($schedule_entries)): ?>
            <table class="schedule-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>Code</th>
                        <th>Subject Name</th>
                        <th>Type</th>
                        <th>Room</th>
                        <th>Time Slot</th>
                        <th>Duration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedule_entries as $entry): ?>
                        <tr>
                            <td><?= htmlspecialchars($entry['SectionName']) ?></td>
                            <td><?= htmlspecialchars($entry['SubjectCode']) ?></td>
                            <td><?= htmlspecialchars($entry['SubjectName']) ?></td>
                            <td><?= htmlspecialchars($entry['Type']) ?></td>
                            <td><?= htmlspecialchars($entry['RoomName']) ?></td>
                            <td><?= htmlspecialchars(date('H:i:s', strtotime($entry['StartTime']))) . '-' . htmlspecialchars(date('H:i:s', strtotime($entry['EndTime']))) ?></td>
                            <td><?= htmlspecialchars(number_format($entry['Duration'], 3)) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-classes">No schedule has been generated yet.</p>
        <?php endif; ?>
    </div>

</body>
</html>
