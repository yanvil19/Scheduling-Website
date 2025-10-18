<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SigmaScheds</title>

    <!-- Styles -->
    <link rel="stylesheet" href="stylesched.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Anton&family=Audiowide&family=Poppins:wght@300;400;500;600;700&family=Sarala:wght@400;700&display=swap"
        rel="stylesheet">

    <!-- JS -->
    <script src="schedule.js" defer></script>
</head>

<body>
    <!-- Header -->
    <div class="upper-upper-home">
        <div class="upper-home">
            <img src="assets/logo.png" alt="Logo" class="logo">
            <span class="name">SigmaScheds</span>
        </div>
    </div>

    <!-- Main Section -->
    <div class="lower-home">
        <!-- Title + Dropdowns -->
        <div class="lower-upper">
            <div class="lower-left-part">
                <span class="title">View Schedules</span>
            </div>

            <div class="lower-right-part">
                <!-- CSV Download button -->
                <div class="dropdown-container">
                    <label class="download-label">&nbsp;</label>
                    <button id="download-csv" class="download-btn" type="button">
                        Download Schedules
                    </button>
                </div>

                <!-- Program dropdown -->
                <div class="dropdown-container">
                    <label for="program" class="program">PROGRAM</label>
                    <select id="program">
                        <option value="">All Programs</option>
                        <?php
                        include "config.php";
                        $res = $conn->query("SELECT Code, Name FROM programs ORDER BY Priority ASC");
                        while ($row = $res->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['Code']) . "'>"
                                . htmlspecialchars($row['Name']) .
                                "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Year dropdown -->
                <div class="dropdown-container">
                    <label for="year" class="year">YEAR</label>
                    <select id="year">
                        <option value="">All Years</option>
                        <option value="1">1st Year</option>
                        <option value="2">2nd Year</option>
                        <option value="3">3rd Year</option>
                        <option value="4">4th Year</option>
                    </select>
                </div>

                <!-- Section dropdown -->
                <div class="dropdown-container">
                    <label for="section" class="section">SECTION</label>
                    <select id="section">
                        <option value="">All Sections</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Schedule Output -->
        <div class="lower-lower">
            <div class="schedule-container" id="schedule-container">
                <div class="schedule-header">
                    <span id="schedule-title">Class Schedule</span>
                </div>
                <div class="loading" id="loading" style="display: none;">
                    Loading schedule...
                </div>
                <div id="schedule-content">
                    <!-- schedule tables will be injected here -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>