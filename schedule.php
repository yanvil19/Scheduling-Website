<?php
// schedule.php

// Function to generate sections
function generateSections($course, $year, $enrollees, $maxClassSize = 40) {
    $sections = [];
    $sectionCount = ceil($enrollees / $maxClassSize);

    for ($i = 1; $i <= $sectionCount; $i++) {
        $sections[] = [
            'year' => $year,
            'section' => $i
        ];
    }

    return $sections;
}

// Only run when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $allData = [];

    // Define courses and years
    $courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
    $years = [
        "firstyr" => "Year 1",
        "secondyr" => "Year 2",
        "thirdyr" => "Year 3",
        "fourthyr" => "Year 4"
    ];

    // Process each course per year
    foreach ($courses as $courseKey => $courseLabel) {
        foreach ($years as $yearKey => $yearLabel) {
            $fieldName = "{$yearKey}_{$courseKey}_enrollees";
            if (isset($_POST[$fieldName])) {
                $enrollees = (int) $_POST[$fieldName];
                $sections = generateSections($courseLabel, $yearLabel, $enrollees);

                if (!isset($allData[$courseLabel])) {
                    $allData[$courseLabel] = [];
                }
                if (!isset($allData[$courseLabel][$yearLabel])) {
                    $allData[$courseLabel][$yearLabel] = [];
                }
                $allData[$courseLabel][$yearLabel] = array_merge($allData[$courseLabel][$yearLabel], $sections);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Generated Sections</title>
</head>
<body>
    <h1>Generated Class Sections</h1>

    <?php if (!empty($allData)) : ?>
        <?php foreach ($allData as $course => $years) : ?>
            <h2><?php echo $course; ?></h2>
            <?php foreach ($years as $year => $sections) : ?>
                <?php foreach ($sections as $sec) : ?>
                    <h3><?php echo $year . " - Section " . $sec['section']; ?>:</h3>
                    <table border="1" cellpadding="5" cellspacing="0">
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Room</th>
                            <th>Time</th>
                        </tr>
                        <!-- Empty rows for now -->
                         
                    </table>
                    <br>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No data submitted. Please go back and enter enrollees.</p>
    <?php endif; ?>
</body>
</html>
