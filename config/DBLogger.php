<?php
// Database logging utility

class DBLogger {
    private $logPath;
    private static $instance = null;

    private function __construct() {
        $this->logPath = __DIR__ . '/logs/db.log';
        
        // Create logs directory if it doesn't exist
        if (!is_dir(dirname($this->logPath))) {
            mkdir(dirname($this->logPath), 0777, true);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DBLogger();
        }
        return self::$instance;
    }

    public function log($message, $level = 'INFO') {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[$timestamp] [$level] $message\n";
        
        file_put_contents($this->logPath, $logMessage, FILE_APPEND);
    }

    public function error($message) {
        $this->log($message, 'ERROR');
    }

    public function info($message) {
        $this->log($message, 'INFO');
    }

    public function warning($message) {
        $this->log($message, 'WARNING');
    }
}

// Example usage:
// $logger = DBLogger::getInstance();
// $logger->info("Database connection successful");
// $logger->error("Query failed: " . $error_message);
?>