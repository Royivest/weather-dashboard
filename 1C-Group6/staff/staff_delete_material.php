<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');

// Get material ID from POST data, default to 0 if not set
$mid = intval($_POST['mid'] ?? 0);

/**
 * Show a modal dialog with a message and redirect after confirmation
 * Used for both error and success messages
 * @param string $message The message to display
 * @param string $redirect The URL to redirect to after confirmation
 */
function showModalAndRedirect($message, $redirect) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Delete Material</title>';
    echo '<link rel="stylesheet" href="../css/view_order_styles.css">';
    echo '<style>.modal-bg{position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;}.modal-box{background:#fff;padding:32px 28px;border-radius:14px;box-shadow:0 4px 24px rgba(0,0,0,0.18);text-align:center;max-width:350px;}.modal-box h2{margin-bottom:18px;color:#29487d;}.modal-box button{margin-top:18px;padding:8px 32px;border-radius:8px;background:#4267b2;color:#fff;border:none;font-size:1.1em;cursor:pointer;}.modal-box button:hover{background:#29487d;}</style>';
    echo '</head><body>';
    echo '<div class="modal-bg"><div class="modal-box">';
    echo '<h2>Delete Material</h2>';
    echo '<div style="font-size:1.1em;margin-bottom:10px;">' . htmlspecialchars($message) . '</div>';
    echo '<button onclick="window.location.href=\'' . $redirect . '\'" autofocus>OK</button>';
    echo '</div></div>';
    echo '</body></html>';
    exit;
}

if ($mid > 0) {
    // Check if the material is referenced by any product (prodmat table)
    $check = $conn->prepare("SELECT COUNT(*) FROM prodmat WHERE mid=?");
    $check->bind_param("i", $mid);
    $check->execute();
    $check->bind_result($prod_count);
    $check->fetch();
    $check->close();

    if ($prod_count > 0) {
        // If material is used by products, prevent deletion and show error
        showModalAndRedirect('This material cannot be deleted because it is referenced by existing products.', 'staff_view_material.php');
    }

    // Soft delete: set is_deleted=1 instead of removing the row from the database
    $stmt = $conn->prepare("UPDATE material SET is_deleted = 1 WHERE mid = ?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $stmt->close();
    // Show success message and redirect
    showModalAndRedirect('Material deleted successfully!', 'staff_view_material.php');
} else {
    // Invalid or missing material ID, show error
    showModalAndRedirect('Invalid material ID!', 'staff_view_material.php');
} 