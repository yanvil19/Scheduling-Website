<?php

$programs = [];
$yearlevels = [];
$sections = [];
$selectedProgram = '';
$selectedYear = '';
$selectedSection = '';

// make MySQLi connection
$conn = new mysqli("localhost", "root", "", "schedulingdb", 3307); // change port based on what you use; 3306 is default; special ako
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8");

// request for year levels based on program
if (isset($_GET['action']) && $_GET['action'] == 'get_years') {
    try {
        header('Content-Type: application/json');

        if (!isset($_GET['ProgramID'])) {
            echo json_encode(['error' => 'ProgramID required']);
            exit;
        }
        $programID = (int) $_GET['ProgramID'];
        $stmt = $conn->prepare("
            SELECT DISTINCT yl.YearLevelID
            FROM yearlevels yl
            JOIN sections s ON yl.YearLevelID = s.YearLevelID
            WHERE s.ProgramID = ?
            ORDER BY yl.YearLevelID
        ");
        $stmt->bind_param("i", $programID);
        $stmt->execute();
        $result = $stmt->get_result();
        $years_response = [];
        while ($row = $result->fetch_assoc()) {
            $years_response[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $years_response,
            'count' => count($years_response)
        ]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    }
    exit;
}

// request for sections based on program and year
if (isset($_GET['action']) && $_GET['action'] == 'get_sections') {
    try {
        header('Content-Type: application/json');

        if (!isset($_GET['ProgramID']) || !isset($_GET['YearLevelID'])) {
            echo json_encode(['error' => 'ProgramID and YearLevelID required']);
            exit;
        }
        $programID = (int) $_GET['ProgramID'];
        $yearLevelID = trim($_GET['YearLevelID']);
        $stmt = $conn->prepare("
            SELECT SectionID, SectionName 
            FROM sections 
            WHERE ProgramID = ? AND YearLevelID = ? 
            ORDER BY SectionName
        ");
        $stmt->bind_param("is", $programID, $yearLevelID);
        $stmt->execute();
        $result = $stmt->get_result();
        $sections_response = [];
        while ($row = $result->fetch_assoc()) {
            $sections_response[] = $row;
        }

        echo json_encode([
            'success' => true,
            'data' => $sections_response,
            'count' => count($sections_response)
        ]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    }
    exit;
}

// request for section-specific calendar schedule
if (isset($_GET['action']) && $_GET['action'] == 'get_calendar_schedule') {
    try {
        header('Content-Type: application/json');
        if (!isset($_GET['SectionName'])) {
            echo json_encode(['error' => 'No SectionName provided']);
            exit;
        }
        $sectionName = trim($_GET['SectionName']);

        $stmt = $conn->prepare("
            SELECT 
                sec.SectionName,
                sub.SubjectCode,
                sub.SubjectName,
                IF(sub.IsLab = 1, 'LAB', 'LEC') AS Type,
                r.RoomName,
                t.StartTime,
                t.EndTime,
                dt.duration AS Duration,
                d.day AS DayOfWeek,
                d.day_id
            FROM schedule sch
            JOIN sections sec ON sch.SectionID = sec.SectionID
            JOIN term2courses sub ON sch.SubjectID = sub.SubjectID
            JOIN rooms r ON sch.RoomID = r.RoomID
            JOIN timeslots t ON sch.TimeSlotID = t.TimeSlotID
            JOIN days_table d ON sch.day_id = d.day_id
            JOIN duration_table dt ON (
                CASE 
                    WHEN sub.IsLab = 1 THEN dt.duration_id = 2 
                    ELSE dt.duration_id = 1 
                END
            )
            WHERE sec.SectionName = ?
            ORDER BY d.day_id, t.StartTime
        ");

        $stmt->bind_param("s", $sectionName);
        $stmt->execute();
        $result = $stmt->get_result();
        $calendar_data = [];
        $time_slots = [];

        while ($row = $result->fetch_assoc()) {
            $day = $row['DayOfWeek'];
            $startTime = $row['StartTime'];
            $endTime = $row['EndTime'];

            if (!in_array($startTime, $time_slots)) {
                $time_slots[] = $startTime;
            }
            if (!isset($calendar_data[$day])) {
                $calendar_data[$day] = [];
            }

            $calendar_data[$day][$startTime] = [
                'subject' => $row['SubjectName'],
                'code' => $row['SubjectCode'],
                'type' => $row['Type'],
                'room' => $row['RoomName'],
                'start' => $startTime,
                'end' => $endTime,
                'duration' => $row['Duration']
            ];
        }
        sort($time_slots);

        echo json_encode([
            'success' => true,
            'calendar_data' => $calendar_data,
            'time_slots' => $time_slots,
            'section_searched' => $sectionName
        ]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    }
    exit;
}

// request for non-section-specific calendar schedules; many schedules
if (isset($_GET['action']) && $_GET['action'] == 'get_multiple_calendar_schedules') {
    try {
        header('Content-Type: application/json');
        if (!isset($_GET['ProgramID'])) {
            echo json_encode(['error' => 'ProgramID required']);
            exit;
        }

        $programID = (int) $_GET['ProgramID'];
        $yearLevelID = isset($_GET['YearLevelID']) ? trim($_GET['YearLevelID']) : null;
        $whereClause = "WHERE s.ProgramID = ?";
        $params = [$programID];
        $types = "i";

        if ($yearLevelID && !empty($yearLevelID)) {
            $whereClause .= " AND s.YearLevelID = ?";
            $params[] = $yearLevelID;
            $types .= "s";
        }

        $stmt = $conn->prepare("
            SELECT 
                sec.SectionName,
                sub.SubjectCode,
                sub.SubjectName,
                IF(sub.IsLab = 1, 'LAB', 'LEC') AS Type,
                r.RoomName,
                t.StartTime,
                t.EndTime,
                dt.duration AS Duration,
                d.day AS DayOfWeek,
                d.day_id
            FROM schedule sch
            JOIN sections sec ON sch.SectionID = sec.SectionID
            JOIN term2courses sub ON sch.SubjectID = sub.SubjectID
            JOIN rooms r ON sch.RoomID = r.RoomID
            JOIN timeslots t ON sch.TimeSlotID = t.TimeSlotID
            JOIN days_table d ON sch.day_id = d.day_id
            JOIN sections s ON sec.SectionID = s.SectionID
            JOIN duration_table dt ON (
                CASE 
                    WHEN sub.IsLab = 1 THEN dt.duration_id = 2 
                    ELSE dt.duration_id = 1 
                END
            )
            $whereClause
            ORDER BY sec.SectionName, d.day_id, t.StartTime
        ");

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $schedules_by_section = [];
        $all_time_slots = [];

        while ($row = $result->fetch_assoc()) {
            $sectionName = $row['SectionName'];
            $day = $row['DayOfWeek'];
            $startTime = $row['StartTime'];

            if (!isset($schedules_by_section[$sectionName])) {
                $schedules_by_section[$sectionName] = [];
            }
            if (!isset($schedules_by_section[$sectionName][$day])) {
                $schedules_by_section[$sectionName][$day] = [];
            }

            $schedules_by_section[$sectionName][$day][$startTime] = [
                'subject' => $row['SubjectName'],
                'code' => $row['SubjectCode'],
                'type' => $row['Type'],
                'room' => $row['RoomName'],
                'start' => $startTime,
                'end' => $row['EndTime'],
                'duration' => $row['Duration']
            ];

            if (!in_array($startTime, $all_time_slots)) {
                $all_time_slots[] = $startTime;
            }
        }
        sort($all_time_slots);

        echo json_encode([
            'success' => true,
            'schedules_by_section' => $schedules_by_section,
            'time_slots' => $all_time_slots,
            'total_sections' => count($schedules_by_section),
            'programID' => $programID,
            'yearLevelID' => $yearLevelID,
            'debug_total_rows' => $result->num_rows
        ]);

    } catch (Exception $e) {
        echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    }
    exit;
}


try {
    // get programs for dropdown
    $sql = "SELECT ProgramID, Name FROM programs ORDER BY Name";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
    }

    // get years for dropdown
    $sql = "SELECT DISTINCT YearLevelID FROM yearlevels ORDER BY YearLevelID";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $yearlevels[] = $row;
        }
    }

    // get sections based on previous dropdowns
    if (isset($_GET['ProgramID']) && isset($_GET['YearLevelID']) && !empty($_GET['ProgramID']) && !empty($_GET['YearLevelID'])) {
        $ProgramID = $conn->real_escape_string($_GET['ProgramID']);
        $YearLevelID = $conn->real_escape_string($_GET['YearLevelID']);

        $stmt = $conn->prepare("SELECT id, SectionName FROM sections WHERE ProgramID = ? AND YearLevelID = ? ORDER BY SectionName");
        $stmt->bind_param("is", $ProgramID, $YearLevelID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sections[] = $row;
            }
        }
        $selectedProgram = $_GET['ProgramID'];
        $selectedYear = $_GET['YearLevelID'];
        $stmt->close();
    } else {
        // when no section filter
        $sql = "SELECT SectionID, SectionName FROM sections ORDER BY SectionName";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $sections[] = $row;
            }
        }
    }

} catch (Exception $e) {
    $error = "Database error: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SigmaScheds</title>

    <link rel="stylesheet" href="stylesched.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anton&family=Audiowide&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Sarala:wght@400;700&display=swap"
        rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="upper-upper-home">
        <div class="upper-home">
            <img src="../assets/logo-1-10.png" alt="Logo" class="logo">
            <span class="name">SigmaScheds</span>
        </div>
    </div>

    <div class="lower-home">
        <div class="lower-upper">
            <div class="lower-left-part">
                <span class="title">View Schedules</span>
            </div>

            <div class="lower-right-part">
                <?php if (isset($error)): ?>
                    <div class="error-message"> <!-- let me know error -->
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- program dropdown -->
                <div class="dropdown-container">
                    <label for="ProgramID" class="program">PROGRAM</label>
                    <select name="ProgramID" id="ProgramID">
                        <option value="">-- Select Program --</option>
                        <?php foreach ($programs as $program): ?>
                            <option value="<?= (int) $program['ProgramID'] ?>" <?= ($selectedProgram == $program['Name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($program['Name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- year dropdown -->
                <div class="dropdown-container">
                    <label for="YearLevelID" class="year">YEAR</label>
                    <select name="YearLevelID" id="YearLevelID">
                        <option value="">-- Select Year --</option>
                        <?php foreach ($yearlevels as $year): ?>
                            <option value="<?= htmlspecialchars($year['YearLevelID']) ?>"
                                <?= ($selectedYear == $year['YearLevelID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['YearLevelID']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- section dropdown -->
                <div class="dropdown-container">
                    <label for="SectionId" class="section">SECTION</label>
                    <select name="SectionId" id="SectionId">
                        <option value="">-- Select Section --</option>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= (int) $section['SectionName'] ?>">
                                <?= htmlspecialchars($section['SectionName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="lower-lower">
            <div class="schedule-container" id="schedule-container" style="display: none;">
                <div class="schedule-header">
                    <span id="schedule-title">Class Schedule</span>
                </div>
                <div class="loading" id="loading" style="display: none;">
                    Loading schedule...
                </div>
                <div id="schedule-content">
                    <!-- schedule tables will be here -->
                </div>
            </div>
        </div>

    </div>

    <!-- javascript -->
    <script>
        $(document).ready(function () {
            // program dropdown
            $('#ProgramID').on('change', function () {
                const programID = $(this).val();
                console.log('Program selected:', programID);

                if (programID) {
                    loadYears(programID);
                    setTimeout(() => loadMultipleSchedules('program', programID), 100);
                } else {
                    resetYearDropdown();
                    resetSectionDropdown();
                    hideSchedule();
                }
            });

            // year level dropdown
            $('#YearLevelID').on('change', function () {
                const programID = $('#ProgramID').val();
                const yearLevelID = $(this).val();
                console.log('Year selected:', yearLevelID);

                if (programID && yearLevelID) {
                    loadSections(programID, yearLevelID);
                    setTimeout(() => loadMultipleSchedules('year', programID, yearLevelID), 100);
                } else if (programID) {
                    resetSectionDropdown();
                    setTimeout(() => loadMultipleSchedules('program', programID), 100);
                } else {
                    resetSectionDropdown();
                    hideSchedule();
                }
            });

            // sections dropdown
            $('#SectionId').on('change', function () {
                const selectedValue = $(this).val();
                const sectionName = $(this).find('option:selected').text();
                console.log('Section selected:', selectedValue);

                if (selectedValue && sectionName !== "-- Select Section --") {
                    loadCalendarSchedule(sectionName);
                } else {
                    const programID = $('#ProgramID').val();
                    const yearLevelID = $('#YearLevelID').val();
                    if (programID && yearLevelID) {
                        setTimeout(() => loadMultipleSchedules('year', programID, yearLevelID), 100);
                    } else if (programID) {
                        setTimeout(() => loadMultipleSchedules('program', programID), 100);
                    } else {
                        hideSchedule();
                    }
                }
            });

            // year loading
            function loadYears(programID) {
                console.log('Loading years for program:', programID);
                $('#YearLevelID').html('<option value="">Loading years...</option>');

                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    data: {
                        action: 'get_years',
                        ProgramID: programID
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('Years Response:', response);
                        const yearSelect = $('#YearLevelID');
                        yearSelect.html('<option value="">-- Select Year --</option>');

                        if (response.success && response.data && response.data.length > 0) {
                            $.each(response.data, function (index, year) {
                                yearSelect.append('<option value="' + year.YearLevelID + '">' +
                                    year.YearLevelID + '</option>');
                            });
                        } else {
                            yearSelect.append('<option value="">No years available</option>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading years:', error);
                        $('#YearLevelID').html('<option value="">Error loading years</option>');
                    }
                });
            }

            // sections loading
            function loadSections(programID, yearLevelID) {
                console.log('Loading sections for Program:', programID, 'Year:', yearLevelID);
                $('#SectionId').html('<option value="">Loading sections...</option>');

                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    data: {
                        action: 'get_sections',
                        ProgramID: programID,
                        YearLevelID: yearLevelID
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('Sections Response:', response);
                        const sectionSelect = $('#SectionId');
                        sectionSelect.html('<option value="">-- Select Section --</option>');

                        if (response.success && response.data && response.data.length > 0) {
                            $.each(response.data, function (index, section) {
                                sectionSelect.append('<option value="' + section.SectionName + '">' +
                                    section.SectionName + '</option>');
                            });
                        } else {
                            sectionSelect.append('<option value="">No sections found</option>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.error('Error loading sections:', error);
                        $('#SectionId').html('<option value="">Error loading sections</option>');
                    }
                });
            }

            // non-section-specific loading of schedules
            function loadMultipleSchedules(type, programID, yearLevelID = null) {
                console.log('Loading multiple calendar schedules for', type, programID, yearLevelID);
                $('#loading').show();
                $('#schedule-content').empty();
                $('#schedule-container').show();
                const requestData = {
                    action: 'get_multiple_calendar_schedules', // ← Changed to calendar version
                    ProgramID: programID
                };

                if (yearLevelID) {
                    requestData.YearLevelID = yearLevelID;
                }

                let titleText = 'Class Schedules';
                if (type === 'program') {
                    const programName = $('#ProgramID option:selected').text();
                    titleText = `All Sections - ${programName}`;
                } else if (type === 'year') {
                    const programName = $('#ProgramID option:selected').text();
                    const yearName = $('#YearLevelID option:selected').text();
                    titleText = `${programName} - Year ${yearName} Sections`;
                }
                $('#schedule-title').text(titleText);

                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    data: requestData,
                    dataType: 'json',
                    success: function (response) {
                        console.log('Multiple Calendar Schedules Response:', response);
                        $('#loading').hide();

                        if (response.success && response.schedules_by_section && Object.keys(response.schedules_by_section).length > 0) {
                            displayMultipleCalendarSchedules(response.schedules_by_section, response.time_slots, response.total_sections);
                        } else if (response.error) {
                            $('#schedule-content').html('<div class="no-schedule">Error: ' + response.error + '</div>');
                        } else {
                            $('#schedule-content').html('<div class="no-schedule">No schedules found for the selected criteria.</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        console.log('Multiple Calendar Schedules AJAX Error:');
                        console.log('Status:', xhr.status);
                        console.log('Response text:', xhr.responseText);
                        $('#loading').hide();
                        $('#schedule-content').html('<div class="no-schedule">Error loading schedules: ' + error + '</div>');
                    }
                });
            }

            // displaying calendars
            function displayMultipleCalendarSchedules(schedulesBySection, timeSlots, totalSections) {
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

                let contentHtml = `
                    <div class="multiple-calendars-container">
                `;

                Object.keys(schedulesBySection).forEach(sectionName => {
                    const sectionData = schedulesBySection[sectionName];

                    contentHtml += `
                        <div class="section-calendar-block">
                            <div class="section-calendar-header">
                                ${sectionName}
                            </div>
                            <div class="calendar-container">
                                <div class="calendar-grid">
                                    <!-- Header Row -->
                                    <div class="time-header">Time</div>
                    `;

                    days.forEach(day => {
                        contentHtml += `<div class="day-header">${day}</div>`;
                    });

                    const subjectColors = {};
                    const colors = ['#fcc60f', '#e87425', '#70bfb8', '#38a4d8', '#1c65ab', '#fffcc8'];
                    let colorIndex = 0;

                    timeSlots.forEach(timeSlot => {
                        const startTime = formatTime(timeSlot);
                        contentHtml += `
                            <div class="time-slot">${startTime}</div>`;
                        days.forEach(day => {
                            const dayData = sectionData[day];
                            const classData = dayData && dayData[timeSlot];

                            if (classData) {
                                const subjectKey = classData.code;
                                if (!subjectColors[subjectKey]) {
                                    subjectColors[subjectKey] = colors[colorIndex % colors.length];
                                    colorIndex++;
                                }

                                let endTime;
                                if (classData.type === 'LEC') {
                                    // add 1.335 hours (1 hour 20 minutes) to start time; lec
                                    endTime = calculateEndTime(classData.start, 1.335);
                                } else {
                                    // use the original end time or add 2 hours; lab
                                    endTime = classData.end ? formatTime(classData.end) : calculateEndTime(classData.start, 2.0);
                                }

                                contentHtml += `
                                <div class="schedule-block" style="background-color: ${subjectColors[subjectKey]}">
                                    <div class="subject-name">${classData.subject}</div>
                                    <div class="subject-details">
                                        ${formatTime(classData.start)}-${formatTime(classData.end)}<br>
                                        ${classData.room}
                                    </div>
                                </div>
                            `;
                            } else {
                                contentHtml += `<div class="empty-block"></div>`;
                            }
                        });
                    });

                    contentHtml += `
                                </div>
                            </div>
                        </div>
                    `;
                });

                contentHtml += '</div>';
                $('#schedule-content').html(contentHtml);
            }

            // for section loading
            function loadCalendarSchedule(sectionName) {
                console.log('Loading calendar schedule for:', sectionName);
                $('#loading').show();
                $('#schedule-content').empty();
                $('#schedule-container').show();

                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    data: {
                        action: 'get_calendar_schedule',
                        SectionName: sectionName
                    },
                    dataType: 'json',
                    success: function (response) {
                        console.log('Calendar Schedule Response:', response);
                        $('#loading').hide();
                        $('#schedule-title').text('Class Schedule - ' + sectionName);

                        if (response.success && response.calendar_data) {
                            displayCalendarSchedule(response.calendar_data, response.time_slots);
                        } else if (response.error) {
                            $('#schedule-content').html('<div class="no-schedule">Error: ' + response.error + '</div>');
                        } else {
                            $('#schedule-content').html('<div class="no-schedule">No schedule found for section: ' + sectionName + '</div>');
                        }
                    },
                    error: function (xhr, status, error) {
                        $('#loading').hide();
                        $('#schedule-content').html('<div class="no-schedule">Error: ' + error + '</div>');
                    }
                });
            }

            function displayCalendarSchedule(calendarData, timeSlots) {
                const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                const subjectColors = {};
                const colors = ['#fcc60f', '#e87425', '#70bfb8', '#38a4d8', '#1c65ab', '#fffcc8'];
                let colorIndex = 0;

                const sectionName = $('#schedule-title').text().replace('Class Schedule - ', '');

                let calendarHtml = `
                    <div class="calendar-container">
                    <div class="single-section-header">
                        ${sectionName}
                    </div>
                        <div class="calendar-grid">
                            <!-- Header Row -->
                            <div class="time-header">Time</div>
                `;

                days.forEach(day => {
                    calendarHtml += `<div class="day-header">${day}</div>`;
                });

                timeSlots.forEach(timeSlot => {
                    const startTime = formatTime(timeSlot);
                    calendarHtml += `<div class="time-slot">${startTime}</div>`;

                    days.forEach(day => {
                        const dayData = calendarData[day];
                        const classData = dayData && dayData[timeSlot];

                        if (classData) {
                            const subjectKey = classData.code;
                            if (!subjectColors[subjectKey]) {
                                subjectColors[subjectKey] = colors[colorIndex % colors.length];
                                colorIndex++;
                            }

                            let endTime;
                            if (classData.type === 'LEC') {
                                // add 1.335 hours (1 hour 20 minutes); lec
                                endTime = calculateEndTime(classData.start, 1.335);
                            } else {
                                // use original end time or add 2 hours; lab
                                endTime = classData.end ? formatTime(classData.end) : calculateEndTime(classData.start, 2.0);
                            }

                            calendarHtml += `
                                <div class="schedule-block" style="background-color: ${subjectColors[subjectKey]}">
                                    <div class="subject-name">${classData.subject}</div>
                                    <div class="subject-details">
                                        ${formatTime(classData.start)}-${formatTime(classData.end)}<br>
                                        ${classData.room}
                                    </div>
                                </div>
                            `;
                        } else {
                            calendarHtml += `<div class="empty-block"></div>`;
                        }
                    });
                });

                calendarHtml += `
                        </div>
                    </div>
                `;

                $('#schedule-content').html(calendarHtml);
            }

            // schedule table itself
            function displaySchedule(schedules) {
                let tableHtml = `
                    <table class="schedule-table">
                        <thead>
                            <tr>
                                <th>Subject Code</th>
                                <th>Subject Name</th>
                                <th>Type</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Room</th>
                                <th>Duration (hrs)</th>
                            </tr>
                        </thead>
                        <tbody>
                `;

                $.each(schedules, function (index, schedule) {
                    const startTime = schedule.StartTime ? formatTime(schedule.StartTime) : 'TBD';
                    const endTime = schedule.EndTime ? formatTime(schedule.EndTime) : 'TBD';

                    tableHtml += `
                        <tr>
                            <td>${schedule.SubjectCode || 'N/A'}</td>
                            <td>${schedule.SubjectName || 'N/A'}</td>
                            <td>${schedule.Type || 'N/A'}</td>
                            <td>${schedule.DayOfWeek || 'TBD'}</td>
                            <td>${startTime} - ${endTime}</td>
                            <td>${schedule.RoomName || 'N/A'}</td>
                            <td>${parseFloat(schedule.Duration || 0).toFixed(1)}</td>
                        </tr>
                    `;
                });

                tableHtml += '</tbody></table>';
                $('#schedule-content').html(tableHtml);
            }

            // military time from db to 12hrs format
            function formatTime(timeString) {
                try {
                    const time = new Date('1970-01-01T' + timeString);
                    return time.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                } catch (e) {
                    return timeString;
                }
            }

            // calculate end time based on start time and duration
            function calculateEndTime(startTime, durationHours) {
                try {
                    const start = new Date('1970-01-01T' + startTime);
                    const endTimeMs = start.getTime() + (durationHours * 60 * 60 * 1000);
                    const endTime = new Date(endTimeMs);

                    return endTime.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                } catch (e) {
                    console.error('Error calculating end time:', e);
                    return 'TBD';
                }
            }

            // resetting dropdown
            function resetYearDropdown() {
                $('#YearLevelID').html('<option value="">-- Select Year --</option>').val('');
            }

            function resetSectionDropdown() {
                $('#SectionId').html('<option value="">-- Select Section --</option>').val('');
            }

            function hideSchedule() {
                $('#schedule-container').hide();
            }

            resetYearDropdown();
            resetSectionDropdown();
            hideSchedule();

            console.log('Dropdown system initialized');
        });
    </script>

</body>

</html>

<?php

// for closing the database connection
$conn->close();
?>