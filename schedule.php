<?php
require_once "db.php"; // ✅ Include database + queries

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

$allData = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $courses = ["it" => "BSIT", "is" => "BSIS", "cs" => "BSCS"];
    $years = ["firstyr" => 1, "secondyr" => 2, "thirdyr" => 3, "fourthyr" => 4];

    foreach ($courses as $courseKey => $courseLabel) {
        foreach ($years as $yearKey => $yearNumber) {
            $fieldName = "{$yearKey}_{$courseKey}_enrollees";
            if (isset($_POST[$fieldName])) {
                $enrollees = (int) $_POST[$fieldName];
                $sections = generateSections($courseLabel, $yearNumber, $enrollees);

                if (!isset($allData[$courseLabel][$yearNumber])) {
                    $allData[$courseLabel][$yearNumber] = [];
                }

                // ✅ Get courses for this program & year from db.php
                $coursesList = getCoursesByYearAndProgram($db, $yearNumber, $courseLabel);

                // Group courses by term
                $groupedByTerm = [
                    1 => [],
                    2 => [],
                    3 => []
                ];
                foreach ($coursesList as $course) {
                    $term = (int)$course['Term'];
                    if (isset($groupedByTerm[$term])) {
                        $groupedByTerm[$term][] = $course;
                    }
                }

                foreach ($sections as &$sec) {
                    $sec['courses'] = $groupedByTerm;
                }

                $allData[$courseLabel][$yearNumber] = array_merge(
                    $allData[$courseLabel][$yearNumber],
                    $sections
                );
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
            <h2><?php echo htmlspecialchars($course); ?></h2>
            <?php foreach ($years as $year => $sections) : ?>
                <?php foreach ($sections as $sec) : ?>
                    <h3><?php echo "Year " . htmlspecialchars($year) . " - Section " . htmlspecialchars($sec['section']); ?>:</h3>

                    <?php for ($t = 1; $t <= 3; $t++) : ?>
                        <h4>Term <?php echo $t; ?></h4>
                        <table border="1" cellpadding="5" cellspacing="0">
                            <tr>
                                <th>Code</th>
                                <th>Course Name</th>
                                <th>Lec Hrs</th>
                                <th>Lab Hrs</th>
                            </tr>
                            <?php if (!empty($sec['courses'][$t])) : ?>
                                <?php foreach ($sec['courses'][$t] as $courseRow) : ?>
                                    <tr>
                                    <td><?php echo htmlspecialchars($courseRow['Code']); ?></td>
                                    <td><?php echo htmlspecialchars($courseRow['Course_Title']); ?></td>
                                    <td><?php echo htmlspecialchars($courseRow['Lec_Hrs']); ?></td>
                                    <td><?php echo htmlspecialchars($courseRow['Lab_Hrs']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <tr><td colspan="4">No courses for Term <?php echo $t; ?></td></tr>
                            <?php endif; ?>
                        </table>
                        <br>
                    <?php endfor; ?>

                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php else : ?>
        <p>No data submitted. Please go back and enter enrollees.</p>
    <?php endif; ?>
</body>
</html>
