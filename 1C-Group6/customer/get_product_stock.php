<?php
// Include the database connection file to enable database operations
require_once('../includes/db_connect.php');
// Set the response content type to JSON for AJAX/API usage
header('Content-Type: application/json');

// Retrieve the product ID from the GET request and ensure it is an integer
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

// Business logic: Only proceed if a valid product ID is provided
if ($pid > 0) {
    // Calculate the maximum stock for the product based on all required materials
    // This ensures that the order quantity does not exceed what can be produced
    // with the current inventory, preventing over-selling and production errors
    $sql = "SELECT MIN(FLOOR(m.mqty / pm.pmqty)) AS stock FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = $pid";
    $res = $conn->query($sql);
    // If the query returns a result, output the available stock in JSON format
    if ($row = $res->fetch_assoc()) {
        // Success: Return the calculated stock for the product
        echo json_encode(['result'=>'ok', 'stock'=>intval($row['stock'])]);
        exit;
    }
}
// If the product ID is invalid or not found, return an error result
// This prevents the frontend from proceeding with invalid or missing product data
echo json_encode(['result'=>'error']); 