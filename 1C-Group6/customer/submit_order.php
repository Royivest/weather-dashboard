<?php
/**
 * Order submission handler
 * This file handles customer order form submission, including:
 * - Input validation
 * - Order data insertion
 * - Product amount calculation
 * - Redirect to order view page
 */
// Request method validation: Only allow POST requests for order submission.
// This prevents users from accessing this script directly via GET and ensures data integrity.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "Invalid request method. Please submit the form.";
    exit;
}

// Enable error display and start session for debugging and authentication.
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Database connection settings: Set up connection parameters for MySQL.
$hostname = "127.0.0.1";
$database = "projectdb"; 
$username = "root";
$password = "";

// Establish database connection using mysqli.
// If connection fails, stop processing and show error.
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve product order quantities from the submitted form.
$qty = isset($_POST['qty']) ? $_POST['qty'] : [];

// Retrieve half-customized options (checkboxes or similar inputs).
$customize_material = isset($_POST['customize_material']) ? 1 : 0;
$customize_color = isset($_POST['customize_color']) ? 1 : 0;
$customize_accessory = isset($_POST['customize_accessory']) ? 1 : 0;

// Retrieve fully customized order fields from the form.
$custom_desc = isset($_POST['custom_desc']) ? mysqli_real_escape_string($conn, $_POST['custom_desc']) : '';
$custom_budget = isset($_POST['custom_budget']) ? floatval($_POST['custom_budget']) : null;
$custom_qty = isset($_POST['custom_qty']) ? intval($_POST['custom_qty']) : null;
$custom_deadline = isset($_POST['custom_deadline']) ? $_POST['custom_deadline'] : null;

// Retrieve customer information from the form and session.
$customer_name = isset($_POST['customer_name']) ? mysqli_real_escape_string($conn, $_POST['customer_name']) : '';
$contact = isset($_POST['contact']) ? mysqli_real_escape_string($conn, $_POST['contact']) : '';
$address = isset($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : '';
$company = isset($_POST['company']) ? mysqli_real_escape_string($conn, $_POST['company']) : '';
$cid = isset($_SESSION['customerID']) ? intval($_SESSION['customerID']) : 0;

$order_date = date("Y-m-d H:i:s");

// Retrieve selected materials and colors for each product (if applicable).
$material_selected = isset($_POST['material_selected']) ? $_POST['material_selected'] : [];
$color_selected = isset($_POST['color_selected']) ? $_POST['color_selected'] : [];

// --- Inventory check logic ---
// Calculate the total material needs for all ordered products.
$all_enough = true;
$low_stock_materials = [];
$material_needs = [];
foreach ($qty as $pid => $oqty) {
    $oqty = intval($oqty);
    if ($oqty > 0) {
        // For each product, determine the required amount of each material.
        $sql_mat = "SELECT pm.mid, pm.pmqty, m.mqty, m.mreorderqty, m.mname FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = $pid";
        $result_mat = mysqli_query($conn, $sql_mat);
        while ($row = mysqli_fetch_assoc($result_mat)) {
            $need = $row['pmqty'] * $oqty;
            if (!isset($material_needs[$row['mid']])) {
                $material_needs[$row['mid']] = [
                    'need' => 0,
                    'mqty' => $row['mqty'],
                    'mreorderqty' => $row['mreorderqty'],
                    'mname' => $row['mname']
                ];
            }
            $material_needs[$row['mid']]['need'] += $need;
        }
    }
}
// Check if all required materials are in stock.
foreach ($material_needs as $mid => $info) {
    if ($info['mqty'] < $info['need']) {
        // If any material is insufficient, set flag and break.
        $all_enough = false;
        break;
    }
    // Track materials that will fall below reorder level after this order.
    if (($info['mqty'] - $info['need']) < $info['mreorderqty']) {
        $low_stock_materials[] = $info['mname'];
    }
}
// If not enough material, alert the user and stop order processing.
if (!$all_enough) {
    echo "<script>alert('Not enough material stock for your order! Please try again.');window.location.href='create_order.php';</script>";
    exit;
}
// Deduct used materials from inventory and update the database.
foreach ($material_needs as $mid => $info) {
    $sql = "UPDATE material SET mqty = mqty - ? WHERE mid = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $info['need'], $mid);
    mysqli_stmt_execute($stmt);
}

// --- Order type validation logic ---
$totalAmount = isset($_POST['totalAmount']) ? floatval($_POST['totalAmount']) : 0;
$hasProduct = false;
foreach ($qty as $pid => $oqty) {
    if (intval($oqty) > 0) $hasProduct = true;
}
// Business rule: Cannot submit both product and fully customized order in one submission.
if ($hasProduct && strlen($custom_desc) > 0) {
    echo "<script>alert('You cannot order products and fully customized order at the same time.');window.location.href='create_order.php';</script>";
    exit;
}
// Validation for normal/half-customized orders.
if ($hasProduct) {
    if ($totalAmount <= 0) {
        echo "<script>alert('Total Amount must be greater than 0.');window.location.href='create_order.php';</script>";
        exit;
    }
    // Prevent users from filling in fully customized fields when ordering products.
    if (strlen($custom_desc) > 0 || $custom_budget || $custom_qty || $custom_deadline) {
        echo "<script>alert('Please do not fill in the fully customized order section when ordering products.');window.location.href='create_order.php';</script>";
        exit;
    }
}
// Validation for fully customized orders.
if (strlen($custom_desc) > 0) {
    if (strlen($custom_desc) < 100) {
        echo "<script>alert('Please enter at least 100 characters for your custom description.');window.location.href='create_order.php';</script>";
        exit;
    }
    if (!$custom_budget || $custom_budget <= 0) {
        echo "<script>alert('Please enter a valid budget for your custom order.');window.location.href='create_order.php';</script>";
        exit;
    }
    if (!$custom_qty || $custom_qty <= 0) {
        echo "<script>alert('Please enter a valid quantity for your custom order.');window.location.href='create_order.php';</script>";
        exit;
    }
    if (!$custom_deadline) {
        echo "<script>alert('Please enter an expected delivery date for your custom order.');window.location.href='create_order.php';</script>";
        exit;
    }
    // Prevent users from mixing product and fully customized order logic.
    if ($hasProduct || $totalAmount > 0) {
        echo "<script>alert('For fully customized order, product quantity must be 0 and total amount must be 0.');window.location.href='create_order.php';</script>";
        exit;
    }
}

// --- Order insertion logic ---
// Handle normal/half-customized orders: Insert each product order into the database.
$has_normal_order = false;
foreach ($qty as $pid => $oqty) {
    $oqty = intval($oqty);
    if ($oqty > 0) {
        $has_normal_order = true;
        // Retrieve product price for cost calculation.
        $sql_price = "SELECT pcost FROM product WHERE pid = $pid";
        $result_price = mysqli_query($conn, $sql_price);
        $row_price = mysqli_fetch_assoc($result_price);
        $price = $row_price ? floatval($row_price['pcost']) : 0;
        $ocost = $price * $oqty;
        // Insert the order into the orders table.
        $sql = "INSERT INTO orders (odate, pid, oqty, ocost, cid, ostatus) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "siiid", $order_date, $pid, $oqty, $ocost, $cid);
        mysqli_stmt_execute($stmt);
        $oid = mysqli_insert_id($conn);
        // If there is half-customized data, insert it into the customize table.
        if ($customize_color || $custom_desc || $custom_budget || $custom_qty || $custom_deadline) {
            $sql2 = "INSERT INTO customize (oid, customize_color, customize_desc, created_at) VALUES (?, ?, ?, NOW())";
            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "iss", $oid, $customize_color, $custom_desc);
            mysqli_stmt_execute($stmt2);
        }
    }
}
// Handle fully customized orders: Insert only if no normal order and all validations pass.
if (!$has_normal_order && !empty($custom_desc) && strlen($custom_desc) >= 100 && $custom_qty > 0) {
    $null_pid = null;
    $sql = "INSERT INTO orders (odate, pid, oqty, ocost, cid, 
        customer_expected_budget, customer_expected_date, quote_accepted, quote_round) 
        VALUES (?, ?, ?, 0, ?, ?, ?, NULL, 0)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sisdss", $order_date, $null_pid, $custom_qty, $cid, $custom_budget, $custom_deadline);
    mysqli_stmt_execute($stmt);
    $oid = mysqli_insert_id($conn);
    // Insert the fully customized description into the customize table.
    $sql2 = "INSERT INTO customize (oid, customize_color, customize_desc, created_at) VALUES (?, '', ?, NOW())";
    $stmt2 = mysqli_prepare($conn, $sql2);
    mysqli_stmt_bind_param($stmt2, "is", $oid, $custom_desc);
    mysqli_stmt_execute($stmt2);
}

// Close the database connection to free resources.
mysqli_close($conn);

// If any materials are below reorder level, show a warning to the user after order placement.
if (!empty($low_stock_materials)) {
    $msg = "Order placed successfully! Warning: The following materials are below reorder level, please restock soon: " . implode(", ", array_unique($low_stock_materials));
    echo "<script>alert('$msg');window.location.href='view_order.php';</script>";
    exit;
}

// After completion, redirect to order view page
header("Location: view_order.php");
exit;
?>
