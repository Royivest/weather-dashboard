<?php
/**
 * Password hash generator
 * This file is used to generate secure password hashes for database storage
 * Uses PHP's built-in password_hash() function with PASSWORD_DEFAULT algorithm
 */

// Check if it is a POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the password from POST data, default to empty string if not set
    $password = $_POST['password'] ?? '';
    // If password is not empty, generate hash
    if ($password !== '') {
        // Use PHP's password_hash to securely hash the password
        $hash = password_hash($password, PASSWORD_DEFAULT);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Hash Generator</title>
    <style>
        /* Basic styles for the page layout */
        body { 
            font-family: Arial, sans-serif; 
            background: #f0f0f0; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
        }
        /* Container styles for the hash generator box */
        .container { 
            background: #fff; 
            padding: 2em; 
            border-radius: 8px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); 
        }
        /* Input field styles */
        input[type="text"], input[type="password"] { 
            width: 100%; 
            padding: 0.5em; 
            margin-bottom: 1em; 
            border: 1px solid #ccc; 
            border-radius: 4px; 
        }
        /* Button styles */
        button { 
            padding: 0.5em 1.5em; 
            background: #4CAF50; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        /* Hash display area styles */
        .hash { 
            margin-top: 1em; 
            word-break: break-all; 
            background: #f9f9f9; 
            padding: 1em; 
            border-radius: 4px; 
            border: 1px solid #eee; 
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Password Hash Generator</h2>
    <!-- Password input form for user to enter a password -->
    <form method="post">
        <label for="password">Enter Password:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Generate Hash</button>
    </form>
    <?php if (!empty($hash)): ?>
        <!-- Display generated hash to the user -->
        <div class="hash">
            <strong>Hash:</strong><br>
            <code><?php echo htmlspecialchars($hash); ?></code>
        </div>
    <?php endif; ?>
</div>
</body>
</html> 