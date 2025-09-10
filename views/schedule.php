<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Generated Schedule</title>
<style>
table{border-collapse:collapse;width:100%;margin-bottom:30px}
th,td{padding:6px 8px;border:1px solid #ccc;text-align:left}
th{background:#f2f2f2}
</style>
</head>
<body>
<a href="../index.html">← Back to Index</a>
<h1>Generated Schedule</h1>

<?php foreach ([1,2,3] as $term): ?>
    <h2>Term <?= $term ?></h2>
    <table>
        <tr><th>Day</th><th>Course</th><th>Section</th><th>Time</th><th>Room</th><th>Part</th></tr>
        <?php foreach ($assignments as $a): ?>
            <?php if ($a['term'] == $term): ?>
                <tr>
                    <td><?= htmlspecialchars($a['day']) ?></td>
                    <td><?= htmlspecialchars($a['course_code'].' – '.$a['course_title']) ?></td>
                    <td><?= htmlspecialchars($a['section']) ?></td>
                    <td><?= htmlspecialchars($a['time']) ?></td>
                    <td><?= htmlspecialchars($a['room']) ?></td>
                    <td><?= htmlspecialchars($a['part']) ?></td>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
<?php endforeach; ?>

</body>
</html>
