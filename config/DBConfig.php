<?php
// Database configuration with error logging
class DBConfig {
    // Default configuration
    private static $default = [
        'host' => 'localhost',
        'dbname' => 'student_housing',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ];

    // Store the connection instance
    private static $connection = null;
    private static $logger = null;

    // Initialize logging
    private static function initLogger() {
        if (self::$logger === null) {
            self::$logger = new DBLogger();
        }
        return self::$logger;
    }

    // Get database connection with error handling
    public static function getConnection() {
        if (self::$connection === null) {
            $logger = self::initLogger();
            
            try {
                $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s",
                    self::$default['host'],
                    self::$default['dbname'],
                    self::$default['charset']
                );

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ];

                self::$connection = new PDO(
                    $dsn,
                    self::$default['username'],
                    self::$default['password'],
                    $options
                );

                $logger->log("Database connection established successfully");

            } catch (PDOException $e) {
                $logger->log("Connection failed: " . $e->getMessage(), "ERROR");
                throw new Exception("Database connection failed. Check logs for details.");
            }
        }

        return self::$connection;
    }

    // Initialize/Reset database using SQL file
    public static function initializeDatabase($sqlFile = 'student_housing.sql') {
        $logger = self::initLogger();
        $logger->log("Starting database initialization");

        try {
            $conn = new PDO(
                "mysql:host=" . self::$default['host'],
                self::$default['username'],
                self::$default['password']
            );
            
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Create database if not exists
            $conn->exec("CREATE DATABASE IF NOT EXISTS " . self::$default['dbname']);
            $logger->log("Database created or already exists: " . self::$default['dbname']);

            // Select the database
            $conn->exec("USE " . self::$default['dbname']);

            // Read and execute SQL file
            $sql = file_get_contents($sqlFile);
            if ($sql === false) {
                throw new Exception("Error reading SQL file: $sqlFile");
            }

            $conn->exec($sql);
            $logger->log("Database initialized successfully from $sqlFile");
            
            return true;

        } catch (PDOException $e) {
            $logger->log("Database initialization failed: " . $e->getMessage(), "ERROR");
            throw new Exception("Database initialization failed. Check logs for details.");
        }
    }

    // Test database connection
    public static function testConnection() {
        $logger = self::initLogger();
        
        try {
            $conn = self::getConnection();
            $stmt = $conn->query("SELECT NOW() as time");
            $result = $stmt->fetch();
            
            $logger->log("Connection test successful. Server time: " . $result['time']);
            return true;
            
        } catch (Exception $e) {
            $logger->log("Connection test failed: " . $e->getMessage(), "ERROR");
            return false;
        }
    }
}