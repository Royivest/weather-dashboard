<?php
// Start the session to access user authentication if needed
session_start();
// Include the database connection file to enable database operations
require_once('../includes/db_connect.php');
// Set the response content type to JSON for AJAX/API usage
header('Content-Type: application/json');

// Retrieve the order ID from the GET request and ensure it is an integer
$oid = isset($_GET['oid']) ? intval($_GET['oid']) : 0;

// Business logic: Only proceed if a valid order ID is provided
if ($oid > 0) {
    // Query the database for the product ID and order quantity of the specified order
    $res = $conn->query("SELECT pid, oqty FROM orders WHERE oid=$oid");
    // If the order exists, return the product ID and quantity in JSON format
    if ($row = $res->fetch_assoc()) {
        // Success: Return order info for further processing (e.g., stock check, payment)
        echo json_encode(['result'=>'ok', 'pid'=>$row['pid'], 'oqty'=>intval($row['oqty'])]);
        exit;
    }
}
// If the order ID is invalid or not found, return an error result
// This prevents the frontend from proceeding with invalid or missing order data
echo json_encode(['result'=>'error']); 