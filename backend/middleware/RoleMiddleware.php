<?php

/**
 * Role-based Authorization Middleware
 * Checks if user has required role/permissions
 */
class RoleMiddleware {
    
    /**
     * Require admin role
     */
    public static function requireAdmin() {
        $user = AuthMiddleware::getCurrentUser();
        
        if (!$user) {
            Flight::json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return false;
        }
        
        if (!$user['is_admin']) {
            Flight::json([
                'success' => false,
                'message' => 'Admin access required'
            ], 403);
            return false;
        }
        
        return true;
    }
    
    /**
     * Require authentication (any user)
     */
    public static function requireAuth() {
        $user = AuthMiddleware::getCurrentUser();
        
        if (!$user) {
            Flight::json([
                'success' => false,
                'message' => 'Authentication required'
            ], 401);
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if current user can access resource (admin or own resource)
     */
    public static function canAccessResource($resourceUserId) {
        $user = AuthMiddleware::getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        // Admins can access any resource
        if ($user['is_admin']) {
            return true;
        }
        
        // Users can only access their own resources
        return $user['user_id'] == $resourceUserId;
    }
}

?>

