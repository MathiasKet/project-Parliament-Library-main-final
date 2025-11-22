<?php

class Notification {
    private $db;
    private $id;
    private $userId;
    private $title;
    private $message;
    private $type; // info, success, warning, danger
    private $isRead;
    private $createdAt;
    private $link;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Create a new notification
    public function create($userId, $title, $message, $type = 'info', $link = null) {
        $this->db->query('INSERT INTO notifications 
                         (user_id, title, message, type, link) 
                         VALUES (:user_id, :title, :message, :type, :link)');
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':title', $title);
        $this->db->bind(':message', $message);
        $this->db->bind(':type', $type);
        $this->db->bind(':link', $link);
        
        return $this->db->execute() ? $this->db->lastInsertId() : false;
    }

    // Get notification by ID
    public function getNotification($id) {
        $this->db->query('SELECT * FROM notifications WHERE id = :id');
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if ($row) {
            $this->id = $row->id;
            $this->userId = $row->user_id;
            $this->title = $row->title;
            $this->message = $row->message;
            $this->type = $row->type;
            $this->isRead = (bool)$row->is_read;
            $this->createdAt = $row->created_at;
            $this->link = $row->link;
            
            return $this->getNotificationData();
        }
        
        return false;
    }

    // Get user notifications
    public function getUserNotifications($userId, $limit = 10, $offset = 0, $unreadOnly = false) {
        $sql = 'SELECT * FROM notifications WHERE user_id = :user_id';
        
        if ($unreadOnly) {
            $sql .= ' AND is_read = 0';
        }
        
        $sql .= ' ORDER BY created_at DESC LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Mark notification as read
    public function markAsRead($id) {
        $this->db->query('UPDATE notifications SET is_read = 1 WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    // Mark all user notifications as read
    public function markAllAsRead($userId) {
        $this->db->query('UPDATE notifications SET is_read = 1 WHERE user_id = :user_id');
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }

    // Get unread notification count for a user
    public function getUnreadCount($userId) {
        $this->db->query('SELECT COUNT(*) as count FROM notifications 
                         WHERE user_id = :user_id AND is_read = 0');
        $this->db->bind(':user_id', $userId);
        
        $result = $this->db->single();
        return $result ? (int)$result->count : 0;
    }

    // Delete notification
    public function delete($id) {
        $this->db->query('DELETE FROM notifications WHERE id = :id');
        $this->db->bind(':id', $id);
        
        return $this->db->execute();
    }

    // Delete old notifications (older than 30 days)
    public function cleanupOldNotifications($days = 30) {
        $this->db->query('DELETE FROM notifications 
                         WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)');
        $this->db->bind(':days', (int)$days, PDO::PARAM_INT);
        
        return $this->db->execute();
    }

    // Get notification data as array
    public function getNotificationData() {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'title' => $this->title,
            'message' => $this->message,
            'type' => $this->type,
            'is_read' => $this->isRead,
            'created_at' => $this->createdAt,
            'link' => $this->link,
            'time_ago' => $this->getTimeAgo($this->createdAt)
        ];
    }

    // Helper function to get time ago string
    private function getTimeAgo($datetime) {
        $time = strtotime($datetime);
        $timeDiff = time() - $time;
        
        if ($timeDiff < 60) {
            return 'Just now';
        }
        
        $intervals = [
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        ];
        
        foreach ($intervals as $seconds => $label) {
            $interval = floor($timeDiff / $seconds);
            
            if ($interval >= 1) {
                return $interval . ' ' . $label . ($interval === 1 ? '' : 's') . ' ago';
            }
        }
        
        return 'Just now';
    }
}
