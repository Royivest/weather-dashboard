<?php
session_start(); // Start the session to access session variables
// Unset all session variables to clear any staff login data
$_SESSION = array();
// Destroy the session to fully log out the staff member
session_destroy();
// Redirect the user to the staff login page after logout
header("Location: login.php");
exit(); // Ensure no further code is executed after redirection
?> 