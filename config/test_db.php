<?php
// Database connection test utility
require_once 'db.php';

function testDatabaseConnection($conn) {
    $tests = [
        'Connection Status' => function($conn) {
            return $conn->ping() ? "OK" : "Failed";
        },
        'Server Info' => function($conn) {
            return $conn->server_info;
        },
        'Character Set' => function($conn) {
            return $conn->character_set_name();
        },
        'Database Selected' => function($conn) {
            return $conn->select_db('student_housing') ? "OK" : "Failed";
        },
        'Tables Access' => function($conn) {
            $result = $conn->query("SHOW TABLES");
            if (!$result) return "Failed";
            $tables = [];
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            return implode(", ", $tables);
        }
    ];

    echo "Database Connection Test Results:\n";
    echo "================================\n\n";

    foreach ($tests as $name => $test) {
        try {
            $result = $test($conn);
            echo "$name: $result\n";
        } catch (Exception $e) {
            echo "$name: Error - " . $e->getMessage() . "\n";
        }
    }
}

// Run the tests
try {
    testDatabaseConnection($conn);
} catch (Exception $e) {
    echo "Test failed: " . $e->getMessage() . "\n";
}

// Close connection
$conn->close();
?>