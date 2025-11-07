<?php
// Database initialization script
// Run this script to set up or reset the database using student_housing.sql

// Include database configuration
require_once 'db.php';

// Path to SQL file
$sqlFile = __DIR__ . '/../student_housing.sql';

// Function to initialize database
function initializeDatabase($conn, $sqlFile) {
    // Check if SQL file exists
    if (!file_exists($sqlFile)) {
        die("Error: SQL file not found at: $sqlFile");
    }

    // Read SQL file
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        die("Error: Could not read SQL file");
    }

    echo "Starting database initialization...\n";

    // Execute the SQL
    try {
        if ($conn->multi_query($sql)) {
            do {
                // Store first result set
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());
        }

        echo "Database initialized successfully!\n";
        return true;

    } catch (Exception $e) {
        die("Error initializing database: " . $e->getMessage());
    }
}

// Run the initialization
try {
    initializeDatabase($conn, $sqlFile);
    echo "Database setup complete.\n";
    
} catch (Exception $e) {
    die("Setup failed: " . $e->getMessage());
}

// Close connection
$conn->close();
?>