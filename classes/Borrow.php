<?php
class Borrow {
    private $db;
    private $id;
    private $bookId;
    private $memberId;
    private $borrowedDate;
    private $dueDate;
    private $returnedDate;
    private $status;
    private $fineAmount;
    private $notes;
    private $createdAt;
    private $updatedAt;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // Borrow a book
    public function borrowBook($bookId, $memberId, $dueDays = 14) {
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Check if book is available
            $book = new Book();
            if (!$book->isAvailable($bookId)) {
                throw new Exception('Book is not available for borrowing');
            }
            
            // Check if member can borrow
            $member = new Member();
            if (!$member->canBorrow($memberId)) {
                throw new Exception('Member cannot borrow more books at this time');
            }
            
            // Calculate dates
            $borrowedDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime("+$dueDays days"));
            
            // Create borrow record
            $this->db->query('INSERT INTO borrows 
                             (book_id, member_id, borrowed_date, due_date, status) 
                             VALUES (:book_id, :member_id, :borrowed_date, :due_date, "borrowed")');
            
            $this->db->bind(':book_id', $bookId);
            $this->db->bind(':member_id', $memberId);
            $this->db->bind(':borrowed_date', $borrowedDate);
            $this->db->bind(':due_date', $dueDate);
            
            if (!$this->db->execute()) {
                throw new Exception('Failed to create borrow record');
            }
            
            // Update book availability
            $this->db->query('UPDATE books SET available = available - 1 WHERE id = :id');
            $this->db->bind(':id', $bookId);
            
            if (!$this->db->execute()) {
                throw new Exception('Failed to update book availability');
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'borrow_id' => $this->db->lastInsertId(),
                'due_date' => $dueDate
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Return a borrowed book
    public function returnBook($borrowId) {
        // Start transaction
        $this->db->beginTransaction();
        
        try {
            // Get borrow record
            $this->db->query('SELECT * FROM borrows WHERE id = :id AND status = "borrowed"');
            $this->db->bind(':id', $borrowId);
            $borrow = $this->db->single();
            
            if (!$borrow) {
                throw new Exception('Borrow record not found or already returned');
            }
            
            // Calculate fine if any
            $fineAmount = 0;
            $returnedDate = date('Y-m-d');
            
            if (strtotime($returnedDate) > strtotime($borrow->due_date)) {
                $daysLate = floor((strtotime($returnedDate) - strtotime($borrow->due_date)) / (60 * 60 * 24));
                $fineAmount = $daysLate * 1.00; // $1 per day late
            }
            
            // Update borrow record
            $this->db->query('UPDATE borrows 
                             SET returned_date = :returned_date, 
                                 status = "returned", 
                                 fine_amount = :fine_amount 
                             WHERE id = :id');
            
            $this->db->bind(':returned_date', $returnedDate);
            $this->db->bind(':fine_amount', $fineAmount);
            $this->db->bind(':id', $borrowId);
            
            if (!$this->db->execute()) {
                throw new Exception('Failed to update borrow record');
            }
            
            // Update book availability
            $this->db->query('UPDATE books SET available = available + 1 WHERE id = :id');
            $this->db->bind(':id', $borrow->book_id);
            
            if (!$this->db->execute()) {
                throw new Exception('Failed to update book availability');
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'fine_amount' => $fineAmount
            ];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Get borrow history for a member
    public function getMemberBorrowHistory($memberId, $limit = 10, $offset = 0) {
        $this->db->query('SELECT b.*, bk.title as book_title, bk.isbn,
                         m.first_name, m.last_name, m.member_id
                         FROM borrows b
                         JOIN books bk ON b.book_id = bk.id
                         JOIN members m ON b.member_id = m.id
                         WHERE b.member_id = :member_id
                         ORDER BY b.borrowed_date DESC
                         LIMIT :limit OFFSET :offset');
        
        $this->db->bind(':member_id', $memberId);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Get all current borrows
    public function getCurrentBorrows($limit = 10, $offset = 0) {
        $this->db->query('SELECT b.*, bk.title as book_title, bk.isbn,
                         m.first_name, m.last_name, m.member_id
                         FROM borrows b
                         JOIN books bk ON b.book_id = bk.id
                         JOIN members m ON b.member_id = m.id
                         WHERE b.status = "borrowed"
                         ORDER BY b.due_date ASC
                         LIMIT :limit OFFSET :offset');
        
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Get overdue books
    public function getOverdueBooks($limit = 10, $offset = 0) {
        $today = date('Y-m-d');
        
        $this->db->query('SELECT b.*, bk.title as book_title, bk.isbn,
                         m.first_name, m.last_name, m.member_id,
                         DATEDIFF(:today, b.due_date) as days_overdue
                         FROM borrows b
                         JOIN books bk ON b.book_id = bk.id
                         JOIN members m ON b.member_id = m.id
                         WHERE b.status = "borrowed" 
                         AND b.due_date < :today
                         ORDER BY b.due_date ASC
                         LIMIT :limit OFFSET :offset');
        
        $this->db->bind(':today', $today);
        $this->db->bind(':limit', (int)$limit, PDO::PARAM_INT);
        $this->db->bind(':offset', (int)$offset, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    // Get borrow statistics
    public function getBorrowStats() {
        $stats = [];
        
        // Total borrows
        $this->db->query('SELECT COUNT(*) as total FROM borrows');
        $result = $this->db->single();
        $stats['total_borrows'] = $result->total;
        
        // Current borrows
        $this->db->query('SELECT COUNT(*) as current FROM borrows WHERE status = "borrowed"');
        $result = $this->db->single();
        $stats['current_borrows'] = $result->current;
        
        // Overdue books
        $today = date('Y-m-d');
        $this->db->query('SELECT COUNT(*) as overdue FROM borrows 
                         WHERE status = "borrowed" AND due_date < :today');
        $this->db->bind(':today', $today);
        $result = $this->db->single();
        $stats['overdue_books'] = $result->overdue;
        
        // Total fines
        $this->db->query('SELECT COALESCE(SUM(fine_amount), 0) as total_fines FROM borrows');
        $result = $this->db->single();
        $stats['total_fines'] = $result->total_fines;
        
        return $stats;
    }
}
