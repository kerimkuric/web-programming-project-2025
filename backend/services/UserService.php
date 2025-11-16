<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDao.php';

class UserService extends BaseService {
    public function __construct() {
        $dao = new UserDao();
        parent::__construct($dao);
    }

    // Business logic: Create user with validation
    public function createUser($data) {
        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        // Check if email already exists
        $existingUsers = $this->getAll();
        foreach ($existingUsers as $user) {
            if ($user['email'] === $data['email']) {
                throw new Exception('Email already exists.');
            }
        }

        // Validate phone (basic check)
        if (empty($data['phone']) || strlen($data['phone']) < 10) {
            throw new Exception('Phone number must be at least 10 characters.');
        }

        // Validate name
        if (empty($data['name']) || strlen(trim($data['name'])) < 2) {
            throw new Exception('Name must be at least 2 characters.');
        }

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        } elseif (empty($data['password_hash'])) {
            throw new Exception('Password is required.');
        }

        // Set default is_admin if not provided
        if (!isset($data['is_admin'])) {
            $data['is_admin'] = 0;
        }

        return $this->create($data);
    }

    // Business logic: Update user with validation
    public function updateUser($id, $data) {
        // Validate email format if provided
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format.');
        }

        // Check if email already exists (excluding current user)
        if (isset($data['email'])) {
            $existingUsers = $this->getAll();
            foreach ($existingUsers as $user) {
                if ($user['email'] === $data['email'] && $user['id'] != $id) {
                    throw new Exception('Email already exists.');
                }
            }
        }

        // Hash password if provided
        if (isset($data['password']) && !empty($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
            unset($data['password']);
        }

        return $this->update($id, $data);
    }

    // Business logic: Get user by email
    public function getByEmail($email) {
        $users = $this->getAll();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }
}

?>

