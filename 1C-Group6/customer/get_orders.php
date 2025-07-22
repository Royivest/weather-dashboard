/**
 * Customer Order Query API
 * This file is used to retrieve all order information for the currently logged-in customer.
 * Provides authentication, error handling, and returns results in JSON format for frontend use.
 */

<?php
// Enable error display for debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session to access customer login information
session_start();

// Debug: Output current session information for troubleshooting
// (Remove or comment out in production)
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

/**
 * Authentication: Ensure the user is logged in before accessing order data.
 * If not logged in, return a 401 Unauthorized error and stop further processing.
 */
if (!isset($_SESSION['customerID'])) {
    // Security: Prevent unauthorized access to customer order data
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}
$customerID = $_SESSION['customerID'];

/**
 * Database configuration: Set up connection parameters for MySQL.
 * These values should match your environment and be secured in production.
 */
$hostname = "127.0.0.1";  // Database host address
$database = "projectdb";  // Database name
$username = "root";       // Database username
$password = "";           // Database password

/**
 * Establish database connection using mysqli.
 * If connection fails, return a 500 Internal Server Error and stop processing.
 */
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    // Error handling: Database connection failed
    http_response_code(500);
    echo json_encode(["error" => "Connection failed: " . mysqli_connect_error()]);
    exit;
}

/**
 * Query order data for the logged-in customer.
 * Orders are sorted by order date in descending order (most recent first).
 * Business logic: Only show orders belonging to the current customer.
 */
$sql = "SELECT * FROM orders WHERE cid = $customerID ORDER BY odate DESC";
$result = mysqli_query($conn, $sql);

/**
 * Error handling: If the query fails, return a 500 error and stop processing.
 * This prevents the frontend from receiving incomplete or invalid data.
 */
if (!$result) {
    http_response_code(500);
    echo json_encode(["error" => "Database query error: " . mysqli_error($conn)]);
    exit;
}

/**
 * Process query results: Convert the result set into an associative array.
 * This makes it easy for the frontend to consume the data as JSON.
 */
$orders_array = array();
while($row = mysqli_fetch_assoc($result)) {
    $orders_array[] = $row;
}

/**
 * Output the result as JSON.
 * Set the response header to indicate JSON content, then print the order data.
 */
header("Content-Type: application/json");
echo json_encode($orders_array);

// Close the database connection to free resources
mysqli_close($conn);
?>
