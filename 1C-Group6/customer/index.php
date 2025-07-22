<!DOCTYPE html>
<html lang="HK">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Login - Smile & Sunshine Toy Co. Ltd</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    /* Set the specified image as a full-page background */
    body {
      background: url('../Sample Images/background/Screenshot_20250404_025752_DeepSeek.jpg') no-repeat center center fixed;
      background-size: cover;
      min-height: 100vh;
    }
    /* Add a semi-transparent overlay to improve readability */
    .container {
      background: rgba(255,255,255,0.92);
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2>Smile & Sunshine Toy Co. Ltd</h2>
      <h1>Customer Login</h1>
    </div>
    <?php
    // Start the session to access customer authentication information
    session_start();
    
    // Session validation: If the user is already logged in, redirect to the home page.
    // This prevents logged-in users from seeing the login page again and ensures a smooth user experience.
    if(isset($_SESSION['customerID'])) {
        header("Location: http://localhost/1C-Group6/customer/home.php");
        exit();
    }

    // Error handling: Check for login errors passed via GET parameters.
    // This block displays user-friendly error messages for common login issues.
    if(isset($_GET['error'])) {
        echo '<div class="error-message">';
        switch($_GET['error']) {
            case 'invalid':
                // Displayed when the username or password is incorrect
                echo 'Invalid username or password';
                break;
            case 'empty':
                // Displayed when required fields are left blank
                echo 'Please fill in all fields';
                break;
            case 'session':
                // Displayed when the session has expired or the user is not logged in
                echo 'Please log in to continue';
                break;
            default:
                // Catch-all for any other errors
                echo 'An error occurred';
        }
        echo '</div>';
    }
    ?>
    <!-- Login form: Allows the customer to enter credentials and log in -->
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
    <div class="footer">
      <p>Are you a staff member? <a href="../staff/login.php">Staff Login</a></p>
      <p>Don't have an account? <a href="register.php">Register</a></p>
    </div>
  </div>
</body>
</html> 