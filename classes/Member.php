<?php
class Member {
    private $db;
    private $id;
    private $userId;
    private $memberId;
    private $phone;
    private $address;
    private $dateOfBirth;
    private $membershipType;
    private $membershipExpiry;
    private $createdAt;
    private $updatedAt;
    private $userData;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get member by user ID
    public function getMemberByUserId($userId) {
        $this->db->query('SELECT m.*, u.username, u.email, u.first_name, u.last_name, u.role, u.status 
                         FROM members m 
                         JOIN users u ON m.user_id = u.id 
                         WHERE m.user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        
        $row = $this->db->single();
        
        if ($row) {
            $this->id = $row->id;
            $this->userId = $row->user_id;
            $this->memberId = $row->member_id;
            $this->phone = $row->phone;
            $this->address = $row->address;
            $this->dateOfBirth = $row->date_of_birth;
            $this->membershipType = $row->membership_type;
            $this->membershipExpiry = $row->membership_expiry;
            $this->createdAt = $row->created_at;
            $this->updatedAt = $row->updated_at;
            
            // Store user data
            $this->userData = [
                'username' => $row->username,
                'email' => $row->email,
                'first_name' => $row->first_name,
                'last_name' => $row->last_name,
                'role' => $row->role,
                'status' => $row->status
            ];
            
            return $this->getMemberData();
        }
        
        return false;
    }

    // Get all members with pagination
    public function getAllMembers($page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $this->db->query('SELECT m.*, u.username, u.email, u.first_name, u.last_name, u.status 
                         FROM members m 
                         JOIN users u ON m.user_id = u.id 
                         ORDER BY m.created_at DESC 
                         LIMIT :limit OFFSET :offset');
        
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Add a new member
    public function addMember($data) {
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Create user account
            $user = new User();
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'member',
                'status' => 'active'
            ];
            
            $result = $user->register($userData);
            
            if (!$result['success']) {
                $this->db->rollBack();
                return $result;
            }
            
            $userId = $result['user_id'];
            $memberId = 'MEM-' . date('Y') . '-' . str_pad($userId, 4, '0', STR_PAD_LEFT);
            $expiryDate = date('Y-m-d', strtotime('+1 year'));
            
            // Insert member record
            $this->db->query('INSERT INTO members 
                             (user_id, member_id, phone, address, date_of_birth, membership_type, membership_expiry) 
                             VALUES (:user_id, :member_id, :phone, :address, :date_of_birth, :membership_type, :membership_expiry)');
            
            $this->db->bind(':user_id', $userId);
            $this->db->bind(':member_id', $memberId);
            $this->db->bind(':phone', $data['phone'] ?? null);
            $this->db->bind(':address', $data['address'] ?? null);
            $this->db->bind(':date_of_birth', $data['date_of_birth'] ?? null);
            $this->db->bind(':membership_type', $data['membership_type'] ?? 'student');
            $this->db->bind(':membership_expiry', $expiryDate);
            
            if (!$this->db->execute()) {
                throw new Exception('Failed to create member record');
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'member_id' => $memberId,
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Get member data as array
    public function getMemberData() {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'member_id' => $this->memberId,
            'phone' => $this->phone,
            'address' => $this->address,
            'date_of_birth' => $this->dateOfBirth,
            'membership_type' => $this->membershipType,
            'membership_expiry' => $this->membershipExpiry,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'user' => $this->userData
        ];
    }

    // Check if member can borrow more books
    public function canBorrow($memberId) {
        // Check if membership is active
        $this->db->query('SELECT membership_expiry FROM members WHERE id = :id');
        $this->db->bind(':id', $memberId);
        $member = $this->db->single();
        
        if (!$member || strtotime($member->membership_expiry) < time()) {
            return false;
        }
        
        // Check current borrowings (max 5 books)
        $this->db->query('SELECT COUNT(*) as count FROM borrows 
                         WHERE member_id = :member_id AND status != "returned"');
        $this->db->bind(':member_id', $memberId);
        $result = $this->db->single();
        
        return $result && $result->count < 5;
    }
}
