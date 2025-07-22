<?php
/**
 * Customer login handler
 * This file handles customer login logic, including:
 * - Username and password verification
 * - Session management
 * - "Remember me" feature
 * - Secure redirection
 */

// Start session and set error reporting
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once('../includes/db_connect.php');

/**
 * Handle login form submission
 * Validate user credentials and set session
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and clean form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $rememberMe = isset($_POST['rememberMe']) ? true : false;

    /**
     * Input validation
     * Ensure username and password are not empty
     */
    if (empty($username) || empty($password)) {
        header("Location: http://localhost/1C-Group6/customer/index.php?error=empty");
        exit();
    }

    try {
        /**
         * Database query
         * Use prepared statements to prevent SQL injection
         */
        $stmt = $conn->prepare("SELECT cid, cname, cpassword FROM customer WHERE cname = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            /**
             * Password verification
             * Use password_verify to securely verify password hash
             */
            if (password_verify($password, $user['cpassword'])) {
                // Set session variables
                $_SESSION['customerID'] = $user['cid'];
                $_SESSION['username'] = $user['cname'];
                $_SESSION['userType'] = 'customer';

                /**
                 * "Remember me" feature processing
                 * If user chooses to remember login status, create a secure token
                 */
                if ($rememberMe) {
                    // Generate random token
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days valid period
                    
                    // Store token in database
                    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $user['cid'], $token, date('Y-m-d H:i:s', $expires));
                    $stmt->execute();
                    
                    // Set secure cookie
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }

                // Login successful, redirect to homepage
                header("Location: http://localhost/1C-Group6/customer/home.php");
                exit();
            }
        }
        
        // Login failed, redirect to login page
        header("Location: http://localhost/1C-Group6/customer/index.php?error=invalid");
        exit();
        
    } catch (Exception $e) {
        // Record error and display generic error message
        error_log("Login error: " . $e->getMessage());
        header("Location: http://localhost/1C-Group6/customer/index.php?error=system");
        exit();
    }
} else {
    // Non-POST request, redirect to login page
    header("Location: http://localhost/1C-Group6/customer/index.php");
    exit();
}
?>
