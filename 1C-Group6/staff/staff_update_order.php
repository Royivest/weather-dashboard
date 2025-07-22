<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// Handle POST request for updating an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['oid'])) {
    $oid = intval($_POST['oid']);
    $sid = intval($_SESSION['staffID']);
    $response = trim($_POST['staff_response'] ?? '');
    $new_status = intval($_POST['new_status'] ?? 1);
    $new_delivery_status = isset($_POST['new_delivery_status']) ? intval($_POST['new_delivery_status']) : null;
    $design_image = '';
    // Handle design image upload if provided
    if (isset($_FILES['design_image']) && $_FILES['design_image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['design_image']['name'], PATHINFO_EXTENSION);
        $filename = 'design_' . $oid . '_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['design_image']['tmp_name'], __DIR__ . '/uploads/' . $filename);
        $design_image = $filename;
    }
    // Get current order and delivery status (for business logic, e.g., quote handling)
    $res = $conn->query("SELECT ostatus, delivery_status, quote_status, quote_reject_count FROM orders WHERE oid=$oid");
    if ($row = $res->fetch_assoc()) {
        $current_ostatus = (int)$row['ostatus'];
        $current_dstatus = (int)$row['delivery_status'];
        $quote_status = (int)$row['quote_status'];
        $quote_reject_count = (int)$row['quote_reject_count'];
        // Only show response form for orders with quote_status=2 and not too many rejections
        if ($quote_status == 2 && $quote_reject_count < 10) {
            // Show response form (UI logic, not used here)
        }
    }
    // Insert staff response into staff_response table (for interaction history)
    $stmt = $conn->prepare("INSERT INTO staff_response (oid, sid, response_text, design_image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $oid, $sid, $response, $design_image);
    $stmt->execute();
    // Update order status and delivery status as needed
    if ($new_delivery_status !== null) {
        $sql = "UPDATE orders SET ostatus=?, delivery_status=? WHERE oid=?";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("iii", $new_status, $new_delivery_status, $oid);
        $stmt2->execute();
    } else {
        $sql = "UPDATE orders SET ostatus=? WHERE oid=?";
        $stmt2 = $conn->prepare($sql);
        $stmt2->bind_param("ii", $new_status, $oid);
        $stmt2->execute();
    }
    // If a quote value or design image is provided, update the order with these fields
    $has_quote = isset($_POST['quote_value']) && $_POST['quote_value'] !== '';
    $has_image = !empty($design_image);
    if ($has_quote || $has_image) {
        $quote_value = $has_quote ? floatval($_POST['quote_value']) : null;
        $update_sql = "UPDATE orders SET current_quote_value=?, design_image=? WHERE oid=?";
        $stmt3 = $conn->prepare($update_sql);
        $stmt3->bind_param("dsi", $quote_value, $design_image, $oid);
        $stmt3->execute();
    }
    // Redirect to order view page with success message
    header("Location: staff_view_order.php?msg=updated");
    exit();
}
// If not a valid POST, redirect with error
header("Location: staff_view_order.php?error=invalid");
exit();
?> 