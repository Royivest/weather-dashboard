<?php
/**
 * Staff login handler
 * This file handles staff login logic, including:
 * - Username and password verification
 * - Staff role management
 * - Session management
 * - "Remember me" feature
 * - Secure redirection
 */

// Set error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session and include database connection
session_start();
require_once('../includes/db_connect.php');

/**
 * Handle login form submission
 * Validate staff credentials and set session
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
        header("Location: login.php?error=empty");
        exit();
    }

    try {
        /**
         * Database query
         * Use prepared statements to prevent SQL injection
         * Also retrieve staff role information
         */
        $stmt = $conn->prepare("SELECT sid, sname, spassword, srole FROM staff WHERE sname = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            /**
             * Password verification
             * Use password_verify to securely verify password hash
             */
            if (password_verify($password, $row['spassword'])) {
                // Set session variables, including staff role
                $_SESSION['staffID'] = $row['sid'];
                $_SESSION['username'] = $row['sname'];
                $_SESSION['userType'] = 'staff';
                $_SESSION['role'] = $row['srole'];

                /**
                 * "Remember me" feature processing
                 * If staff chooses to remember login status, create security token
                 */
                if ($rememberMe) {
                    // Generate random token
                    $token = bin2hex(random_bytes(32));
                    $expires = time() + (30 * 24 * 60 * 60); // 30 days valid period
                    
                    // Store token in database, marked as staff type
                    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires, user_type) VALUES (?, ?, ?, 'staff')");
                    $stmt->bind_param("iss", $row['sid'], $token, date('Y-m-d H:i:s', $expires));
                    $stmt->execute();
                    
                    // Set security cookie
                    setcookie('remember_token', $token, $expires, '/', '', true, true);
                }

                // Login successful, redirect to staff homepage
                header("Location: home.php");
                exit();
            }
        }
        
        // Login failed, redirect to login page
        header("Location: login.php?error=invalid");
        exit();
        
    } catch (Exception $e) {
        // Record error and show general error message
        error_log("Staff login error: " . $e->getMessage());
        header("Location: login.php?error=system");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="HK">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login - Smile & Sunshine Toy Co. Ltd</title>
    <link rel="stylesheet" href="../css/login_styles.css">
</head>
<body>
    <div class="container">
        <!-- Page title area -->
        <div class="header">
            <h2>Smile & Sunshine Toy Co. Ltd</h2>
            <h1>Staff Login</h1>
        </div>
        <?php
        /**
         * Error message display
         * Show corresponding error message based on error type
         */
        if(isset($_GET['error'])) {
            echo '<div class="error-message">';
            switch($_GET['error']) {
                case 'invalid':
                    echo 'Invalid username or password';
                    break;
                case 'empty':
                    echo 'Please fill in all fields';
                    break;
                case 'session':
                    echo 'Please log in to continue';
                    break;
                case 'unauthorized':
                    echo 'Unauthorized access';
                    break;
                default:
                    echo 'An error occurred';
            }
            echo '</div>';
        }
        ?>
        <!-- Login form -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="remember-me">
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <label for="rememberMe">Remember me</label>
            </div>
            <button type="submit">Login</button>
        </form>
        <!-- Footer area -->
        <div class="footer">
            <p>Are you a customer? <a href="../customer/index.php">Customer Login</a></p>
        </div>
    </div>
</body>
</html> 