<?php

class Category {
    private $db;
    private $id;
    private $name;
    private $description;
    private $createdAt;
    private $updatedAt;
    private $type; // 'book' or 'asset'

    public function __construct($type = 'book') {
        $this->db = Database::getInstance();
        $this->type = $type;
    }

    // Get all categories
    public function getAllCategories($filters = []) {
        $sql = 'SELECT * FROM ' . $this->getTableName() . ' WHERE 1=1';
        $params = [];
        
        // Apply filters
        if (!empty($filters['search'])) {
            $sql .= ' AND (name LIKE :search OR description LIKE :search)';
            $params[':search'] = "%{$filters['search']}%";
        }
        
        // Add sorting
        $sort = $filters['sort'] ?? 'name';
        $order = $filters['order'] ?? 'ASC';
        $sql .= " ORDER BY $sort $order";
        
        // Add pagination if needed
        if (isset($filters['limit'])) {
            $sql .= ' LIMIT :limit';
            if (isset($filters['offset'])) {
                $sql .= ' OFFSET :offset';
            }
        }
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        // Bind limit and offset if set
        if (isset($filters['limit'])) {
            $this->db->bind(':limit', (int)$filters['limit'], PDO::PARAM_INT);
            if (isset($filters['offset'])) {
                $this->db->bind(':offset', (int)$filters['offset'], PDO::PARAM_INT);
            }
        }
        
        return $this->db->resultSet();
    }

    // Get category by ID
    public function getCategory($id) {
        $this->db->query('SELECT * FROM ' . $this->getTableName() . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if ($row) {
            $this->id = $row->id;
            $this->name = $row->name;
            $this->description = $row->description;
            $this->createdAt = $row->created_at;
            $this->updatedAt = $row->updated_at;
            
            return $this->getCategoryData();
        }
        
        return false;
    }

    // Add a new category
    public function addCategory($data) {
        // Check if category with same name already exists
        $this->db->query('SELECT id FROM ' . $this->getTableName() . ' WHERE name = :name');
        $this->db->bind(':name', $data['name']);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'A category with this name already exists'
            ];
        }
        
        // Insert new category
        $this->db->query('INSERT INTO ' . $this->getTableName() . ' 
                         (name, description) 
                         VALUES (:name, :description)');
        
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description'] ?? null);
        
        if ($this->db->execute()) {
            return [
                'success' => true,
                'category_id' => $this->db->lastInsertId()
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to add category'
        ];
    }

    // Update a category
    public function updateCategory($id, $data) {
        // Check if another category with the same name exists
        $this->db->query('SELECT id FROM ' . $this->getTableName() . ' 
                         WHERE name = :name AND id != :id');
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':id', $id);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return [
                'success' => false,
                'message' => 'Another category with this name already exists'
            ];
        }
        
        // Update category
        $this->db->query('UPDATE ' . $this->getTableName() . ' 
                         SET name = :name, 
                             description = :description,
                             updated_at = CURRENT_TIMESTAMP
                         WHERE id = :id');
        
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $data['name']);
        $this->db->bind(':description', $data['description'] ?? null);
        
        if ($this->db->execute()) {
            return [
                'success' => true,
                'category_id' => $id
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to update category'
        ];
    }

    // Delete a category
    public function deleteCategory($id) {
        // Check if category is in use
        $table = $this->type === 'book' ? 'books' : 'digital_assets';
        $this->db->query('SELECT COUNT(*) as count FROM ' . $table . ' WHERE category_id = :category_id');
        $this->db->bind(':category_id', $id);
        $result = $this->db->single();
        
        if ($result && $result->count > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete category as it is in use by ' . 
                            ($result->count == 1 ? '1 item' : $result->count . ' items')
            ];
        }
        
        // Delete category
        $this->db->query('DELETE FROM ' . $this->getTableName() . ' WHERE id = :id');
        $this->db->bind(':id', $id);
        
        if ($this->db->execute()) {
            return [
                'success' => true,
                'message' => 'Category deleted successfully'
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Failed to delete category'
        ];
    }

    // Get category data as array
    public function getCategoryData() {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'type' => $this->type
        ];
    }

    // Get the appropriate table name based on category type
    private function getTableName() {
        return $this->type === 'book' ? 'book_categories' : 'asset_categories';
    }
}
