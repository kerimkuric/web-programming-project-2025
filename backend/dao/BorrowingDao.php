<?php

require_once __DIR__ . '/BaseDao.php';

class BorrowingDao extends BaseDao {
    public function __construct() {
        parent::__construct('borrowings');
    }

    // Return borrowings joined with book title and user name
    public function getAllWithDetails() {
        $sql = "SELECT b.*, bo.title AS book_title, u.name AS user_name
                FROM borrowings b
                JOIN books bo ON b.book_id = bo.id
                JOIN users u ON b.user_id = u.id
                ORDER BY b.borrow_date DESC";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    public function getByIdWithDetails($id) {
        $sql = "SELECT b.*, bo.title AS book_title, u.name AS user_name
                FROM borrowings b
                JOIN books bo ON b.book_id = bo.id
                JOIN users u ON b.user_id = u.id
                WHERE b.id = :id";
        $statement = $this->connection->prepare($sql);
        $statement->execute([':id' => $id]);
        return $statement->fetch();
    }

    public function getByUserIdWithDetails($userId) {
        $sql = "SELECT b.*, bo.title AS book_title, u.name AS user_name
                FROM borrowings b
                JOIN books bo ON b.book_id = bo.id
                JOIN users u ON b.user_id = u.id
                WHERE b.user_id = :user_id
                ORDER BY b.borrow_date DESC";
        $statement = $this->connection->prepare($sql);
        $statement->execute([':user_id' => $userId]);
        return $statement->fetchAll();
    }

    public function getByBookIdWithDetails($bookId) {
        $sql = "SELECT b.*, bo.title AS book_title, u.name AS user_name
                FROM borrowings b
                JOIN books bo ON b.book_id = bo.id
                JOIN users u ON b.user_id = u.id
                WHERE b.book_id = :book_id
                ORDER BY b.borrow_date DESC";
        $statement = $this->connection->prepare($sql);
        $statement->execute([':book_id' => $bookId]);
        return $statement->fetchAll();
    }

    public function getActiveWithDetails() {
        $sql = "SELECT b.*, bo.title AS book_title, u.name AS user_name
                FROM borrowings b
                JOIN books bo ON b.book_id = bo.id
                JOIN users u ON b.user_id = u.id
                WHERE b.return_date IS NULL
                ORDER BY b.borrow_date DESC";
        $statement = $this->connection->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }
}

?>

