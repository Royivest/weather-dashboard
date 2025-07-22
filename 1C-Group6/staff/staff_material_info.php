<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    // If not logged in, redirect to login page for security
    header("Location: login.php?error=session");
    exit();
}
?> 