<?php
// Database configuration
$host = '127.0.0.1:3307';
$dbname = 'schedulingdb';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get filter parameters
$programFilter = isset($_GET['program']) ? $_GET['program'] : 'all';
$yearFilter = isset($_GET['year']) ? $_GET['year'] : 'all';

// Course code mappings
$courseCodeMap = [
    // IT Program
    'INPROLA' => 'IT-1',
    'MANPRIN' => 'IT-2',
    'OPESYST' => 'IT-3',
    'WEBPROG' => 'IT-4',
    'MOBPROG' => 'IT-5',
    'DATAMA2' => 'IT-6',
    'PEMBEDS' => 'IT-7',
    'ELECTV1' => 'IT-8',
    'CLDCOMP' => 'IT-9',
    'MCSPROJ' => 'IT-10',
    'TENTREP' => 'IT-11',
    'ELECTV4' => 'IT-12',
    'xINTERN1' => 'IT-13',
    
    // IS Program
    'CCFUINSY' => 'IS-1',
    'CIENTARL' => 'IS-4',
    'CCQUAMET' => 'IS-6',
    'BUPRDEMA' => 'IS-7',
    'CISECDEL' => 'IS-8',
    'CTSYSADL' => 'IS-9',
    'CISPRMAL' => 'IS-10',
    'CISELE1L' => 'IS-11',
    'CCISINTX' => 'IS-12',
    
    // CS Program
    'CCDISTR1' => 'CS-1',
    'CCOMPORL' => 'CS-4',
    'CCDATSYL' => 'CS-5',
    'CCSFEN1L' => 'CS-7',
    'CTINASSL' => 'CS-8',
    'CCNETCOL' => 'CS-9',
    'CCCSINTX' => 'CS-10',
    'CCSELE2L' => 'CS-11',
    
    // Shared courses
    'CCPRGG2X' => ['IS-2', 'CS-2'],
    'CCPRG2BL' => ['IS-3', 'CS-3'],
    'CCSPEL2X' => ['IS-5', 'CS-6']
];

// Function to get course code based on program
function getCourseCode($subjectCode, $programId, $courseCodeMap) {
    if (!isset($courseCodeMap[$subjectCode])) {
        return $subjectCode;
    }
    
    $code = $courseCodeMap[$subjectCode];
    
    // Handle shared courses
    if (is_array($code)) {
        if ($programId == 2) return $code[0]; // IS
        if ($programId == 3) return $code[1]; // CS
        return $code[0];
    }
    
    return $code;
}

// Fetch schedule data
$query = "
    SELECT 
        sec.SectionID,
        sec.SectionName,
        sec.ProgramID,
        sec.YearLevelID,
        d.day,
        d.day_id,
        ts.StartTime,
        ts.EndTime,
        at.ActualStartTime,
        at.ActualEndTime,
        sub.SubjectCode,
        sub.SubjectName,
        sub.IsLab,
        r.RoomName,
        r.RoomType
    FROM schedule s
    JOIN sections sec ON s.SectionID = sec.SectionID
    JOIN days_table d ON s.day_id = d.day_id
    JOIN timeslots ts ON s.TimeSlotID = ts.TimeSlotID
    JOIN actual_time at ON s.ActualTimeID = at.ActualTimeID
    JOIN term2courses sub ON s.SubjectID = sub.SubjectID
    JOIN rooms r ON s.RoomID = r.RoomID
    WHERE 1=1
";

$params = [];

if ($programFilter !== 'all') {
    $query .= " AND sec.ProgramID = :program";
    $params[':program'] = $programFilter;
}

if ($yearFilter !== 'all') {
    $query .= " AND sec.YearLevelID = :year";
    $params[':year'] = $yearFilter;
}

$query .= " ORDER BY sec.SectionName, d.day_id, at.ActualStartTime";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize schedules by section
$schedulesBySection = [];
foreach ($schedules as $schedule) {
    $sectionName = $schedule['SectionName'];
    if (!isset($schedulesBySection[$sectionName])) {
        $schedulesBySection[$sectionName] = [
            'info' => [
                'SectionID' => $schedule['SectionID'],
                'ProgramID' => $schedule['ProgramID'],
                'YearLevelID' => $schedule['YearLevelID']
            ],
            'schedule' => []
        ];
    }
    
    $schedulesBySection[$sectionName]['schedule'][] = $schedule;
}

// Function to format time slot
function getTimeSlot($startTime, $endTime) {
    $start = date('g', strtotime($startTime));
    $end = date('g', strtotime($endTime));
    
    $timeSlots = [
        '7-9' => ['07:00:00', '09:00:00'],
        '9-11' => ['09:00:00', '11:00:00'],
        '11-1' => ['11:00:00', '13:00:00'],
        '1-3' => ['13:00:00', '15:00:00'],
        '3-5' => ['15:00:00', '17:00:00'],
        '5-7' => ['17:00:00', '19:00:00'],
        '7-9pm' => ['19:00:00', '21:00:00']
    ];
    
    foreach ($timeSlots as $label => $times) {
        if ($startTime == $times[0] && $endTime == $times[1]) {
            return $label;
        }
    }
    
    return $start . '-' . $end;
}

// Function to get schedule for specific day and time
function getScheduleForSlot($scheduleData, $day, $timeSlot) {
    foreach ($scheduleData as $schedule) {
        $scheduleTimeSlot = getTimeSlot($schedule['ActualStartTime'], $schedule['ActualEndTime']);
        if ($schedule['day'] === $day && $scheduleTimeSlot === $timeSlot) {
            return $schedule;
        }
    }
    return null;
}

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
$timeSlots = ['7-9', '9-11', '11-1', '1-3', '3-5', '5-7', '7-9pm'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule Viewer</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            font-size: 2em;
        }
        
        .legend-box {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .legend-box h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 1.3em;
        }
        
        .legend-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        .legend-column h4 {
            font-size: 1.1em;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 2px solid #ddd;
        }
        
        .legend-column.it h4 { color: #2563eb; border-color: #2563eb; }
        .legend-column.is h4 { color: #059669; border-color: #059669; }
        .legend-column.cs h4 { color: #7c3aed; border-color: #7c3aed; }
        
        .legend-column div {
            padding: 4px 0;
            font-size: 0.9em;
        }
        
        .legend-column span {
            font-weight: 600;
        }
        
        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 250px;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .filter-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1em;
        }
        
        .schedule-grid {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .schedule-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .schedule-table-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        @media (max-width: 1200px) {
            .schedule-row {
                grid-template-columns: 1fr;
            }
        }
        
        .section-header {
            background: #041133;
            color: white;
            padding: 15px 20px;
            font-size: 1.2em;
            font-weight: bold;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .table-wrapper {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        
        th {
            background: #0146c7;
            color: white;
            font-weight: 600;
        }
        
        th:first-child {
            text-align: left;
        }
        
        td:first-child {
            text-align: left;
            font-weight: 600;
            color: #555;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .course-code {
            display: inline-block;
            background: #dbeafe;
            color: #1e40af;
            padding: 6px 12px;
            border-radius: 5px;
            font-weight: 600;
            font-size: 0.95em;
        }
        
        .no-schedule {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            color: #999;
        }
        
        .room-info {
            font-size: 0.75em;
            color: #666;
            display: block;
            margin-top: 4px;
        }
        
        @media print {
            body {
                background: white;
            }
            
            .filter-box {
                display: none;
            }
            
            .schedule-table-container {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Class Schedule Viewer</h1>
        
        <!-- Course Code Legend -->
        <div class="legend-box">
            <h3>Course Codes</h3>
            <div class="legend-grid">
                <div class="legend-column it">
                    <h4>IT Program</h4>
                    <div><span>IT-1</span> = INPROLA</div>
                    <div><span>IT-2</span> = MANPRIN</div>
                    <div><span>IT-3</span> = OPESYST</div>
                    <div><span>IT-4</span> = WEBPROG</div>
                    <div><span>IT-5</span> = MOBPROG</div>
                    <div><span>IT-6</span> = DATAMA2</div>
                    <div><span>IT-7</span> = PEMBEDS</div>
                    <div><span>IT-8</span> = ELECTV1</div>
                    <div><span>IT-9</span> = CLDCOMP</div>
                    <div><span>IT-10</span> = MCSPROJ</div>
                    <div><span>IT-11</span> = TENTREP</div>
                    <div><span>IT-12</span> = ELECTV4</div>
                    <div><span>IT-13</span> = xINTERN1</div>
                </div>
                
                <div class="legend-column is">
                    <h4>IS Program</h4>
                    <div><span>IS-1</span> = CCFUINSY</div>
                    <div><span>IS-2</span> = CCPRGG2X</div>
                    <div><span>IS-3</span> = CCPRG2BL</div>
                    <div><span>IS-4</span> = CIENTARL</div>
                    <div><span>IS-5</span> = CCSPEL2X</div>
                    <div><span>IS-6</span> = CCQUAMET</div>
                    <div><span>IS-7</span> = BUPRDEMA</div>
                    <div><span>IS-8</span> = CISECDEL</div>
                    <div><span>IS-9</span> = CTSYSADL</div>
                    <div><span>IS-10</span> = CISPRMAL</div>
                    <div><span>IS-11</span> = CISELE1L</div>
                    <div><span>IS-12</span> = CCISINTX</div>
                </div>
                
                <div class="legend-column cs">
                    <h4>CS Program</h4>
                    <div><span>CS-1</span> = CCDISTR1</div>
                    <div><span>CS-2</span> = CCPRGG2X</div>
                    <div><span>CS-3</span> = CCPRG2BL</div>
                    <div><span>CS-4</span> = CCOMPORL</div>
                    <div><span>CS-5</span> = CCDATSYL</div>
                    <div><span>CS-6</span> = CCSPEL2X</div>
                    <div><span>CS-7</span> = CCSFEN1L</div>
                    <div><span>CS-8</span> = CTINASSL</div>
                    <div><span>CS-9</span> = CCNETCOL</div>
                    <div><span>CS-10</span> = CCCSINTX</div>
                    <div><span>CS-11</span> = CCSELE2L</div>
                </div>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="filter-box">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label>Program</label>
                        <select name="program" onchange="this.form.submit()">
                            <option value="all" <?= $programFilter === 'all' ? 'selected' : '' ?>>All Programs</option>
                            <option value="1" <?= $programFilter === '1' ? 'selected' : '' ?>>Information Technology (IT)</option>
                            <option value="2" <?= $programFilter === '2' ? 'selected' : '' ?>>Information Systems (IS)</option>
                            <option value="3" <?= $programFilter === '3' ? 'selected' : '' ?>>Computer Science (CS)</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Year Level</label>
                        <select name="year" onchange="this.form.submit()">
                            <option value="all" <?= $yearFilter === 'all' ? 'selected' : '' ?>>All Years</option>
                            <option value="1" <?= $yearFilter === '1' ? 'selected' : '' ?>>1st Year</option>
                            <option value="2" <?= $yearFilter === '2' ? 'selected' : '' ?>>2nd Year</option>
                            <option value="3" <?= $yearFilter === '3' ? 'selected' : '' ?>>3rd Year</option>
                            <option value="4" <?= $yearFilter === '4' ? 'selected' : '' ?>>4th Year</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Schedule Tables -->
        <?php if (empty($schedulesBySection)): ?>
            <div class="no-schedule">
                No schedules found for the selected filters.
            </div>
        <?php else: ?>
            <div class="schedule-grid">
                <?php 
                $sections = array_keys($schedulesBySection);
                for ($i = 0; $i < count($sections); $i += 2): 
                ?>
                    <div class="schedule-row">
                        <!-- First section in row -->
                        <?php 
                        $sectionName = $sections[$i];
                        $data = $schedulesBySection[$sectionName];
                        ?>
                        <div class="schedule-table-container">
                            <div class="section-header"><?= htmlspecialchars($sectionName) ?></div>
                            <div class="table-wrapper">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <?php foreach ($days as $day): ?>
                                                <th><?= $day ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($timeSlots as $timeSlot): ?>
                                            <tr>
                                                <td><?= $timeSlot ?></td>
                                                <?php foreach ($days as $day): ?>
                                                    <td>
                                                        <?php 
                                                        $schedule = getScheduleForSlot($data['schedule'], $day, $timeSlot);
                                                        if ($schedule): 
                                                            $courseCode = getCourseCode($schedule['SubjectCode'], $data['info']['ProgramID'], $courseCodeMap);
                                                        ?>
                                                            <span class="course-code">
                                                                <?= htmlspecialchars($courseCode) ?>
                                                            </span>
                                                            <span class="room-info">
                                                                <?= htmlspecialchars($schedule['RoomName']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Second section in row (if exists) -->
                        <?php if (isset($sections[$i + 1])): ?>
                            <?php 
                            $sectionName = $sections[$i + 1];
                            $data = $schedulesBySection[$sectionName];
                            ?>
                            <div class="schedule-table-container">
                                <div class="section-header"><?= htmlspecialchars($sectionName) ?></div>
                                <div class="table-wrapper">
                                    <table>
                                        <thead>
                                            <tr>
                                                <th>Time</th>
                                                <?php foreach ($days as $day): ?>
                                                    <th><?= $day ?></th>
                                                <?php endforeach; ?>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($timeSlots as $timeSlot): ?>
                                                <tr>
                                                    <td><?= $timeSlot ?></td>
                                                    <?php foreach ($days as $day): ?>
                                                        <td>
                                                            <?php 
                                                            $schedule = getScheduleForSlot($data['schedule'], $day, $timeSlot);
                                                            if ($schedule): 
                                                                $courseCode = getCourseCode($schedule['SubjectCode'], $data['info']['ProgramID'], $courseCodeMap);
                                                            ?>
                                                                <span class="course-code">
                                                                    <?= htmlspecialchars($courseCode) ?>
                                                                </span>
                                                                <span class="room-info">
                                                                    <?= htmlspecialchars($schedule['RoomName']) ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>