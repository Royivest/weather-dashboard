<?php
/**
 * Customer Home Page
 * This page displays a welcome message and function entry points for customers after login.
 * Includes session validation, navigation bar, logout button, etc.
 */
// Start the session to access customer authentication information
session_start();
// Session validation: Ensure only authenticated customers can access this page.
// If the session does not contain a customer ID, it means the user is not logged in.
// Redirect to the login page to prevent unauthorized access to customer features.
if (!isset($_SESSION['customerID'])) {
    header('Location: index.php?error=session');
    exit();
}
?>
<!DOCTYPE html>
<html lang="HK">
<head>
  <meta charset="UTF-8">
  <title>Customer Home - Smile & Sunshine Toy Co. Ltd</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    /* Navigation bar style: provides links to main customer features */
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
    /* Dashboard box style: main content area for welcome and actions */
    .dashboard-box {
      background: #fff;
      border-radius: 14px;
      padding: 40px 30px 30px 30px;
      max-width: 600px;
      margin: 40px auto;
      text-align: center;
      box-shadow: 0 2px 16px rgba(0,0,0,0.08);
    }
    .dashboard-box h1 {
      font-size: 2.5em;
      margin-bottom: 20px;
    }
    .dashboard-box p {
      font-size: 1.2em;
      margin-bottom: 30px;
    }
    .logout-btn {
      background: #5cb85c;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 16px 0;
      width: 100%;
      font-size: 1.2em;
      font-weight: bold;
      cursor: pointer;
      margin-top: 20px;
      transition: background 0.2s;
    }
    .logout-btn:hover {
      background: #449d44;
    }
  </style>
</head>
<body>
  <!-- Navigation bar: provides quick access to all main customer features -->
  <div class="navbar">
    <a href="home.php">Home</a>
    <a href="create_order.php">Create Order</a>
    <a href="view_order.php">View Orders</a>
    <a href="update_profile.php">Update Profile</a>
  </div>
  <div class="dashboard-box">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <p>This is your dashboard. Please choose an option from the menu above to continue.</p>
    <!-- Logout form: allows the customer to securely end their session and log out -->
    <form action="../includes/logout.php" method="post">
      <button type="submit" class="logout-btn">Logout</button>
    </form>
  </div>
</body>
</html> 