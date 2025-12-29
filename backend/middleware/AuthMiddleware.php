<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

require_once __DIR__ . '/../config/Config.php';

class AuthMiddleware {

    public function verifyToken($token){
        if(!$token)
            Flight::halt(401, "Missing authentication header");
        
        //decoding jwt token
        try {
            $decoded_token = JWT::decode($token, new Key(Config::JWT_SECRET(), 'HS256'));
            Flight::set('user', $decoded_token->user);
            Flight::set('jwt_token', $token);
            return TRUE;
        } catch (\Exception $e) {
            Flight::halt(401, $e->getMessage());
        }
    }

    public function authorizeRole($requiredRole) {
        $user = Flight::get('user');
        if (!$user) {
            Flight::halt(401, 'Authentication required');
        }
        if ($user->role !== $requiredRole) {
            Flight::halt(403, 'Access denied: insufficient privileges');
        }
    }

    public function authorizeRoles($roles) {
        $user = Flight::get('user');
        if (!$user) {
            Flight::halt(401, 'Authentication required');
        }
        if (!in_array($user->role, $roles)) {
            Flight::halt(403, 'Forbidden: role not allowed');
        }
    }

    function authorizePermission($permission) {
        $user = Flight::get('user');
        if (!$user) {
            Flight::halt(401, 'Authentication required');
        }
        if (!isset($user->permissions) || !in_array($permission, $user->permissions)) {
            Flight::halt(403, 'Access denied: permission missing');
        }
    }   

    
    // Static helper compatibility methods for routes that call AuthMiddleware::authenticate() or getCurrentUser()
    public static function authenticate() {
        // If Flight already has a user from middleware, accept it
        $user = Flight::get('user');
        if ($user) return true;

        // Try to read Authorization header from multiple server vars and request headers
        $token = null;
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $token = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['HTTP_X_AUTHORIZATION'])) {
            $token = $_SERVER['HTTP_X_AUTHORIZATION'];
        } elseif (isset($_SERVER['HTTP_AUTH'])) {
            $token = $_SERVER['HTTP_AUTH'];
        } elseif (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) $token = $headers['Authorization'];
            if (isset($headers['authorization'])) $token = $headers['authorization'];
            if (isset($headers['X-Authorization'])) $token = $headers['X-Authorization'];
            if (isset($headers['x-authorization'])) $token = $headers['x-authorization'];
        }

        // Also allow token via query param as a dev fallback: ?token=...
        if (!$token && isset($_GET['token'])) {
            $token = $_GET['token'];
        } elseif (!$token && isset($_REQUEST['token'])) {
            $token = $_REQUEST['token'];
        }

        if ($token && strpos($token, 'Bearer ') === 0) {
            $token = substr($token, 7);
        }

        if (!$token) {
            Flight::json(['success' => false, 'message' => 'Authentication required'], 401);
            return false;
        }

        try {
            $inst = new self();
            $inst->verifyToken($token);
            return true;
        } catch (Exception $e) {
            Flight::json(['success' => false, 'message' => $e->getMessage()], 401);
            return false;
        }
    }

    public static function getCurrentUser() {
        $user = Flight::get('user');
        if (!$user) return null;

        // $user may be object from JWT or array from DB; normalize to associative array
        if (is_object($user)) {
            $arr = (array) $user;
        } else {
            $arr = (array) $user;
        }

        // Normalize keys expected by routes
        $normalized = [];
        $normalized['user_id'] = isset($arr['id']) ? $arr['id'] : (isset($arr['user_id']) ? $arr['user_id'] : null);
        $normalized['email'] = isset($arr['email']) ? $arr['email'] : null;
        $normalized['name'] = isset($arr['name']) ? $arr['name'] : null;
        // Determine is_admin from role if present
        if (isset($arr['role'])) {
            $normalized['is_admin'] = ($arr['role'] === 'admin') ? 1 : 0;
            $normalized['role'] = $arr['role'];
        } else {
            // fallback if permission flag available
            $normalized['is_admin'] = isset($arr['is_admin']) ? (int)$arr['is_admin'] : 0;
        }

        return $normalized;
    }

}

?>
