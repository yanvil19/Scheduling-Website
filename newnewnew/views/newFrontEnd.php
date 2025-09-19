<?php

// Initialize variables
$programs = [];
$yearlevels = [];
$sections = [];
$selectedProgram = '';
$selectedYear = '';
$selectedSection = '';

// Create MySQLi connection
$conn = new mysqli("localhost", "root", "", "schedulingdb", 3307);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8
$conn->set_charset("utf8");

try {
    // Fetch programs for dropdown
    $sql = "SELECT ProgramID, Name FROM programs ORDER BY Name";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
    }

    // Fetch years for dropdown
    $sql = "SELECT DISTINCT YearLevelID FROM yearlevels ORDER BY YearLevelID";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $yearlevels[] = $row;
        }
    }

    // Fetch sections based on selected program and year (if any)
    if (isset($_GET['ProgramID']) && isset($_GET['YearLevelID']) && !empty($_GET['ProgramID']) && !empty($_GET['YearLevelID'])) {
        $ProgramID = $conn->real_escape_string($_GET['ProgramID']);
        $YearLevelID = $conn->real_escape_string($_GET['YearLevelID']);

        // Use prepared statement for security
        $stmt = $conn->prepare("SELECT id, SectionName FROM sections WHERE ProgramID = ? AND YearLevelID = ? ORDER BY SectionName");
        $stmt->bind_param("is", $ProgramID, $YearLevelID);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $sections[] = $row;
            }
        }

        $selectedProgram = $_GET['ProgramID'];
        $selectedYear = $_GET['YearLevelID'];
        $stmt->close();
    } else {
        // Get all sections if no filter
        $sql = "SELECT SectionID, SectionName FROM sections ORDER BY SectionName";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $sections[] = $row;
            }
        }
    }

} catch(Exception $e) {
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
                <span class="title">Schedules</span>
            </div>

            <div class="lower-right-part">
                <?php if (isset($error)): ?>
                    <div class="error-message"> <!-- For Erroring -->
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <!-- Program Dropdown -->
                <div class="dropdown-container">
                    <label for="ProgramID" class="program">PROGRAM</label>
                    <select name="ProgramID" id="ProgramID">
                        <option value="">-- Select Program --</option> <!-- Default option -->
                        <?php foreach($programs as $program): ?>
                            <option value="<?= (int)$program['Name'] ?>"  
                                <?= ($selectedProgram == $program['Name']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($program['Name']) ?> <!-- Fetching from Database -->
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Year Dropdown -->
                <div class="dropdown-container">
                    <label for="YearLevelID" class="year">YEAR</label>
                    <select name="YearLevelID" id="YearLevelID">
                        <option value="">-- Select Year --</option>
                        <?php foreach($yearlevels  as $year): ?>
                            <option value="<?= htmlspecialchars($year['YearLevelID']) ?>"
                                <?= ($selectedYear == $year['YearLevelID']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($year['YearLevelID']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Section Dropdown -->
                <div class="dropdown-container">
                    <label for="SectionId" class="section">SECTION</label>
                    <select name="SectionId" id="SectionId">
                        <option value="">-- Select Section --</option>
                        <?php foreach($sections as $section): ?>
                            <option value="<?= (int)$section['SectionName'] ?>">
                                <?= htmlspecialchars($section['SectionName']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="lower-lower"></div>
    </div>

    <!-- Insert JS Functions for Dropdown Interface -->
    <script> 
        // I aspire to be a Frontend Developer; ginusto ko 'to
    </script>
</body>

</html>

<?php
// Close the database connection
$conn->close();
?>