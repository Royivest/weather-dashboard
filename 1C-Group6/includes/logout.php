<?php
/**
 * Logout handler
 * This file handles the user logout process, including clearing session, remember me tokens, etc.
 */

// Start session to access session data
session_start();
require_once('db_connect.php');

/**
 * Clear 'remember me' token
 * If a remember me token exists, delete it from the database and client
 */
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Delete token from database
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    
    // Clear client cookie
    // Parameters:
    // - Set expiration time to the past (immediate expiration)
    // - Path is root directory
    // - Set as secure cookie (transmitted only via HTTPS)
    // - Set as HTTPOnly (prevent JavaScript access)
    setcookie('remember_token', '', time() - 3600, '/', '', true, true);
}

/**
 * Clear session data
 * Clear all session variables
 */
$_SESSION = array();

/**
 * Destroy session
 * Completely delete session data
 */
session_destroy();

/**
 * Redirect to login page
 * Redirect to the corresponding login page based on user type
 */
if (isset($_GET['type']) && $_GET['type'] === 'staff') {
    header("Location: ../staff/login.php");
} else {
    header("Location: ../customer/index.php");
}
exit();
?> 