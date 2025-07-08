<?php
/**
 * Utility functions for the Pet Management System
 */

/**
 * Check if the current user is an admin
 * 
 * @return bool True if the user is an admin, false otherwise
 */
function is_admin() {
    return (
        isset($_SESSION['user_id']) && 
        isset($_SESSION['user_type']) && 
        $_SESSION['user_type'] === 'veterinary' && 
        isset($_SESSION['username']) && 
        $_SESSION['username'] === 'admin'
    );
}

/**
 * Redirect admin users away from pages they shouldn't access
 * 
 * @param string $redirect_url The URL to redirect to
 * @return void
 */
function restrict_admin_access($redirect_url = 'admin/index.php') {
    if (is_admin()) {
        header("Location: $redirect_url");
        exit();
    }
}
?>
