<?php
/**
 * Database connection configuration file
 * This file contains database connection settings and related helper functions
 * Provides a secure database operation interface
 */

/**
 * Database configuration constants
 * Define parameters required for database connection
 */
define('DB_HOST', 'localhost');  // Database host address
define('DB_USER', 'root');       // Database username
define('DB_PASS', '');           // Database password
define('DB_NAME', 'projectdb');  // Database name

/**
 * Establish database connection
 * Use mysqli object-oriented method to create connection
 */
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

/**
 * Connection error handling
 * If connection fails, log the error and terminate the program
 */
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

/**
 * Set character set
 * Use utf8mb4 to support full Unicode character set (including emojis)
 */
$conn->set_charset("utf8mb4");

/**
 * String escape function
 * Used to prevent SQL injection attacks
 * @param string $string The string to be escaped
 * @return string Escaped string
 */
function escape_string($string) {
    global $conn;
    return $conn->real_escape_string($string);
}

/**
 * Secure query execution function
 * Execute SQL queries using prepared statements to prevent SQL injection
 * @param string $sql SQL query statement
 * @param string $types Parameter type string (i: integer, d: double, s: string, b: blob)
 * @param array $params Query parameter array
 * @return mysqli_stmt|false Returns statement object on success, false on failure
 */
function execute_query($sql, $types = "", $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        error_log("Query preparation failed: " . $conn->error);
        return false;
    }
    
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    if (!$stmt->execute()) {
        error_log("Query execution failed: " . $stmt->error);
        return false;
    }
    
    return $stmt;
}

function hash_password($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}
?> 