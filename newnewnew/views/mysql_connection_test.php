<?php
echo "<h2>MySQL Connection Test</h2>";

// Test if MySQL is running
$servername = "localhost";
$username = "root";
$password = "";


echo "<p>Testing connection to MySQL server...</p>";

try {
    // Try to connect without specifying a database first
    $conn = new mysqli($servername, $username, $password);

    if ($conn->connect_error) {
        echo "<div style='color: red; background: #ffebee; padding: 15px; border-radius: 5px;'>";
        echo "<strong>❌ CONNECTION FAILED:</strong><br>";
        echo "Error: " . $conn->connect_error . "<br>";
        echo "<strong>This means MySQL service is NOT running!</strong>";
        echo "</div>";

        echo "<h3>🔧 SOLUTION STEPS:</h3>";
        echo "<ol>";
        echo "<li><strong>Open XAMPP Control Panel</strong></li>";
        echo "<li><strong>Look for MySQL row</strong></li>";
        echo "<li><strong>Click START button next to MySQL</strong></li>";
        echo "<li><strong>Wait for it to show 'Running' in green</strong></li>";
        echo "<li><strong>Refresh this page</strong></li>";
        echo "</ol>";

    } else {
        echo "<div style='color: green; background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
        echo "<strong>✅ SUCCESS: Connected to MySQL server!</strong><br>";
        echo "Server info: " . $conn->server_info . "<br>";
        echo "</div>";

        // Now test if the database exists
        echo "<h3>Testing Database 'schedulingdb':</h3>";
        $result = $conn->query("SHOW DATABASES LIKE 'schedulingdb'");

        if ($result->num_rows > 0) {
            echo "<div style='color: green; background: #e8f5e8; padding: 15px; border-radius: 5px;'>";
            echo "✅ Database 'schedulingdb' exists!";
            echo "</div>";

            // Test tables
            $conn->select_db("schedulingdb");
            echo "<h3>Checking Tables:</h3>";

            $tables = ['programs', 'yearlevels', 'sections'];
            foreach ($tables as $table) {
                $result = $conn->query("SHOW TABLES LIKE '$table'");
                if ($result->num_rows > 0) {
                    echo "<p>✅ Table '$table' exists</p>";

                    // Count records
                    $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
                    $count = $count_result->fetch_assoc()['count'];
                    echo "<p>&nbsp;&nbsp;&nbsp;Records: $count</p>";
                } else {
                    echo "<p>❌ Table '$table' missing</p>";
                }
            }

        } else {
            echo "<div style='color: orange; background: #fff3cd; padding: 15px; border-radius: 5px;'>";
            echo "⚠️ Database 'schedulingdb' does not exist!<br>";
            echo "You need to create it in phpMyAdmin.";
            echo "</div>";
        }

        $conn->close();
    }
} catch(Exception $e) {
    echo "<div style='color: red; background: #ffebee; padding: 15px; border-radius: 5px;'>";
    echo "<strong>❌ ERROR:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<hr>";
echo "<h3>🔍 XAMPP Status Check:</h3>";
echo "<p>Your XAMPP should show:</p>";
echo "<ul>";
echo "<li><strong>Apache:</strong> <span style='color: green; font-weight: bold;'>Running</span></li>";
echo "<li><strong>MySQL:</strong> <span style='color: green; font-weight: bold;'>Running</span></li>";
echo "</ul>";

echo "<h3>📍 Quick Links:</h3>";
echo "<p><a href='http://localhost/phpmyadmin' target='_blank'>📊 Open phpMyAdmin</a></p>";
echo "<p><a href='http://localhost' target='_blank'>🏠 XAMPP Dashboard</a></p>";

?>