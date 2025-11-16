<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/BookDao.php';
require_once __DIR__ . '/../dao/AuthorDao.php';
require_once __DIR__ . '/../dao/GenreDao.php';

class BookService extends BaseService {
    private $authorDao;
    private $genreDao;

    public function __construct() {
        $dao = new BookDao();
        parent::__construct($dao);
        $this->authorDao = new AuthorDao();
        $this->genreDao = new GenreDao();
    }

    // Business logic: Create book with validation
    public function createBook($data) {
        // Validate title
        if (empty($data['title']) || strlen(trim($data['title'])) < 1) {
            throw new Exception('Book title is required.');
        }

        // Validate and check author exists
        if (empty($data['author_id'])) {
            throw new Exception('Author ID is required.');
        }
        $author = $this->authorDao->getById($data['author_id']);
        if (!$author) {
            throw new Exception('Author not found.');
        }

        // Validate and check genre exists
        if (empty($data['genre_id'])) {
            throw new Exception('Genre ID is required.');
        }
        $genre = $this->genreDao->getById($data['genre_id']);
        if (!$genre) {
            throw new Exception('Genre not found.');
        }

        // Validate year (must be reasonable)
        if (empty($data['year']) || !is_numeric($data['year'])) {
            throw new Exception('Year must be a valid number.');
        }
        $year = (int)$data['year'];
        if ($year < 0 || $year > date('Y') + 1) {
            throw new Exception('Year must be between 0 and ' . (date('Y') + 1) . '.');
        }

        // Validate ISBN format (10 or 13 characters, alphanumeric)
        if (empty($data['isbn'])) {
            throw new Exception('ISBN is required.');
        }
        $isbn = preg_replace('/[^0-9X]/', '', $data['isbn']); // Remove hyphens and spaces
        if (strlen($isbn) !== 10 && strlen($isbn) !== 13) {
            throw new Exception('ISBN must be 10 or 13 digits.');
        }

        // Check if ISBN already exists
        $existingBooks = $this->getAll();
        foreach ($existingBooks as $book) {
            if ($book['isbn'] === $data['isbn']) {
                throw new Exception('ISBN already exists.');
            }
        }

        return $this->create($data);
    }

    // Business logic: Update book with validation
    public function updateBook($id, $data) {
        // Validate author exists if provided
        if (isset($data['author_id'])) {
            $author = $this->authorDao->getById($data['author_id']);
            if (!$author) {
                throw new Exception('Author not found.');
            }
        }

        // Validate genre exists if provided
        if (isset($data['genre_id'])) {
            $genre = $this->genreDao->getById($data['genre_id']);
            if (!$genre) {
                throw new Exception('Genre not found.');
            }
        }

        // Validate year if provided
        if (isset($data['year'])) {
            if (!is_numeric($data['year'])) {
                throw new Exception('Year must be a valid number.');
            }
            $year = (int)$data['year'];
            if ($year < 0 || $year > date('Y') + 1) {
                throw new Exception('Year must be between 0 and ' . (date('Y') + 1) . '.');
            }
        }

        // Validate ISBN if provided
        if (isset($data['isbn'])) {
            $isbn = preg_replace('/[^0-9X]/', '', $data['isbn']);
            if (strlen($isbn) !== 10 && strlen($isbn) !== 13) {
                throw new Exception('ISBN must be 10 or 13 digits.');
            }

            // Check if ISBN already exists (excluding current book)
            $existingBooks = $this->getAll();
            foreach ($existingBooks as $book) {
                if ($book['isbn'] === $data['isbn'] && $book['id'] != $id) {
                    throw new Exception('ISBN already exists.');
                }
            }
        }

        return $this->update($id, $data);
    }

    // Business logic: Get books by author
    public function getByAuthor($authorId) {
        $books = $this->getAll();
        return array_filter($books, function($book) use ($authorId) {
            return $book['author_id'] == $authorId;
        });
    }

    // Business logic: Get books by genre
    public function getByGenre($genreId) {
        $books = $this->getAll();
        return array_filter($books, function($book) use ($genreId) {
            return $book['genre_id'] == $genreId;
        });
    }
}

?>

