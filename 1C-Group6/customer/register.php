<!DOCTYPE html>
<html lang="HK">
<head>
  <meta charset="UTF-8">
  <title>Customer Register - Smile & Sunshine Toy Co. Ltd</title>
  <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
  <div class="container">
    <div class="header">
      <h2>Smile & Sunshine Toy Co. Ltd</h2>
      <h1>Customer Register</h1>
    </div>
    <?php
    /**
     * Display registration error or success message
     */
    if (isset($_GET['error'])) {
      echo '<div class="error-message">';
      switch ($_GET['error']) {
        case 'exists': echo 'Username already exists!'; break;
        case 'empty': echo 'Please fill in all fields!'; break;
        case 'system': echo 'System error, please try again.'; break;
        default: echo 'An error occurred.'; break;
      }
      echo '</div>';
    }
    if (isset($_GET['success'])) {
      echo '<div class="success-message">Registration successful! You can now <a href="http://localhost/1C-Group6/customer/index.php">login</a>.</div>';
    }
    ?>
    <!-- Registration form -->
    <form action="register.php" method="POST">
      <div class="form-group">
        <label for="cname">Username</label>
        <input type="text" id="cname" name="cname" required>
      </div>
      <div class="form-group">
        <label for="cpassword">Password</label>
        <input type="password" id="cpassword" name="cpassword" required>
      </div>
      <div class="form-group">
        <label for="ctel">Phone</label>
        <input type="text" id="ctel" name="ctel" required>
      </div>
      <div class="form-group">
        <label for="caddr">Address</label>
        <input type="text" id="caddr" name="caddr" required>
      </div>
      <div class="form-group">
        <label for="company">Company</label>
        <input type="text" id="company" name="company" required>
      </div>
      <button type="submit">Register</button>
    </form>
    <div class="footer">
      <p>Already have an account? <a href="index.php">Login</a></p>
    </div>
  </div>
</body>
</html>

<?php
session_start();
/**
 * Handle registration form submission
 * Includes input validation, account uniqueness check, password hashing, and database insertion
 */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  require_once("../includes/db_connect.php");
  $cname = trim($_POST['cname']);
  $cpassword = $_POST['cpassword'];
  $ctel = trim($_POST['ctel']);
  $caddr = trim($_POST['caddr']);
  $company = trim($_POST['company']);

  // Check if any field is empty
  if (empty($cname) || empty($cpassword) || empty($ctel) || empty($caddr) || empty($company)) {
    header("Location: register.php?error=empty");
    exit();
  }

  // Check if account already exists
  $stmt = $conn->prepare("SELECT cid FROM customer WHERE cname = ?");
  $stmt->bind_param("s", $cname);
  $stmt->execute();
  $stmt->store_result();
  if ($stmt->num_rows > 0) {
    header("Location: register.php?error=exists");
    exit();
  }

  // Password hashing
  $hash = password_hash($cpassword, PASSWORD_DEFAULT);

  // Insert new customer data
  $stmt = $conn->prepare("INSERT INTO customer (cname, cpassword, ctel, caddr, company) VALUES (?, ?, ?, ?, ?)");
  $stmt->bind_param("sssss", $cname, $hash, $ctel, $caddr, $company);
  if ($stmt->execute()) {
    header("Location: register.php?success=1");
    exit();
  } else {
    header("Location: register.php?error=system");
    exit();
  }
}