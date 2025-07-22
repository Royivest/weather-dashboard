<?php
session_start();
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
$pid = intval($_POST['pid'] ?? 0);

function showModalAndRedirect($message, $redirect) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>Delete Product</title>';
    echo '<link rel="stylesheet" href="../css/view_order_styles.css">';
    echo '<style>.modal-bg{position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;z-index:9999;}.modal-box{background:#fff;padding:32px 28px;border-radius:14px;box-shadow:0 4px 24px rgba(0,0,0,0.18);text-align:center;max-width:350px;}.modal-box h2{margin-bottom:18px;color:#29487d;}.modal-box button{margin-top:18px;padding:8px 32px;border-radius:8px;background:#4267b2;color:#fff;border:none;font-size:1.1em;cursor:pointer;}.modal-box button:hover{background:#29487d;}</style>';
    echo '</head><body>';
    echo '<div class="modal-bg"><div class="modal-box">';
    echo '<h2>Delete Product</h2>';
    echo '<div style="font-size:1.1em;margin-bottom:10px;">' . htmlspecialchars($message) . '</div>';
    echo '<button onclick="window.location.href=\'' . $redirect . '\'" autofocus>OK</button>';
    echo '</div></div>';
    echo '</body></html>';
    exit;
}

if ($pid > 0) {
    // Check if product is referenced by any order
    $check = $conn->prepare("SELECT COUNT(*) FROM orders WHERE pid=?");
    $check->bind_param("i", $pid);
    $check->execute();
    $check->bind_result($order_count);
    $check->fetch();
    $check->close();

    if ($order_count > 0) {
        showModalAndRedirect('This product cannot be deleted because it is referenced by existing orders.', 'staff_view_items.php');
    }

    // Soft delete: set is_deleted=1 instead of deleting the row
    $stmt = $conn->prepare("UPDATE product SET is_deleted = 1 WHERE pid = ?");
    $stmt->bind_param("i", $pid);
    $stmt->execute();
    showModalAndRedirect('Product deleted successfully!', 'staff_view_items.php');
} else {
    showModalAndRedirect('Invalid product ID!', 'staff_view_items.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delete Product - Staff</title>
    <style>
        body { background: #f4f6fb; font-family: Arial, sans-serif; }
        .container { max-width: 700px; margin: 3em auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 2em; text-align: center; }
        .back-link { display: inline-block; margin-bottom: 1.5em; color: #4267b2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        h1 { color: #29487d; margin-bottom: 1em; }
        .placeholder { color: #888; font-size: 1.1em; margin-top: 2em; }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-link" href="home.php">&larr; Back to Home</a>
        <h1>Delete Product</h1>
        <div class="placeholder">This page is under construction.<br>Product deletion features coming soon.</div>
    </div>
</body>
</html> 