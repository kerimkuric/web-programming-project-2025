<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/GenreDao.php';

class GenreService extends BaseService {
    public function __construct() {
        $dao = new GenreDao();
        parent::__construct($dao);
    }

    // Business logic: Create genre with validation
    public function createGenre($data) {
        // Validate name
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            throw new Exception('Genre name must be at least 2 characters.');
        }

        // Check if genre name already exists (case-insensitive)
        $existingGenres = $this->getAll();
        foreach ($existingGenres as $genre) {
            if (strtolower($genre['name']) === strtolower(trim($data['name']))) {
                throw new Exception('Genre name already exists.');
            }
        }

        // Trim and capitalize first letter
        $data['name'] = ucfirst(trim($data['name']));

        return $this->create($data);
    }

    // Business logic: Update genre with validation
    public function updateGenre($id, $data) {
        // Validate name if provided
        if (isset($data['name'])) {
            if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
                throw new Exception('Genre name must be at least 2 characters.');
            }

            // Check if genre name already exists (excluding current genre)
            $existingGenres = $this->getAll();
            foreach ($existingGenres as $genre) {
                if (strtolower($genre['name']) === strtolower(trim($data['name'])) && $genre['id'] != $id) {
                    throw new Exception('Genre name already exists.');
                }
            }

            // Trim and capitalize first letter
            $data['name'] = ucfirst(trim($data['name']));
        }

        return $this->update($id, $data);
    }
}

?>

