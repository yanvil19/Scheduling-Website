<?php
include 'db.php';

// Fetch Programs
$programs = $conn->query("SELECT ProgramID, Code, Name FROM programs ORDER BY Priority");

// Fetch Years
$years = $conn->query("SELECT YearLevelID, Level FROM yearlevels ORDER BY Level");

// Fetch Sections
$sections = $conn->query("SELECT * FROM sections ORDER BY SectionName");
$sectionsData = [];
while ($row = $sections->fetch_assoc()) {
    $sectionsData[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Class Schedule Viewer</title>
    <script src="script.js"></script>
</head>
<body>
    <h2>View Schedule</h2>
    <form method="POST" id="scheduleForm">
        <!-- Program -->
        <label>Program:</label>
        <select name="program" id="program" onchange="autoSubmit()">
            <option value="">-- All Programs --</option>
            <?php while($row = $programs->fetch_assoc()): ?>
                <option value="<?= $row['ProgramID'] ?>"><?= $row['Code'] ?> - <?= $row['Name'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Year -->
        <label>Year:</label>
        <select name="year" id="year" onchange="autoSubmit()">
            <option value="">-- All Years --</option>
            <?php while($row = $years->fetch_assoc()): ?>
                <option value="<?= $row['YearLevelID'] ?>">Year <?= $row['Level'] ?></option>
            <?php endwhile; ?>
        </select>

        <!-- Section -->
        <label>Section:</label>
        <select name="section" id="section" onchange="autoSubmit()">
            <option value="">-- All Sections --</option>
        </select>
    </form>

    <h3>Schedule</h3>
    <div id="scheduleTable">
        <!-- JS will load schedule here -->
    </div>

    <script>
        let sectionsData = <?php echo json_encode($sectionsData); ?>;
    </script>
</body>
</html>
