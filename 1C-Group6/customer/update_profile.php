<?php
/**
 * Customer Profile Update Page
 * This page allows customers to update their profile and password.
 * Includes session validation, form display, and backend update logic.
 *
 * Business logic:
 * - Only logged-in customers can access this page (session validation)
 * - Customers can update their name, contact, address, and optionally their password
 * - All updates are written to the database, and session info is refreshed
 */
require_once('../includes/session_handler.php');
require_customer(); // Ensure only logged-in customers can access this page

// Load customer profile data from the database for display in the form
require_once('../includes/db_connect.php');
$cid = $_SESSION['customerID'];
$sql = "SELECT * FROM customer WHERE cid = $cid";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
$name = isset($row['cname']) ? htmlspecialchars($row['cname']) : '';
$contact = isset($row['ctel']) ? htmlspecialchars($row['ctel']) : '';
$address = isset($row['caddr']) ? htmlspecialchars($row['caddr']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Profile - Smile & Sunshine Toy Co. Ltd</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    /* Navigation bar styles */
    .navbar {
      display: flex;
      justify-content: center;
      align-items: center;
      background: #fff;
      padding: 20px 0 10px 0;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      margin-bottom: 30px;
    }
    .navbar a {
      color: #234;
      text-decoration: none;
      font-weight: bold;
      margin: 0 30px;
      font-size: 1.2em;
      transition: color 0.2s;
    }
    .navbar a:hover {
      color: #2a7ae2;
    }
    .container {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      border-radius: 16px;
      padding: 40px 30px 30px 30px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    }
    .header h1 {
      font-size: 2.2em;
      margin-bottom: 20px;
      text-align: center;
    }
  </style>
</head>
<body>
  <!-- Navigation bar for customer pages -->
  <div class="navbar">
    <a href="home.php">Home</a>
    <a href="create_order.php">Create Order</a>
    <a href="view_order.php">View Orders</a>
    <a href="update_profile.php">Update Profile</a>
  </div>
  <div class="container">
    <div class="header">
      <h1>Update Profile</h1>
    </div>
    <!-- Profile update form. Allows customer to update their name, contact, address, and password. -->
    <form method="POST" action="http://localhost/1C-Group6/customer/update_profile.php" style="max-width:400px;margin:0 auto;">
      <label for="cname">Name</label>
      <input type="text" id="cname" name="cname" value="<?php echo $name; ?>" required><br><br>
      <label for="contact">Contact</label>
      <input type="text" id="contact" name="contact" value="<?php echo $contact; ?>" required><br><br>
      <label for="address">Address</label>
      <input type="text" id="address" name="address" value="<?php echo $address; ?>" required><br><br>
      <label for="password">New Password</label>
      <input type="password" id="password" name="password" placeholder="Leave blank to keep current"><br><br>
      <button type="submit">Update Profile</button>
    </form>
  </div>
</body>
</html>

<?php
/**
 * Handle profile update form submission
 * - Validates and updates customer info in the database
 * - If a new password is provided, updates the password as well
 * - Updates session info to reflect new name
 * - Shows a success message and reloads the page
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once('../includes/db_connect.php');
    $cid = $_SESSION['customerID'];
    // Sanitize and validate input fields
    $cname = mysqli_real_escape_string($conn, $_POST['cname']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    // Update customer info (name, contact, address)
    $update_sql = "UPDATE customer SET cname='$cname', ctel='$contact', caddr='$address' WHERE cid=$cid";
    mysqli_query($conn, $update_sql);
    // If a new password is provided, update the password (hashed)
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_pwd = "UPDATE customer SET cpassword='$password' WHERE cid=$cid";
        mysqli_query($conn, $update_pwd);
    }
    // Update session to ensure userType and username are set to the latest values
    $_SESSION['username'] = $cname;
    $_SESSION['userType'] = 'customer';
    mysqli_close($conn);
    // Show a success message and reload the page
    echo '<script>alert("Profile updated successfully!");window.location.href="http://localhost/1C-Group6/customer/update_profile.php";</script>';
}
?>
