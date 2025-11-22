<?php
class DigitalAsset {
    private $db;
    private $id;
    private $title;
    private $description;
    private $filePath;
    private $fileType;
    private $fileSize;
    private $categoryId;
    private $isPublic;
    private $uploadedBy;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Upload a new digital asset
    public function uploadAsset($file, $data, $userId) {
        // Validate file
        $allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($fileExt, $allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = 'uploads/digital_assets/' . date('Y/m/');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // Generate unique filename
        $fileName = uniqid() . '.' . $fileExt;
        $targetPath = $uploadDir . $fileName;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'message' => 'Failed to upload file'];
        }
        
        // Insert record
        $this->db->query('INSERT INTO digital_assets 
                         (title, description, file_path, file_type, file_size, 
                          category_id, is_public, uploaded_by) 
                         VALUES (:title, :description, :file_path, :file_type, 
                                :file_size, :category_id, :is_public, :uploaded_by)');
        
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':file_path', $targetPath);
        $this->db->bind(':file_type', $fileExt);
        $this->db->bind(':file_size', $file['size']);
        $this->db->bind(':category_id', $data['category_id'] ?? null);
        $this->db->bind(':is_public', $data['is_public'] ?? 1);
        $this->db->bind(':uploaded_by', $userId);
        
        if ($this->db->execute()) {
            return [
                'success' => true,
                'asset_id' => $this->db->lastInsertId(),
                'file_path' => $targetPath
            ];
        }
        
        return ['success' => false, 'message' => 'Failed to save asset record'];
    }

    // Get asset by ID
    public function getAsset($id, $checkPublic = true) {
        $sql = 'SELECT da.*, u.username as uploaded_by_name, c.name as category_name 
               FROM digital_assets da 
               LEFT JOIN users u ON da.uploaded_by = u.id 
               LEFT JOIN categories c ON da.category_id = c.id 
               WHERE da.id = :id';
        
        if ($checkPublic) {
            $sql .= ' AND (da.is_public = 1 OR da.uploaded_by = :user_id)';
        }
        
        $this->db->query($sql);
        $this->db->bind(':id', $id);
        
        if ($checkPublic) {
            $this->db->bind(':user_id', $_SESSION['user_id'] ?? 0);
        }
        
        return $this->db->single();
    }

    // Get all assets with filters
    public function getAssets($filters = [], $limit = 10, $offset = 0) {
        $sql = 'SELECT da.*, u.username as uploaded_by_name, c.name as category_name 
               FROM digital_assets da 
               LEFT JOIN users u ON da.uploaded_by = u.id 
               LEFT JOIN categories c ON da.category_id = c.id 
               WHERE 1=1';
        
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= ' AND (da.title LIKE :search OR da.description LIKE :search)';
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= ' AND da.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (isset($filters['is_public'])) {
            $sql .= ' AND da.is_public = :is_public';
            $params[':is_public'] = $filters['is_public'] ? 1 : 0;
        }
        
        // For non-admin users, only show public assets or their own
        if (empty($filters['admin_view'])) {
            $sql .= ' AND (da.is_public = 1 OR da.uploaded_by = :user_id)';
            $params[':user_id'] = $_SESSION['user_id'] ?? 0;
        }
        
        // Add sorting
        $sort = $filters['sort'] ?? 'da.created_at';
        $order = $filters['order'] ?? 'DESC';
        $sql .= " ORDER BY $sort $order";
        
        // Add pagination
        $sql .= ' LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Update asset
    public function updateAsset($id, $data) {
        $sql = 'UPDATE digital_assets SET 
                   title = :title,
                   description = :description,';
        
        // Only update file info if a new file is provided
        if (!empty($data['file_path'])) {
            $sql .= ' file_path = :file_path,';
            $sql .= ' file_type = :file_type,';
            $sql .= ' file_size = :file_size,';
        }
        
        $sql .= ' category_id = :category_id,
                is_public = :is_public
                WHERE id = :id';
        
        $this->db->query($sql);
        
        // Bind parameters
        $this->db->bind(':id', $id);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':category_id', $data['category_id'] ?? null);
        $this->db->bind(':is_public', $data['is_public'] ?? 1);
        
        // Bind file parameters if updating file
        if (!empty($data['file_path'])) {
            $this->db->bind(':file_path', $data['file_path']);
            $this->db->bind(':file_type', $data['file_type']);
            $this->db->bind(':file_size', $data['file_size']);
        }
        
        return $this->db->execute();
    }

    // Delete asset
    public function deleteAsset($id) {
        // First get the file path to delete the actual file
        $asset = $this->getAsset($id, false);
        
        if (!$asset) {
            return false;
        }
        
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Delete the database record
            $this->db->query('DELETE FROM digital_assets WHERE id = :id');
            $this->db->bind(':id', $id);
            $result = $this->db->execute();
            
            if ($result) {
                // Delete the actual file
                if (file_exists($asset->file_path)) {
                    unlink($asset->file_path);
                }
                
                $this->db->commit();
                return true;
            }
            
            $this->db->rollBack();
            return false;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Error deleting asset: ' . $e->getMessage());
            return false;
        }
    }

    // Get asset categories
    public function getCategories() {
        $this->db->query('SELECT * FROM categories WHERE type = "digital_asset" ORDER BY name');
        return $this->db->resultSet();
    }
    
    /**
     * Count total assets with optional filters
     * 
     * @param array $filters Array of filters
     * @return int Total count
     */
    public function countAssets($filters = []) {
        $sql = 'SELECT COUNT(*) as total FROM digital_assets da WHERE 1=1';
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= ' AND (da.title LIKE :search OR da.description LIKE :search)';
            $params[':search'] = "%{$filters['search']}%";
        }
        
        if (!empty($filters['category_id'])) {
            $sql .= ' AND da.category_id = :category_id';
            $params[':category_id'] = $filters['category_id'];
        }
        
        if (isset($filters['is_public'])) {
            $sql .= ' AND da.is_public = :is_public';
            $params[':is_public'] = $filters['is_public'] ? 1 : 0;
        }
        
        // For non-admin users, only count public assets or their own
        if (empty($filters['admin_view'])) {
            $sql .= ' AND (da.is_public = 1 OR da.uploaded_by = :user_id)';
            $params[':user_id'] = $_SESSION['user_id'] ?? 0;
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        $result = $this->db->single();
        return $result ? (int)$result->total : 0;
    }
    
    /**
     * Record a download in the database
     * 
     * @param int $assetId Asset ID
     * @param int $userId User ID who downloaded
     * @return bool True on success, false on failure
     */
    public function recordDownload($assetId, $userId) {
        $this->db->query('INSERT INTO asset_downloads (asset_id, user_id, downloaded_at) 
                         VALUES (:asset_id, :user_id, NOW())');
        
        $this->db->bind(':asset_id', $assetId);
        $this->db->bind(':user_id', $userId);
        
        return $this->db->execute();
    }
    
    /**
     * Get download history for an asset
     * 
     * @param int $assetId Asset ID
     * @param int $limit Number of records to return
     * @return array Array of download records
     */
    public function getDownloadHistory($assetId, $limit = 10) {
        $this->db->query('SELECT ad.*, u.username, u.email 
                         FROM asset_downloads ad
                         JOIN users u ON ad.user_id = u.id
                         WHERE ad.asset_id = :asset_id
                         ORDER BY ad.downloaded_at DESC
                         LIMIT :limit');
        
        $this->db->bind(':asset_id', $assetId);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Get asset statistics
    public function getStats() {
        $stats = [];
        
        // Total assets
        $this->db->query('SELECT COUNT(*) as total FROM digital_assets');
        $result = $this->db->single();
        $stats['total_assets'] = $result->total;
        
        // Public assets
        $this->db->query('SELECT COUNT(*) as public_count FROM digital_assets WHERE is_public = 1');
        $result = $this->db->single();
        $stats['public_assets'] = $result->public_count;
        
        // Private assets
        $this->db->query('SELECT COUNT(*) as private_count FROM digital_assets WHERE is_public = 0');
        $result = $this->db->single();
        $stats['private_assets'] = $result->private_count;
        
        // Total file size
        $this->db->query('SELECT COALESCE(SUM(file_size), 0) as total_size FROM digital_assets');
        $result = $this->db->single();
        $stats['total_size'] = $result->total_size;
        
        return $stats;
    }
}
