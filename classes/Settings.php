<?php

class Settings {
    private $db;
    private static $instance = null;
    private $settings = [];
    private $loaded = false;

    private function __construct() {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }

    // Get singleton instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Load all settings from database
    private function loadSettings() {
        if ($this->loaded) {
            return;
        }

        try {
            $this->db->query('SELECT setting_key, setting_value, is_serialized FROM system_settings');
            $results = $this->db->resultSet();
            
            foreach ($results as $row) {
                $value = $row->is_serialized ? unserialize($row->setting_value) : $row->setting_value;
                $this->settings[$row->setting_key] = $value;
            }
            
            $this->loaded = true;
        } catch (Exception $e) {
            // If table doesn't exist, create it
            $this->createSettingsTable();
        }
    }

    // Create settings table if it doesn't exist
    private function createSettingsTable() {
        $this->db->query('CREATE TABLE IF NOT EXISTS system_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            is_serialized TINYINT(1) DEFAULT 0,
            description VARCHAR(255) DEFAULT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (setting_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        
        $this->db->execute();
        
        // Insert default settings if table was just created
        if ($this->db->rowCount() === 0) {
            $this->setDefaultSettings();
        }
    }

    // Set default settings
    private function setDefaultSettings() {
        $defaults = [
            'site_name' => 'Parliament Library',
            'site_description' => 'Digital Library Management System',
            'items_per_page' => 10,
            'max_borrow_days' => 14,
            'max_renewals' => 2,
            'fine_per_day' => 0.50,
            'currency' => 'GHS',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'maintenance_mode' => false,
            'allow_registrations' => true,
            'default_user_role' => 'member',
            'email_notifications' => true,
            'smtp_host' => '',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_encryption' => 'tls',
            'from_email' => 'noreply@parliament.gh',
            'from_name' => 'Parliament Library'
        ];
        
        foreach ($defaults as $key => $value) {
            $this->set($key, $value);
        }
    }

    // Get a setting value
    public function get($key, $default = null) {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        }
        return $default;
    }

    // Set a setting value
    public function set($key, $value, $description = null) {
        $isSerialized = !is_string($value) && !is_numeric($value) && !is_bool($value);
        $valueToStore = $isSerialized ? serialize($value) : $value;
        
        // Check if setting already exists
        $this->db->query('SELECT id FROM system_settings WHERE setting_key = :key');
        $this->db->bind(':key', $key);
        $settingExists = (bool)$this->db->single();
        
        if ($settingExists) {
            // Update existing setting
            $sql = 'UPDATE system_settings SET 
                    setting_value = :value,
                    is_serialized = :is_serialized,
                    description = :description,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE setting_key = :key';
        } else {
            // Insert new setting
            $sql = 'INSERT INTO system_settings 
                   (setting_key, setting_value, is_serialized, description) 
                   VALUES (:key, :value, :is_serialized, :description)';
        }
        
        $this->db->query($sql);
        $this->db->bind(':key', $key);
        $this->db->bind(':value', $valueToStore);
        $this->db->bind(':is_serialized', $isSerialized ? 1 : 0, PDO::PARAM_INT);
        $this->db->bind(':description', $description);
        
        $result = $this->db->execute();
        
        if ($result) {
            // Update in-memory settings
            $this->settings[$key] = $value;
            return true;
        }
        
        return false;
    }

    // Delete a setting
    public function delete($key) {
        $this->db->query('DELETE FROM system_settings WHERE setting_key = :key');
        $this->db->bind(':key', $key);
        $result = $this->db->execute();
        
        if ($result) {
            unset($this->settings[$key]);
            return true;
        }
        
        return false;
    }

    // Get all settings as an array
    public function getAll() {
        return $this->settings;
    }

    // Get settings by prefix
    public function getByPrefix($prefix) {
        $filtered = [];
        $prefixLength = strlen($prefix);
        
        foreach ($this->settings as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $filtered[substr($key, $prefixLength)] = $value;
            }
        }
        
        return $filtered;
    }

    // Magic method for property access
    public function __get($name) {
        return $this->get($name);
    }

    // Magic method for property setting
    public function __set($name, $value) {
        return $this->set($name, $value);
    }

    // Check if a setting exists
    public function has($key) {
        return array_key_exists($key, $this->settings);
    }
}
