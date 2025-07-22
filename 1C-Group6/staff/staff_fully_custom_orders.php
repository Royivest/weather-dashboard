<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
// Database connection settings
$hostname = "127.0.0.1";
$database = "projectdb";
$username = "root";
$password = "";
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    // If database connection fails, stop execution
    die("Connection failed: " . mysqli_connect_error());
}
// Query to fetch all fully customized orders, including customer info and customization description
$sql = "SELECT o.*, c.customize_desc, cu.cname FROM orders o JOIN customize c ON o.oid = c.oid LEFT JOIN customer cu ON o.cid = cu.cid WHERE (o.pid IS NULL OR o.pid = 0) ORDER BY o.odate DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fully Customized Orders - Staff</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        body { background: #4267b2; font-family: 'Roboto', Arial, sans-serif; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 40px 30px 30px 30px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        h1 { text-align: center; margin-bottom: 30px; }
        .order-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .order-table th, .order-table td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        .order-table th { background: #f5f5f5; }
        .order-empty { text-align: center; font-size: 1.3em; color: #444; margin: 40px 0; }
        .back-link { display: inline-block; margin-bottom: 18px; color: #29487d; text-decoration: underline; font-weight: 500; }
        .back-link:hover { color: #4267b2; }
    </style>
</head>
<body>
    <div class="container">
        <a class="back-link" href="home.php">&larr; Back to Home</a>
        <h1>Fully Customized Orders</h1>
        <?php
        // Check if there are any fully customized orders to display
        if (mysqli_num_rows($result) > 0) {
            echo '<table class="order-table">';
            echo '<tr><th>Order ID</th><th>Date</th><th>Customer</th><th>Description</th><th>Status</th></tr>';
            while($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                echo '<td>' . $row['oid'] . '</td>';
                echo '<td>' . $row['odate'] . '</td>';
                echo '<td>' . htmlspecialchars($row['cname']) . '</td>';
                echo '<td>' . htmlspecialchars($row['customize_desc']) . '</td>';
                echo '<td>';
                // Business logic: show negotiation/quote status for each order
                if (is_null($row['quote_accepted']) && intval($row['quote_round']) === 0) {
                    // Awaiting staff's first quote
                    echo 'Awaiting staff quote';
                } elseif (is_null($row['quote_accepted']) && intval($row['quote_round']) === 1) {
                    // Awaiting staff's quote after customer counter-offer
                    echo 'Awaiting staff quote (after customer counter-offer)';
                } elseif ($row['quote_accepted'] == 1) {
                    // Customer accepted the quote, waiting for payment
                    echo 'Quote accepted, please wait for payment';
                } elseif ($row['quote_accepted'] == 0 && intval($row['quote_round']) === 1) {
                    // Customer rejected the second quote, order will be deleted
                    echo 'Customer rejected second quote, order will be deleted.';
                } else {
                    // Other/unexpected status
                    echo 'Other';
                }
                echo '</td>';
                echo '</tr>';
                // Show quote form for staff if quote_accepted is NULL (negotiation ongoing)
                if (is_null($row['quote_accepted'])) {
                    // If this is a counter-offer (quote_round == 1), show customer expectations
                    if (intval($row['quote_round']) === 1) {
                        echo '<tr><td colspan="5" style="background:#f9f9f9;">';
                        echo '<b>Customer Counter-Offer:</b><br>';
                        echo 'Expected Budget: $' . number_format($row['customer_expected_budget'], 2) . ' USD<br>';
                        echo 'Expected Delivery Date: ' . htmlspecialchars($row['customer_expected_date']) . '<br>';
                        echo '</td></tr>';
                    }
                    // Staff quote/response form
                    echo '<tr><td colspan="5" style="background:#f9f9f9;">';
                    echo '<form method="post" action="staff_update_order.php" enctype="multipart/form-data" style="margin:0;">';
                    echo '<input type="hidden" name="oid" value="' . $row['oid'] . '">';
                    echo '<textarea name="staff_response" rows="2" placeholder="Reply to customer..." style="width:90%;margin-bottom:8px;"></textarea><br>';
                    echo '<label>Upload Design Image: <input type="file" name="design_image" accept="image/*"></label><br>';
                    echo '<input type="number" name="quote_value" step="0.01" placeholder="Quote (USD)" style="width:120px;margin-bottom:8px;"> ';
                    // Show already sent quote and design image if present
                    if (!empty($row['current_quote_value'])) {
                        echo '<div style="margin:8px 0;color:#1a7a1a;font-weight:bold;">Quoted: $' . number_format($row['current_quote_value'], 2) . '</div>';
                    }
                    if (!empty($row['design_image'])) {
                        echo '<div style="margin:8px 0;"><a href="uploads/' . htmlspecialchars($row['design_image']) . '" target="_blank">View Design Image</a></div>';
                    }
                    echo '<input type="date" name="current_estimated_date" style="margin-bottom:8px;"> <br>';
                    echo '<button type="submit" class="action-btn" style="margin-top:8px;">Send Quote</button>';
                    echo '</form>';
                    echo '</td></tr>';
                }
                // Show staff and customer interaction history for this order
                echo '<tr><td colspan="5" style="background:#f9f9f9;text-align:left;">';
                echo '<strong>Staff Interaction History:</strong>';
                echo '<div style="margin-top:8px;">';
                $sr_res = mysqli_query($conn, "SELECT sr.*, s.sname FROM staff_response sr LEFT JOIN staff s ON sr.sid=s.sid WHERE sr.oid=" . $row['oid'] . " ORDER BY sr.created_at ASC");
                if ($sr_res && mysqli_num_rows($sr_res) > 0) {
                    while($sr = mysqli_fetch_assoc($sr_res)) {
                        echo '<div style="margin-bottom:10px;">';
                        echo '<span style="color:#1a7a1a;font-weight:500;">Designer ' . htmlspecialchars($sr['sname']) . ':</span> ';
                        echo nl2br(htmlspecialchars($sr['response_text']));
                        if (!empty($sr['design_image'])) {
                            echo '<br><a href="uploads/' . htmlspecialchars($sr['design_image']) . '" target="_blank">View Design Image</a>';
                        }
                        echo ' <span style="color:#888;font-size:0.95em;">(' . $sr['created_at'] . ')</span>';
                        echo '</div>';
                    }
                } else {
                    echo '<span style="color:#888;">No staff replies yet.</span>';
                }
                echo '</div>';
                // Show customer feedback history for this order
                echo '<div style="margin-top:16px;"><strong>Customer Feedback History:</strong><div style="margin-top:8px;">';
                $fb_res = mysqli_query($conn, "SELECT * FROM feedback WHERE oid=" . $row['oid'] . " ORDER BY created_at ASC");
                if ($fb_res && mysqli_num_rows($fb_res) > 0) {
                    while($fb = mysqli_fetch_assoc($fb_res)) {
                        echo '<div style="margin-bottom:10px;">';
                        echo '<span style="color:#2a7ae2;font-weight:500;">Customer:</span> ';
                        echo nl2br(htmlspecialchars($fb['feedback_text']));
                        if (isset($fb['rating']) && $fb['rating'] > 0) {
                            echo ' <span style="color:#f5b301;font-size:1.1em;">';
                            for ($i = 0; $i < $fb['rating']; $i++) echo '★';
                            for ($i = $fb['rating']; $i < 5; $i++) echo '☆';
                            echo '</span>';
                        }
                        echo ' <span style="color:#888;font-size:0.95em;">(' . $fb['created_at'] . ')</span>';
                        echo '</div>';
                    }
                } else {
                    echo '<span style="color:#888;">No customer feedback yet.</span>';
                }
                echo '</div></div></td></tr>';
            }
            echo '</table>';
        } else {
            // If no fully customized orders, show message
            echo '<div class="order-empty">No fully customized orders found!</div>';
        }
        // Close the database connection at the end
        mysqli_close($conn);
        ?>
    </div>
</body>
</html> 