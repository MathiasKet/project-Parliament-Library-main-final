<?php
class User {
    private $db;
    private $id;
    private $username;
    private $email;
    private $role;
    private $status;
    private $createdAt;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Register a new user
    public function register($data) {
        // Check if username already exists
        $this->db->query('SELECT id FROM users WHERE username = :username');
        $this->db->bind(':username', $data['username']);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username already exists'];
        }

        // Check if email already exists
        $this->db->query('SELECT id FROM users WHERE email = :email');
        $this->db->bind(':email', $data['email']);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already exists'];
        }

        // Hash password
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insert user
        $this->db->query('INSERT INTO users (username, email, password, first_name, last_name, role) 
                         VALUES (:username, :email, :password, :first_name, :last_name, :role)');
        
        // Bind values
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':first_name', $data['first_name']);
        $this->db->bind(':last_name', $data['last_name']);
        $this->db->bind(':role', $data['role'] ?? 'member');

        // Execute
        if ($this->db->execute()) {
            return ['success' => true, 'user_id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Something went wrong'];
        }
    }

    // Login user
    public function login($username, $password) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        
        $row = $this->db->single();

        if ($row) {
            $hashedPassword = $row->password;
            if (password_verify($password, $hashedPassword)) {
                $this->id = $row->id;
                $this->username = $row->username;
                $this->email = $row->email;
                $this->role = $row->role;
                $this->status = $row->status;
                $this->createdAt = $row->created_at;
                
                // Update last login
                $this->updateLastLogin();
                
                return true;
            }
        }
        
        return false;
    }

    // Update last login timestamp
    private function updateLastLogin() {
        $this->db->query('UPDATE users SET last_login = NOW() WHERE id = :id');
        $this->db->bind(':id', $this->id);
        $this->db->execute();
    }

    // Get user by ID
    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if ($row) {
            $this->id = $row->id;
            $this->username = $row->username;
            $this->email = $row->email;
            $this->role = $row->role;
            $this->status = $row->status;
            $this->createdAt = $row->created_at;
            
            return $this->getUserData();
        }
        
        return false;
    }

    // Get user data as array
    public function getUserData() {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->createdAt
        ];
    }

    // Check if user is logged in
    public function isLoggedIn() {
        return isset($this->id);
    }

    // Check if user is admin
    public function isAdmin() {
        return $this->role === 'admin';
    }

    // Logout user
    public function logout() {
        session_destroy();
        return true;
    }

    // Get all users (for admin)
    public function getAllUsers() {
        $this->db->query('SELECT id, username, email, role, status, created_at FROM users ORDER BY created_at DESC');
        return $this->db->resultSet();
    }

    // Update user profile
    public function updateProfile($data) {
        $sql = 'UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email';
        $params = [
            ':id' => $this->id,
            ':first_name' => $data['first_name'],
            ':last_name' => $data['last_name'],
            ':email' => $data['email']
        ];

        // If password is being updated
        if (!empty($data['password'])) {
            $sql .= ', password = :password';
            $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $sql .= ' WHERE id = :id';
        
        $this->db->query($sql);
        
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        return $this->db->execute();
    }
}
