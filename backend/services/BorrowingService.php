<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BorrowingDao.php';
require_once __DIR__ . '/../dao/BookDao.php';
require_once __DIR__ . '/../dao/UserDao.php';

class BorrowingService extends BaseService {
    private $bookDao;
    private $userDao;

    public function __construct() {
        $dao = new BorrowingDao();
        parent::__construct($dao);
        $this->bookDao = new BookDao();
        $this->userDao = new UserDao();
    }

    // Business logic: Create borrowing with validation
    public function createBorrowing($data) {
        // Validate user exists
        if (empty($data['user_id'])) {
            throw new Exception('User ID is required.');
        }
        $user = $this->userDao->getById($data['user_id']);
        if (!$user) {
            throw new Exception('User not found.');
        }

        // Validate book exists
        if (empty($data['book_id'])) {
            throw new Exception('Book ID is required.');
        }
        $book = $this->bookDao->getById($data['book_id']);
        if (!$book) {
            throw new Exception('Book not found.');
        }

        // Check if book is already borrowed (not returned)
        $existingBorrowings = $this->getAll();
        foreach ($existingBorrowings as $borrowing) {
            if ($borrowing['book_id'] == $data['book_id'] && empty($borrowing['return_date'])) {
                throw new Exception('Book is already borrowed and not yet returned.');
            }
        }

        // Validate borrow_date
        if (empty($data['borrow_date'])) {
            throw new Exception('Borrow date is required.');
        }
        $borrowDate = strtotime($data['borrow_date']);
        if ($borrowDate === false) {
            throw new Exception('Invalid borrow date format.');
        }

        // Set default borrow_date to today if not provided
        if (!isset($data['borrow_date'])) {
            $data['borrow_date'] = date('Y-m-d');
        }

        // Don't allow return_date on creation (must be set via update)
        if (isset($data['return_date'])) {
            unset($data['return_date']);
        }

        return $this->create($data);
    }

    // Business logic: Update borrowing with validation
    public function updateBorrowing($id, $data) {
        // Validate user exists if provided
        if (isset($data['user_id'])) {
            $user = $this->userDao->getById($data['user_id']);
            if (!$user) {
                throw new Exception('User not found.');
            }
        }

        // Validate book exists if provided
        if (isset($data['book_id'])) {
            $book = $this->bookDao->getById($data['book_id']);
            if (!$book) {
                throw new Exception('Book not found.');
            }

            // Check if book is already borrowed (excluding current borrowing)
            if (isset($data['book_id'])) {
                $existingBorrowings = $this->getAll();
                foreach ($existingBorrowings as $borrowing) {
                    if ($borrowing['book_id'] == $data['book_id'] 
                        && empty($borrowing['return_date']) 
                        && $borrowing['id'] != $id) {
                        throw new Exception('Book is already borrowed and not yet returned.');
                    }
                }
            }
        }

        // Validate dates if provided
        if (isset($data['borrow_date'])) {
            $borrowDate = strtotime($data['borrow_date']);
            if ($borrowDate === false) {
                throw new Exception('Invalid borrow date format.');
            }
        }

        if (isset($data['return_date'])) {
            $returnDate = strtotime($data['return_date']);
            if ($returnDate === false) {
                throw new Exception('Invalid return date format.');
            }

            // Get current borrowing to check borrow_date
            $currentBorrowing = $this->getById($id);
            if ($currentBorrowing) {
                $borrowDate = strtotime($currentBorrowing['borrow_date']);
                if ($returnDate < $borrowDate) {
                    throw new Exception('Return date cannot be before borrow date.');
                }
            }
        }

        return $this->update($id, $data);
    }

    // Business logic: Return a book (set return_date)
    public function returnBook($borrowingId) {
        $borrowing = $this->getById($borrowingId);
        if (!$borrowing) {
            throw new Exception('Borrowing not found.');
        }

        if (!empty($borrowing['return_date'])) {
            throw new Exception('Book has already been returned.');
        }

        return $this->update($borrowingId, ['return_date' => date('Y-m-d')]);
    }

    // Business logic: Get borrowings by user
    public function getByUserId($userId) {
        $borrowings = $this->getAll();
        return array_filter($borrowings, function($borrowing) use ($userId) {
            return $borrowing['user_id'] == $userId;
        });
    }

    // Business logic: Get borrowings by book
    public function getByBookId($bookId) {
        $borrowings = $this->getAll();
        return array_filter($borrowings, function($borrowing) use ($bookId) {
            return $borrowing['book_id'] == $bookId;
        });
    }

    // Business logic: Get active borrowings (not returned)
    public function getActiveBorrowings() {
        $borrowings = $this->getAll();
        return array_filter($borrowings, function($borrowing) {
            return empty($borrowing['return_date']);
        });
    }
}

?>

