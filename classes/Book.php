<?php
class Book {
    private $db;
    private $id;
    private $isbn;
    private $title;
    private $author;
    private $publisher;
    private $publicationYear;
    private $categoryId;
    private $description;
    private $coverImage;
    private $quantity;
    private $available;
    private $featured;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Get all books with optional filters
    public function getBooks($filters = [], $limit = 10, $offset = 0) {
        $sql = 'SELECT b.*, c.name as category_name 
                FROM books b 
                LEFT JOIN categories c ON b.category_id = c.id 
                WHERE 1=1';
        
        $params = [];
        
        // Add filters
        if (!empty($filters['category'])) {
            $sql .= ' AND b.category_id = :category';
            $params[':category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $sql .= ' AND (b.title LIKE :search OR b.author LIKE :search OR b.isbn = :exact_search)';
            $params[':search'] = '%' . $filters['search'] . '%';
            $params[':exact_search'] = $filters['search'];
        }
        
        if (isset($filters['featured'])) {
            $sql .= ' AND b.featured = :featured';
            $params[':featured'] = $filters['featured'] ? 1 : 0;
        }
        
        // Add sorting
        $sort = $filters['sort'] ?? 'title';
        $order = $filters['order'] ?? 'ASC';
        $sql .= " ORDER BY b.$sort $order";
        
        // Add pagination
        $sql .= ' LIMIT :limit OFFSET :offset';
        
        $this->db->query($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $this->db->bind($key, $value);
        }
        
        // Bind limit and offset as integers
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Get book by ID
    public function getBookById($id) {
        $this->db->query('SELECT b.*, c.name as category_name 
                         FROM books b 
                         LEFT JOIN categories c ON b.category_id = c.id 
                         WHERE b.id = :id');
        $this->db->bind(':id', $id);
        
        $row = $this->db->single();
        
        if ($row) {
            $this->id = $row->id;
            $this->isbn = $row->isbn;
            $this->title = $row->title;
            $this->author = $row->author;
            $this->publisher = $row->publisher;
            $this->publicationYear = $row->publication_year;
            $this->categoryId = $row->category_id;
            $this->description = $row->description;
            $this->coverImage = $row->cover_image;
            $this->quantity = $row->quantity;
            $this->available = $row->available;
            $this->featured = $row->featured;
            $this->createdAt = $row->created_at;
            $this->updatedAt = $row->updated_at;
            
            return $this->getBookData();
        }
        
        return false;
    }

    // Add a new book
    public function addBook($data) {
        // Check if ISBN already exists
        if (!empty($data['isbn'])) {
            $this->db->query('SELECT id FROM books WHERE isbn = :isbn');
            $this->db->bind(':isbn', $data['isbn']);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'A book with this ISBN already exists'];
            }
        }
        
        // Insert book
        $sql = 'INSERT INTO books (isbn, title, author, publisher, publication_year, category_id, 
                                 description, cover_image, quantity, available, featured) 
                VALUES (:isbn, :title, :author, :publisher, :publication_year, :category_id, 
                       :description, :cover_image, :quantity, :available, :featured)';
        
        $this->db->query($sql);
        
        // Bind values
        $this->db->bind(':isbn', $data['isbn'] ?? null);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':author', $data['author']);
        $this->db->bind(':publisher', $data['publisher'] ?? null);
        $this->db->bind(':publication_year', $data['publication_year'] ?? null);
        $this->db->bind(':category_id', $data['category_id'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        $this->db->bind(':cover_image', $data['cover_image'] ?? null);
        $this->db->bind(':quantity', $data['quantity'] ?? 1);
        $this->db->bind(':available', $data['available'] ?? $data['quantity'] ?? 1);
        $this->db->bind(':featured', $data['featured'] ?? 0);
        
        // Execute
        if ($this->db->execute()) {
            return ['success' => true, 'book_id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Failed to add book'];
        }
    }

    // Update a book
    public function updateBook($id, $data) {
        // Check if ISBN already exists for another book
        if (!empty($data['isbn'])) {
            $this->db->query('SELECT id FROM books WHERE isbn = :isbn AND id != :id');
            $this->db->bind(':isbn', $data['isbn']);
            $this->db->bind(':id', $id);
            $this->db->execute();
            
            if ($this->db->rowCount() > 0) {
                return ['success' => false, 'message' => 'A book with this ISBN already exists'];
            }
        }
        
        // Update book
        $sql = 'UPDATE books SET 
                   isbn = :isbn,
                   title = :title,
                   author = :author,
                   publisher = :publisher,
                   publication_year = :publication_year,
                   category_id = :category_id,
                   description = :description,';
        
        // Only update cover image if provided
        if (!empty($data['cover_image'])) {
            $sql .= ' cover_image = :cover_image,';
        }
        
        $sql .= ' quantity = :quantity,
                 available = :available,
                 featured = :featured
                WHERE id = :id';
        
        $this->db->query($sql);
        
        // Bind values
        $this->db->bind(':id', $id);
        $this->db->bind(':isbn', $data['isbn'] ?? null);
        $this->db->bind(':title', $data['title']);
        $this->db->bind(':author', $data['author']);
        $this->db->bind(':publisher', $data['publisher'] ?? null);
        $this->db->bind(':publication_year', $data['publication_year'] ?? null);
        $this->db->bind(':category_id', $data['category_id'] ?? null);
        $this->db->bind(':description', $data['description'] ?? null);
        
        if (!empty($data['cover_image'])) {
            $this->db->bind(':cover_image', $data['cover_image']);
        }
        
        $this->db->bind(':quantity', $data['quantity'] ?? 1);
        $this->db->bind(':available', $data['available'] ?? $data['quantity'] ?? 1);
        $this->db->bind(':featured', $data['featured'] ?? 0);
        
        // Execute
        if ($this->db->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to update book'];
        }
    }

    // Delete a book
    public function deleteBook($id) {
        // First check if the book is currently borrowed
        $this->db->query('SELECT id FROM borrows WHERE book_id = :book_id AND status != "returned"');
        $this->db->bind(':book_id', $id);
        $this->db->execute();
        
        if ($this->db->rowCount() > 0) {
            return ['success' => false, 'message' => 'Cannot delete book that is currently borrowed'];
        }
        
        // Delete book
        $this->db->query('DELETE FROM books WHERE id = :id');
        $this->db->bind(':id', $id);
        
        if ($this->db->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to delete book'];
        }
    }

    // Get book data as array
    public function getBookData() {
        return [
            'id' => $this->id,
            'isbn' => $this->isbn,
            'title' => $this->title,
            'author' => $this->author,
            'publisher' => $this->publisher,
            'publication_year' => $this->publicationYear,
            'category_id' => $this->categoryId,
            'description' => $this->description,
            'cover_image' => $this->coverImage,
            'quantity' => $this->quantity,
            'available' => $this->available,
            'featured' => $this->featured,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt
        ];
    }

    // Get book categories
    public function getCategories() {
        $this->db->query('SELECT * FROM categories ORDER BY name');
        return $this->db->resultSet();
    }

    // Check if a book is available for borrowing
    public function isAvailable($bookId) {
        $this->db->query('SELECT available FROM books WHERE id = :id');
        $this->db->bind(':id', $bookId);
        $row = $this->db->single();
        
        return $row ? $row->available > 0 : false;
    }
}
