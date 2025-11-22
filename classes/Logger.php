<?php

class Logger {
    private static $instance = null;
    private $logFile;
    private $logToFile = true;
    private $logToDatabase = false;
    private $logLevels = [
        'emergency' => 0,
        'alert'     => 1,
        'critical'  => 2,
        'error'     => 3,
        'warning'   => 4,
        'notice'    => 5,
        'info'      => 6,
        'debug'     => 7
    ];
    private $minLogLevel = 'debug';
    private $db;
    
    private function __construct() {
        $this->logFile = __DIR__ . '/../logs/app-' . date('Y-m-d') . '.log';
        $this->ensureLogDirectoryExists();
        $this->db = Database::getInstance();
    }
    
    // Get singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Ensure log directory exists
    private function ensureLogDirectoryExists() {
        $logDir = dirname($this->logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    // Set log file path
    public function setLogFile($path) {
        $this->logFile = $path;
        $this->ensureLogDirectoryExists();
        return $this;
    }
    
    // Enable/disable file logging
    public function setFileLogging($enabled) {
        $this->logToFile = (bool)$enabled;
        return $this;
    }
    
    // Enable/disable database logging
    public function setDatabaseLogging($enabled) {
        $this->logToDatabase = (bool)$enabled;
        return $this;
    }
    
    // Set minimum log level
    public function setMinLogLevel($level) {
        $level = strtolower($level);
        if (array_key_exists($level, $this->logLevels)) {
            $this->minLogLevel = $level;
        }
        return $this;
    }
    
    // Check if the log level should be logged
    private function shouldLog($level) {
        $level = strtolower($level);
        if (!isset($this->logLevels[$level])) {
            return false;
        }
        
        return $this->logLevels[$level] <= $this->logLevels[$this->minLogLevel];
    }
    
    // Format log message
    private function formatMessage($level, $message, $context = []) {
        $timestamp = date('Y-m-d H:i:s');
        $level = strtoupper($level);
        
        // Replace context in message
        if (!empty($context)) {
            $replace = [];
            foreach ($context as $key => $value) {
                $replace['{' . $key . '}'] = is_string($value) ? $value : json_encode($value);
            }
            $message = strtr($message, $replace);
        }
        
        return "[$timestamp] [$level] $message" . PHP_EOL;
    }
    
    // Write log to file
    private function writeToFile($message) {
        if ($this->logToFile) {
            file_put_contents($this->logFile, $message, FILE_APPEND | LOCK_EX);
        }
    }
    
    // Write log to database
    private function writeToDatabase($level, $message, $context = []) {
        if (!$this->logToDatabase) {
            return;
        }
        
        try {
            // Ensure logs table exists
            $this->createLogsTableIfNotExists();
            
            // Insert log record
            $this->db->query('INSERT INTO system_logs 
                             (level, message, context, ip_address, user_agent, user_id) 
                             VALUES (:level, :message, :context, :ip_address, :user_agent, :user_id)');
            
            $this->db->bind(':level', $level);
            $this->db->bind(':message', $message);
            $this->db->bind(':context', !empty($context) ? json_encode($context) : null);
            $this->db->bind(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
            $this->db->bind(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);
            $this->db->bind(':user_id', $_SESSION['user_id'] ?? null);
            
            return $this->db->execute();
        } catch (Exception $e) {
            // Fallback to file logging if database logging fails
            $this->writeToFile("Failed to write to database log: " . $e->getMessage() . PHP_EOL);
        }
    }
    
    // Create logs table if it doesn't exist
    private function createLogsTableIfNotExists() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS system_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                level VARCHAR(20) NOT NULL,
                message TEXT NOT NULL,
                context TEXT,
                ip_address VARCHAR(45),
                user_agent TEXT,
                user_id INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_level (level),
                INDEX idx_created_at (created_at),
                INDEX idx_user_id (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
        $this->db->execute();
    }
    
    // Log a message
    public function log($level, $message, $context = []) {
        if (!$this->shouldLog($level)) {
            return false;
        }
        
        $formattedMessage = $this->formatMessage($level, $message, $context);
        
        // Write to file
        $this->writeToFile($formattedMessage);
        
        // Write to database
        $this->writeToDatabase($level, $message, $context);
        
        return true;
    }
    
    // Magic methods for different log levels
    public function emergency($message, array $context = []) {
        return $this->log('emergency', $message, $context);
    }
    
    public function alert($message, array $context = []) {
        return $this->log('alert', $message, $context);
    }
    
    public function critical($message, array $context = []) {
        return $this->log('critical', $message, $context);
    }
    
    public function error($message, array $context = []) {
        return $this->log('error', $message, $context);
    }
    
    public function warning($message, array $context = []) {
        return $this->log('warning', $message, $context);
    }
    
    public function notice($message, array $context = []) {
        return $this->log('notice', $message, $context);
    }
    
    public function info($message, array $context = []) {
        return $this->log('info', $message, $context);
    }
    
    public function debug($message, array $context = []) {
        return $this->log('debug', $message, $context);
    }
    
    // Get logs from database
    public function getLogs($filters = [], $limit = 50, $offset = 0) {
        try {
            $sql = 'SELECT * FROM system_logs WHERE 1=1';
            $params = [];
            
            // Apply filters
            if (!empty($filters['level'])) {
                $sql .= ' AND level = :level';
                $params[':level'] = $filters['level'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= ' AND user_id = :user_id';
                $params[':user_id'] = $filters['user_id'];
            }
            
            if (!empty($filters['start_date'])) {
                $sql .= ' AND created_at >= :start_date';
                $params[':start_date'] = $filters['start_date'];
            }
            
            if (!empty($filters['end_date'])) {
                $sql .= ' AND created_at <= :end_date';
                $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
            }
            
            if (!empty($filters['search'])) {
                $sql .= ' AND (message LIKE :search OR context LIKE :search)';
                $params[':search'] = "%{$filters['search']}%";
            }
            
            // Add sorting
            $sort = $filters['sort'] ?? 'created_at';
            $order = $filters['order'] ?? 'DESC';
            $sql .= " ORDER BY $sort $order";
            
            // Add pagination
            $sql .= ' LIMIT :limit OFFSET :offset';
            
            $this->db->query($sql);
            
            // Bind parameters
            foreach ($params as $key => $value) {
                $this->db->bind($key, $value);
            }
            
            // Bind limit and offset
            $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
            $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
            
            return $this->db->resultSet();
            
        } catch (Exception $e) {
            $this->error('Failed to retrieve logs', ['error' => $e->getMessage()]);
            return [];
        }
    }
    
    // Get log levels
    public function getLogLevels() {
        return array_keys($this->logLevels);
    }
    
    // Clean up old logs
    public function cleanupOldLogs($days = 30) {
        try {
            $this->db->query('DELETE FROM system_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
            $this->db->bind(':days', (int)$days, PDO::PARAM_INT);
            $result = $this->db->execute();
            
            if ($result) {
                $count = $this->db->rowCount();
                $this->info("Cleaned up $count old log entries");
                return $count;
            }
            
            return 0;
            
        } catch (Exception $e) {
            $this->error('Failed to clean up old logs', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
