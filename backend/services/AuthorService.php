<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/AuthorDao.php';

class AuthorService extends BaseService {
    public function __construct() {
        $dao = new AuthorDao();
        parent::__construct($dao);
    }

    // Business logic: Create author with validation
    public function createAuthor($data) {
        // Validate name
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            throw new Exception('Author name must be at least 2 characters.');
        }

        // Validate country
        if (empty($data['country']) || strlen(trim($data['country'])) < 2) {
            throw new Exception('Country must be at least 2 characters.');
        }

        return $this->create($data);
    }

    // Business logic: Update author with validation
    public function updateAuthor($id, $data) {
        // Validate name if provided
        if (isset($data['name']) && (empty($data['name']) || strlen(trim($data['name'])) < 2)) {
            throw new Exception('Author name must be at least 2 characters.');
        }

        // Validate country if provided
        if (isset($data['country']) && (empty($data['country']) || strlen(trim($data['country'])) < 2)) {
            throw new Exception('Country must be at least 2 characters.');
        }

        return $this->update($id, $data);
    }

    // Business logic: Get authors by country
    public function getByCountry($country) {
        $authors = $this->getAll();
        return array_filter($authors, function($author) use ($country) {
            return strtolower($author['country']) === strtolower($country);
        });
    }
}

?>

