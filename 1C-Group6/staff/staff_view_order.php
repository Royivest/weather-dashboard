<?php
/**
 * Staff order view page
 * This page displays all customer orders and provides detailed information, interaction history, response, and status update features
 * Includes session validation, database queries, interaction forms, and UI block descriptions
 */
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// Query all orders, join customer and product information for display
$sql = "SELECT o.*, c.cname, p.pname FROM orders o JOIN customer c ON o.cid = c.cid JOIN product p ON o.pid = p.pid ORDER BY o.odate DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Orders - Staff</title>
    <link rel="stylesheet" href="../css/view_order_styles.css">
    <style>
        .action-btn { background:#4267b2; color:#fff; border:none; border-radius:6px; padding:8px 18px; margin:4px 0; font-weight:bold; cursor:pointer; min-width:100px; }
        .action-btn:hover { background:#29487d; }
        .order-detail { background:#f9f9f9; border-radius:8px; margin:10px 0 20px 0; padding:18px 20px; text-align:left; box-shadow:0 2px 8px rgba(0,0,0,0.04); }
        .status-label { font-weight:bold; }
        .respond-form textarea { width:100%; border-radius:6px; border:1px solid #ccc; padding:8px; margin-bottom:8px; }
        .respond-form button { margin-right:8px; }
        .staff-response { color:#1a7a1a; background:#eafbe7; border-radius:6px; padding:8px 12px; margin:10px 0; font-weight:500; }
        .feedback-count { color:#4267b2; font-weight:500; margin-left:10px; }
        .replied-badge { display:inline-block; background:#1a7a1a; color:#fff; border-radius:6px; padding:2px 10px; font-size:0.95em; margin-left:6px; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="home.php">&larr; Back to Home</a>
    <h1>All Customer Orders</h1>
    <?php if ($result && $result->num_rows > 0): ?>
    <table class="order-table">
        <tr>
            <th>Order ID</th><th>Date</th><th>Customer</th><th>Product</th><th>Qty</th><th>Status</th><th>Delivery Status</th><th>Feedback Count</th><th>Action</th>
        </tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <?php
        // Query feedback count for this order
        $oid = $row['oid'];
        $feedback_count = 0;
        $feedback_sql = "SELECT COUNT(*) as cnt FROM feedback WHERE oid=$oid";
        $feedback_res = $conn->query($feedback_sql);
        if ($feedback_res && $frow = $feedback_res->fetch_assoc()) {
            $feedback_count = intval($frow['cnt']);
        }
        $is_fully_custom = (empty($row['pid']) || intval($row['pid']) === 0);
        ?>
        <tr>
            <td><?= $row['oid'] ?></td>
            <td><?= $row['odate'] ?></td>
            <td><?= htmlspecialchars($row['cname']) ?></td>
            <td><?= htmlspecialchars($row['pname']) ?></td>
            <td><?= $row['oqty'] ?></td>
            <td class="status-label">
                <?php
                // Show order status as text
                $status = $row['ostatus'];
                if ($status == 1) echo 'Processing';
                elseif ($status == 2) echo 'Paid';
                elseif ($status == 3) echo 'Completed';
                else echo 'Other';
                ?>
                <?php if (!empty($row['staff_response'])): ?><span class="replied-badge">Replied</span><?php endif; ?>
            </td>
            <td>
                <?php
                // Show delivery status as text
                $dstatus = isset($row['delivery_status']) ? (int)$row['delivery_status'] : 0;
                $dstatus_text = ['Not shipped', 'Shipped', 'In transit', 'Delivered'];
                echo $dstatus_text[$dstatus] ?? 'Unknown';
                ?>
            </td>
            <td>
                <span class="feedback-count">Customer feedback count: <?= $feedback_count ?></span>
            </td>
            <td>
                <button class="action-btn" onclick="toggleDetail('detail<?= $row['oid'] ?>')">Details</button>
            </td>
        </tr>
        <tr id="detail<?= $row['oid'] ?>" style="display:none;"><td colspan="9">
            <?php if ($is_fully_custom): ?>
            <div class="order-detail">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong>Order Description:</strong>
                    <button type="button" class="action-btn" style="background:#d9534f;min-width:80px;" onclick="toggleDetail('detail<?= $row['oid'] ?>')">Cancel</button>
                </div>
                <div style="margin:10px 0 8px 0;white-space:pre-wrap;word-break:break-all;"> <?= nl2br(htmlspecialchars($row['custom_desc'])) ?> </div>
                <div style="margin-bottom:8px;">
                    <strong>Budget:</strong> <?= isset($row['custom_budget']) ? htmlspecialchars($row['custom_budget']) : 'N/A' ?> |
                    <strong>Customer Contact:</strong> <?= htmlspecialchars($row['cid']) ?>
                </div>
                <div style="margin-bottom:8px;"><strong>Order Status:</strong> <?= $status ?></div>
                <div style="margin-bottom:8px;"><strong>Delivery Status:</strong> <?= $dstatus_text[$dstatus] ?? 'Unknown' ?></div>
                <div class="feedback-count">Customer feedback count: <?= $feedback_count ?></div>
                <!-- History section -->
                <div style="margin-bottom:8px;"><strong>Interaction History:</strong></div>
                <div style="background:#f6f6f6;border-radius:8px;padding:12px 16px;max-height:220px;overflow-y:auto;">
                <?php
                // Show all feedback for this order
                $fb_res = $conn->query("SELECT * FROM feedback WHERE oid=$oid ORDER BY created_at ASC");
                if ($fb_res && $fb_res->num_rows > 0) {
                    while($fb = $fb_res->fetch_assoc()) {
                        echo '<div style=\'margin-bottom:10px;\'><span style=\'color:#2a7ae2;font-weight:500;\'>Customer:</span> '.nl2br(htmlspecialchars($fb['feedback_text']));
                        if (isset($fb['rating']) && $fb['rating'] > 0) {
                            echo ' <span style=\'color:#f5b301;font-size:1.1em;\'>';
                            for ($i = 0; $i < $fb['rating']; $i++) echo '★';
                            for ($i = $fb['rating']; $i < 5; $i++) echo '☆';
                            echo '</span>';
                        }
                        echo ' <span style=\'color:#888;font-size:0.95em;\'>('.$fb['created_at'].')</span></div>';
                    }
                }
                // Show all staff_response for this order
                $sr_res = $conn->query("SELECT sr.*, s.sname FROM staff_response sr LEFT JOIN staff s ON sr.sid=s.sid WHERE sr.oid=$oid ORDER BY sr.created_at ASC");
                if ($sr_res && $sr_res->num_rows > 0) {
                    while($sr = $sr_res->fetch_assoc()) {
                        echo '<div style=\'margin-bottom:10px;\'><span style=\'color:#1a7a1a;font-weight:500;\'>Designer '.htmlspecialchars($sr['sname']).':</span> '.nl2br(htmlspecialchars($sr['response_text'])).' <span style=\'color:#888;font-size:0.95em;\'>('.$sr['created_at'].')</span>';
                        if (!empty($sr['design_image'])) {
                            echo '<br><img src=\'uploads/'.htmlspecialchars($sr['design_image']).'\' style=\'max-width:120px;border-radius:8px;margin-top:4px;\'>';
                        }
                        echo '</div>';
                    }
                }
                ?>
                </div>
                <form class="respond-form" method="post" action="staff_update_order.php" enctype="multipart/form-data" style="margin-bottom:10px;">
                    <input type="hidden" name="oid" value="<?= $row['oid'] ?>">
                    <textarea name="staff_response" rows="2" placeholder="Reply to customer or update status..." style="margin-bottom:8px;"></textarea><br>
                    <label style="font-weight:500;">Upload Design Image: <input type="file" name="design_image" accept="image/*"></label><br>
                    <label style="font-weight:500;">Update Order Status:
                        <select name="new_status">
                            <option value="1" <?= $status==1?'selected':'' ?>>Processing</option>
                            <option value="2" <?= $status==2?'selected':'' ?>>Paid</option>
                            <option value="3" <?= $status==3?'selected':'' ?>>Completed</option>
                        </select>
                    </label>
                    <label style="font-weight:500;">Update Delivery Status:
                        <select name="new_delivery_status">
                            <option value="0" <?= $dstatus==0?'selected':'' ?>>Not shipped</option>
                            <option value="1" <?= $dstatus==1?'selected':'' ?>>Shipped</option>
                            <option value="2" <?= $dstatus==2?'selected':'' ?>>In transit</option>
                            <option value="3" <?= $dstatus==3?'selected':'' ?>>Delivered</option>
                        </select>
                    </label><br>
                    <button type="submit" class="action-btn">Send Response</button>
                </form>
                <?php if (!empty($row['design_image'])): ?>
                    <div style="margin-top:10px;"><strong>Design Image:</strong><br><img src="uploads/<?= htmlspecialchars($row['design_image']) ?>" style="max-width:200px;border-radius:8px;"></div>
                <?php endif; ?>
                <form method="post" action="staff_update_order.php" style="margin-top:10px;">
                    <input type="hidden" name="oid" value="<?= $row['oid'] ?>">
                    <label>Mark as Error/Return:
                        <select name="is_error">
                            <option value="0" <?= $row['ostatus'] == 9 ? '' : 'selected' ?>>No</option>
                            <option value="1" <?= $row['ostatus'] == 9 ? 'selected' : '' ?>>Yes</option>
                        </select>
                    </label>
                    <input type="text" name="error_reason" placeholder="Reason/Feedback" value="">
                    <label style="margin-left:20px;">Delivery Status:
                        <select name="new_delivery_status">
                            <option value="0" <?= $row['delivery_status']==0?'selected':'' ?>>Not shipped</option>
                            <option value="1" <?= $row['delivery_status']==1?'selected':'' ?>>Shipped</option>
                            <option value="2" <?= $row['delivery_status']==2?'selected':'' ?>>In transit</option>
                            <option value="3" <?= $row['delivery_status']==3?'selected':'' ?>>Delivered</option>
                        </select>
                    </label>
                    <button type="submit">Update</button>
                </form>
            </div>
            <?php else: ?>
            <div class="order-detail">
                <div style="margin-bottom:8px;"><strong>Interaction History:</strong></div>
                <div style="background:#f6f6f6;border-radius:8px;padding:12px 16px;max-height:220px;overflow-y:auto;">
                    <?php
                    // Show all feedback for this order
                    $fb_res = $conn->query("SELECT * FROM feedback WHERE oid=$oid ORDER BY created_at ASC");
                    if ($fb_res && $fb_res->num_rows > 0) {
                        while($fb = $fb_res->fetch_assoc()) {
                            echo '<div style=\'margin-bottom:10px;\'><span style=\'color:#2a7ae2;font-weight:500;\'>Customer:</span> '.nl2br(htmlspecialchars($fb['feedback_text']));
                            if (isset($fb['rating']) && $fb['rating'] > 0) {
                                echo ' <span style=\'color:#f5b301;font-size:1.1em;\'>';
                                for ($i = 0; $i < $fb['rating']; $i++) echo '★';
                                for ($i = $fb['rating']; $i < 5; $i++) echo '☆';
                                echo '</span>';
                            }
                            echo ' <span style=\'color:#888;font-size:0.95em;\'>('.$fb['created_at'].')</span></div>';
                        }
                    }
                    // Show all staff_response for this order
                    $sr_res = $conn->query("SELECT sr.*, s.sname FROM staff_response sr LEFT JOIN staff s ON sr.sid=s.sid WHERE sr.oid=$oid ORDER BY sr.created_at ASC");
                    if ($sr_res && $sr_res->num_rows > 0) {
                        while($sr = $sr_res->fetch_assoc()) {
                            echo '<div style=\'margin-bottom:10px;\'><span style=\'color:#1a7a1a;font-weight:500;\'>Designer '.htmlspecialchars($sr['sname']).':</span> '.nl2br(htmlspecialchars($sr['response_text'])).' <span style=\'color:#888;font-size:0.95em;\'>('.$sr['created_at'].')</span>';
                            if (!empty($sr['design_image'])) {
                                echo '<br><img src=\'uploads/'.htmlspecialchars($sr['design_image']).'\' style=\'max-width:120px;border-radius:8px;margin-top:4px;\'>';
                            }
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>
        </td></tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <div class="order-empty">No orders found!</div>
    <?php endif; ?>
</div>
<script>
function toggleDetail(id) {
    var el = document.getElementById(id);
    if (el.style.display === 'none') el.style.display = '';
    else el.style.display = 'none';
}
</script>
</body>
</html> 