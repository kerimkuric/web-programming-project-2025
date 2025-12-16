<?php

require_once 'BaseService.php';
require_once __DIR__ . '/../dao/AuthDao.php';
require_once __DIR__ . '/../config/Config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthService extends BaseService {
    private $auth_dao;

    public function __construct() {
        $this->auth_dao = new AuthDao();
        parent::__construct(new AuthDao);
    }

    public function get_user_by_email($email){
        return $this->auth_dao->get_user_by_email($email);
    }

    public function register($entity) {  
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $email_exists = $this->auth_dao->get_user_by_email($entity['email']);
        if($email_exists){
            return ['success' => false, 'error' => 'Email already registered.'];
        }

        // Map password to password_hash for database
        if (isset($entity['password'])) {
            $entity['password_hash'] = password_hash($entity['password'], PASSWORD_BCRYPT);
            unset($entity['password']);
        }

        // Map is_admin to database field (if provided)
        if (isset($entity['is_admin'])) {
            // Keep as is
        } else {
            $entity['is_admin'] = 0; // Default to regular user
        }

        // Add required fields if missing
        if (!isset($entity['name'])) {
            $entity['name'] = '';
        }
        if (!isset($entity['phone'])) {
            $entity['phone'] = '';
        }

        $entity = parent::add($entity);
        $user = $this->auth_dao->getById($entity);
        unset($user['password_hash']);
        
        return ['success' => true, 'data' => $user];             
    }

    public function login($entity) {  
        if (empty($entity['email']) || empty($entity['password'])) {
            return ['success' => false, 'error' => 'Email and password are required.'];
        }

        $user = $this->auth_dao->get_user_by_email($entity['email']);
        if(!$user){
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        if(!password_verify($entity['password'], $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid username or password.'];
        }

        unset($user['password_hash']);

        // Determine role based on is_admin field
        $role = ($user['is_admin'] == 1 || $user['is_admin'] === true) ? 'admin' : 'user';
       
        $jwt_payload = [
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $role
            ],
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // valid for day
        ];

        $token = JWT::encode(
            $jwt_payload,
            Config::JWT_SECRET(),
            'HS256'
        );

        return ['success' => true, 'data' => array_merge($user, ['token' => $token, 'role' => $role])];             
    }
}

?>

