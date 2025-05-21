<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once(__DIR__ . '/../../database/config.php');

// Define roles and their permissions
$rolePermissions = [
    'admin' => [
        'dashboard' => true,
        'users' => true,
        'trainers' => true,
        'services' => true,
        'subscriptions' => true,
        'schedule' => true,
        'training_sessions' => true,
        'reviews' => true,
        'statistics' => true,
        'settings' => true
    ],
    'manager' => [
        'dashboard' => true,
        'users' => true,
        'trainers' => true,
        'services' => true,
        'subscriptions' => true,
        'schedule' => true,
        'training_sessions' => false, // No access to training sessions
        'reviews' => true,
        'statistics' => true,
        'settings' => false // No access to settings
    ],
    'trainer' => [
        'dashboard' => false,
        'users' => false,
        'trainers' => false,
        'services' => false,
        'subscriptions' => false,
        'schedule' => false,
        'training_sessions' => true, // Only has access to training sessions
        'reviews' => false,
        'statistics' => false,
        'settings' => false
    ]
];

/**
 * Check if the current user has permission to access the specified resource
 * 
 * @param string $resource The resource to check access for
 * @return bool True if user has access, false otherwise
 */
if (!function_exists('hasPermission')) {
    function hasPermission($resource) {
        global $rolePermissions;
        
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            return false;
        }
        
        $userRole = $_SESSION['user_role'];
        
        // If the role doesn't exist in our permissions array, deny access
        if (!isset($rolePermissions[$userRole])) {
            return false;
        }
        
        // If the resource isn't defined for this role, deny access
        if (!isset($rolePermissions[$userRole][$resource])) {
            return false;
        }
        
        // Return the permission value for this resource
        return $rolePermissions[$userRole][$resource];
    }
}

/**
 * Check if the current user has required role(s)
 * 
 * @param array|string $roles Role or array of roles
 * @return bool True if user has at least one of the required roles
 */
if (!function_exists('hasRole')) {
    function hasRole($roles) {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
            return false;
        }
        
        if (is_array($roles)) {
            return in_array($_SESSION['user_role'], $roles);
        } else {
            return $_SESSION['user_role'] === $roles;
        }
    }
}

/**
 * Get current user's role
 * 
 * @return string|null The user's role or null if not logged in
 */
if (!function_exists('getUserRole')) {
    function getUserRole() {
        return $_SESSION['user_role'] ?? null;
    }
}

/**
 * Get current user data from the database
 * 
 * @return array|null User data or null if not logged in
 */
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        global $pdo;
        if (!isset($_SESSION['user_id'])) {
            return null;
        }
        
        try {
            $stmt = $pdo->prepare("SELECT id, email, first_name, last_name, phone, role FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching current user: " . $e->getMessage());
            return null;
        }
    }
}

/**
 * Check access for the current page
 * If user doesn't have permission, redirect to login or access denied
 * 
 * @param string $resource The resource being accessed
 */
if (!function_exists('checkAccess')) {
    function checkAccess($resource) {
        // If not logged in, redirect to login page
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit();
        }
        
        // If logged in but doesn't have permission for this resource, redirect to access denied
        if (!hasPermission($resource)) {
            // For trainers, redirect to training sessions if trying to access another page
            if ($_SESSION['user_role'] === 'trainer' && $resource !== 'training_sessions') {
                header('Location: access_denied.php');
                exit();
            }
            
            // For managers trying to access forbidden areas
            if ($_SESSION['user_role'] === 'manager' && !hasPermission($resource)) {
                header('Location: access_denied.php');
                exit();
            }
            
            // For any other scenario (shouldn't happen with proper navigation)
            header('Location: access_denied.php');
            exit();
        }
    }
} 