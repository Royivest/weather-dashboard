<?php
/**
 * Order view page
 * This page displays all customer orders and provides payment, delete, design confirmation, feedback, and other interactive features
 * Includes session validation, database queries, order operations, and interaction history display
 *
 * Business logic:
 * - Only logged-in customers can access this page (session validation)
 * - Customers can view, pay for, delete, and give feedback on their orders
 * - Handles both normal/half-customized and fully customized orders
 * - Provides feedback and staff interaction history for each order
 */
// view_order.php
session_start();

// Session validation: Only allow access if customer is logged in
if (!isset($_SESSION['customerID'])) {
    // If not logged in, redirect to login page
    header("Location: index.php?error=session");
    exit;
}

$customerID = $_SESSION['customerID']; // Get the logged-in customer ID

// Database connection settings
$hostname = "127.0.0.1";
$database = "projectdb"; // Database name
$username = "root";
$password = "";

// Connect to the database
$conn = mysqli_connect($hostname, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// --- Payment POST handler ---
// Handles payment for an order. Also checks for fully custom order quote acceptance and material stock.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_oid'])) {
  $oid = intval($_POST['pay_oid']);
  // Retrieve the order to check type and status
  $order_res = mysqli_query($conn, "SELECT * FROM orders WHERE oid=$oid AND cid=$customerID");
  if ($order_row = mysqli_fetch_assoc($order_res)) {
    $is_fully_custom = (empty($order_row['pid']) || intval($order_row['pid']) === 0);
    // If fully custom and not yet accepted, auto-accept the quote
    if ($is_fully_custom && intval($order_row['quote_accepted']) !== 1) {
      mysqli_query($conn, "UPDATE orders SET quote_accepted=1 WHERE oid=$oid AND cid=$customerID");
    }
    $mat_id = isset($order_row['material_selected']) ? intval($order_row['material_selected']) : null;
    $oqty = intval($order_row['oqty']);
    $error = false;
    // If material is selected, check stock
    if ($mat_id) {
      $mat_res = mysqli_query($conn, "SELECT mqty FROM material WHERE mid=$mat_id");
      if (!$mat_res || !($mat_row = mysqli_fetch_assoc($mat_res))) {
        $error = true; // Material not found
      } else if ($mat_row['mqty'] < $oqty) {
        $error = true; // Not enough stock
      }
    }
    if ($error) {
      // Mark order as error status if material is missing or insufficient
      mysqli_query($conn, "UPDATE orders SET ostatus=9 WHERE oid=$oid");
      echo '<script>alert("Order error: material missing or not enough stock. Please contact staff.");window.location.href="view_order.php";</script>';
      exit;
    }
  }
  // Get payment currency and amount from POST
  $pay_currency = isset($_POST['pay_currency']) ? mysqli_real_escape_string($conn, $_POST['pay_currency']) : '';
  $pay_amount = isset($_POST['pay_amount']) ? floatval($_POST['pay_amount']) : 0;
  // Update order status to paid and record payment info
  $sql_pay = "UPDATE orders SET ostatus=2, pay_currency='$pay_currency', pay_amount=$pay_amount WHERE oid=$oid AND cid=$customerID";
  if (!mysqli_query($conn, $sql_pay)) {
      error_log("SQL error: " . mysqli_error($conn));
  }
  // Redirect to refresh the page
  echo '<script>window.location.href="http://localhost/1C-Group6/customer/view_order.php";</script>';
  exit;
}
// --- Delete order POST handler ---
// Allows customer to delete an order if it is still processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_oid'])) {
  $oid = intval($_POST['delete_oid']);
  $sql_del = "DELETE FROM orders WHERE oid=$oid AND ostatus=1 AND cid=$customerID";
  mysqli_query($conn, $sql_del);
  echo '<script>window.location.href="http://localhost/1C-Group6/customer/view_order.php";</script>';
  exit;
}
// --- Feedback POST handler ---
// Allows customer to submit feedback and rating for an order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['feedback_text']) && isset($_POST['oid']) && isset($_POST['rating'])) {
    $oid = intval($_POST['oid']);
    $feedback_text = mysqli_real_escape_string($conn, trim($_POST['feedback_text']));
    $rating = intval($_POST['rating']);
    // Only insert feedback if text is not empty and rating is valid
    if ($feedback_text !== '' && $rating >= 1 && $rating <= 5) {
        $sql_fb = "INSERT INTO feedback (oid, feedback_text, rating, created_at) VALUES ($oid, '$feedback_text', $rating, NOW())";
        mysqli_query($conn, $sql_fb);
    }
    echo '<script>window.location.href="view_order.php";</script>';
    exit;
}

// --- Quote accept/reject handlers for fully custom orders ---
// Accept quote: set quote_accepted=1
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_quote_oid'])) {
    $oid = intval($_POST['accept_quote_oid']);
    mysqli_query($conn, "UPDATE orders SET quote_accepted=1 WHERE oid=$oid AND cid=$customerID");
    echo "<script>alert('Quote accepted. Please proceed to payment.');window.location.href='view_order.php';</script>";
    exit;
}
// Reject quote: if first time, update expectations and set quote_round=1; if second time, delete order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reject_quote_oid'])) {
    $oid = intval($_POST['reject_quote_oid']);
    // Get current quote_round
    $order_res = mysqli_query($conn, "SELECT quote_round FROM orders WHERE oid=$oid AND cid=$customerID");
    $order_row = mysqli_fetch_assoc($order_res);
    $round = $order_row ? intval($order_row['quote_round']) : 0;
    if ($round === 0) {
        // First rejection: update customer expectations and set quote_round=1
        $expected_budget = isset($_POST['customer_expected_budget']) ? floatval($_POST['customer_expected_budget']) : null;
        $expected_date = isset($_POST['customer_expected_date']) ? $_POST['customer_expected_date'] : null;
        mysqli_query($conn, "UPDATE orders SET quote_accepted=0, customer_expected_budget=$expected_budget, customer_expected_date='" . mysqli_real_escape_string($conn, $expected_date) . "', quote_round=1 WHERE oid=$oid AND cid=$customerID");
        echo "<script>alert('Counter-offer sent to staff.');window.location.href='view_order.php';</script>";
    } else {
        // Second rejection: delete order
        mysqli_query($conn, "DELETE FROM orders WHERE oid=$oid AND cid=$customerID");
        echo "<script>alert('Order deleted due to rejection of second quote.');window.location.href='view_order.php';</script>";
    }
    exit;
}

// --- Query for normal/half-customized orders ---
$sql = "SELECT * FROM orders WHERE cid = $customerID AND pid IS NOT NULL AND pid != 0 ORDER BY odate DESC";
$result = mysqli_query($conn, $sql);

// --- Query for fully customized orders ---
$sql_custom = "SELECT * FROM orders WHERE cid = $customerID AND (pid IS NULL OR pid = 0) ORDER BY odate DESC";
$result_custom = mysqli_query($conn, $sql_custom);

// --- Design confirmation handler ---
// Allows customer to confirm the design for a fully customized order
if (isset($_POST['confirm_design']) && isset($_POST['confirm_oid'])) {
    $confirm_oid = intval($_POST['confirm_oid']);
    $update_sql = "UPDATE orders SET ostatus=2 WHERE oid=$confirm_oid AND cid=$customerID";
    mysqli_query($conn, $update_sql);
    // Optionally, add a success message or redirect
    echo '<script>window.location.href = window.location.href;</script>';
    exit();
}

// --- Helper function: getDeliveryStatusText ---
// Returns a human-readable delivery status string based on status code
function getDeliveryStatusText($status) {
    $map = [0 => 'Not shipped', 1 => 'Shipped', 2 => 'In transit', 3 => 'Delivered'];
    return isset($map[$status]) ? $map[$status] : 'Unknown';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - Smile & Sunshine Toy Co. Ltd</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        /* Navigation bar, table, and modal styles for better UI/UX */
        .navbar {
            display: flex;
            justify-content: center;
            align-items: center;
            background: #fff;
            padding: 20px 0 10px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        .navbar a {
            color: #234;
            text-decoration: none;
            font-weight: bold;
            margin: 0 30px;
            font-size: 1.2em;
            transition: color 0.2s;
        }
        .navbar a:hover {
            color: #2a7ae2;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .order-table th, .order-table td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        .order-table th {
            background: #f5f5f5;
        }
        .order-empty {
            text-align: center;
            font-size: 1.3em;
            color: #444;
            margin: 40px 0;
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 16px;
            padding: 40px 30px 30px 30px;
            box-shadow: 0 2px 16px rgba(0,0,0,0.08);
        }
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
            text-align: center;
        }
        .feedback-modal { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:999; align-items:center; justify-content:center; }
        .feedback-modal.active { display:flex; }
        .feedback-modal .modal-content { background:#fff; padding:30px 20px; border-radius:12px; max-width:400px; margin:60px auto; box-shadow:0 4px 24px rgba(0,0,0,0.18); }
        .order-btn { background:#3a5; color:#fff; border:none; border-radius:6px; padding:8px 18px; margin:4px 0; font-weight:bold; cursor:pointer; width:auto; min-width:120px; }
        .order-btn:hover { background:#2a7ae2; }
        .toast {
            visibility: hidden;
            min-width: 220px;
            background-color: #333;
            color: #fff;
            text-align: center;
            border-radius: 8px;
            padding: 16px;
            position: fixed;
            z-index: 2000;
            left: 50%;
            bottom: 40px;
            font-size: 1.1em;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.4s, visibility 0.4s;
        }
        .toast.show {
            visibility: visible;
            opacity: 1;
        }
    </style>
</head>
<body>
    <!-- Navigation bar for customer pages -->
    <div class="navbar">
      <a href="home.php">Home</a>
      <a href="create_order.php">Create Order</a>
      <a href="view_order.php">View Orders</a>
      <a href="update_profile.php">Update Profile</a>
    </div>

    <div class="container">
        <div class="header">
            <h1>Your Orders</h1>
        </div>
        <?php
        /**
         * Display normal/half-customized orders table and interactive buttons
         * Includes payment, delete, feedback, and interaction history
         */
        if (mysqli_num_rows($result) > 0): ?>
        <table class="order-table">
          <tr>
            <th>Order ID</th>
            <th>Date</th>
            <th>Product ID</th>
            <th>Qty</th>
            <th>Amount ($)</th>
            <th>Status</th>
            <th>Delivery Status</th>
            <th>Action</th>
          </tr>
          <?php while($row = mysqli_fetch_assoc($result)): ?>
            <?php
              $oid = $row['oid'];
              $is_paid = (isset($row['ostatus']) && intval($row['ostatus']) === 2);
              $is_completed = (isset($row['ostatus']) && intval($row['ostatus']) === 3);
              $can_pay = (isset($row['ostatus']) && intval($row['ostatus']) === 1 && floatval($row['ocost']) > 0);
              $status_text = ($row['ostatus'] == 1 ? 'Processing' : ($row['ostatus'] == 2 ? 'Paid' : ($row['ostatus'] == 3 ? 'Completed' : 'Other')));
            ?>
            <tr>
              <td><?= $row['oid'] ?></td>
              <td><?= $row['odate'] ?></td>
              <td><?= $row['pid'] ?></td>
              <td><?= $row['oqty'] ?></td>
              <td>$<?= number_format($row['ocost'], 2) ?></td>
              <td><?= $status_text ?></td>
              <td><?= isset($row['delivery_status']) ? getDeliveryStatusText($row['delivery_status']) : '' ?></td>
              <td>
                <?php if ($can_pay): ?>
                  <!-- Show Pay and Delete buttons if order is processing and has a cost -->
                  <button type="button" class="order-btn" onclick="openPayModal(<?= $oid ?>, <?= floatval($row['ocost']) ?>)">Pay</button>
                  <button type="button" class="order-btn" style="background:#d9534f;" onclick="openDeleteModal(<?= $oid ?>)">Delete</button>
                <?php endif; ?>
                <?php if ($is_paid || $is_completed): ?>
                  <!-- Show Feedback button if order is paid or completed -->
                  <button type="button" class="order-btn" onclick="openFeedbackModal(<?= $oid ?>)">Give Feedback</button>
                <?php endif; ?>
              </td>
            </tr>
            <!-- Interaction History for this order -->
            <tr>
              <td colspan="8" style="background:#f9f9f9;text-align:left;">
                <strong>Interaction History:</strong>
                <div style="margin-top:8px;">
                  <?php
                  // Display all feedback for this order
                  $fb_res = mysqli_query($conn, "SELECT * FROM feedback WHERE oid=$oid ORDER BY created_at ASC");
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
                  ?>
                </div>
              </td>
            </tr>
          <?php endwhile; ?>
        </table>
        <?php else: ?>
          <div class="order-empty">No normal or half-customized orders found!</div>
        <?php endif; ?>

        <!-- Fully Customized Orders table at the bottom of the page -->
        <div style="background:#fff;border-radius:16px;box-shadow:0 2px 16px rgba(0,0,0,0.08);padding:30px 20px;margin-top:40px;">
          <h2 style="text-align:center;">Your Fully Customized Orders</h2>
          <?php if (mysqli_num_rows($result_custom) > 0): ?>
          <table class="order-table">
            <tr>
              <th>Order ID</th>
              <th>Date</th>
              <th>Qty</th>
              <th>Customer Budget ($)</th>
              <th>Staff Quote ($)</th>
              <th>Design Image</th>
              <th>Amount ($)</th>
              <th>Status</th>
              <th>Delivery Status</th>
              <th>Action</th>
            </tr>
            <?php while($row = mysqli_fetch_assoc($result_custom)): ?>
              <?php
                $oid = $row['oid'];
                $quote_value = $row['current_quote_value'];
                $customer_budget = $row['customer_expected_budget'] ?? '';
                $design_image = $row['design_image'] ?? '';
                $is_paid = (isset($row['ostatus']) && intval($row['ostatus']) === 2);
                $is_completed = (isset($row['ostatus']) && intval($row['ostatus']) === 3);
                $can_pay = (!is_null($quote_value) && isset($row['ostatus']) && intval($row['ostatus']) === 1);
                $status = ($row['ostatus'] == 1 ? 'Processing' : ($row['ostatus'] == 2 ? 'Paid' : ($row['ostatus'] == 3 ? 'Completed' : 'Other')));
              ?>
              <tr>
                <td><?= $row['oid'] ?></td>
                <td><?= $row['odate'] ?></td>
                <td><?= $row['oqty'] ?></td>
                <td><?= $customer_budget !== '' ? '$' . number_format($customer_budget, 2) : '-' ?></td>
                <td><?= !is_null($quote_value) ? '$' . number_format($quote_value, 2) : '-' ?></td>
                <td>
                  <?php if (!empty($design_image)): ?>
                    <a href="staff/uploads/<?= htmlspecialchars($design_image) ?>" target="_blank">View</a>
                  <?php else: ?>
                    -
                  <?php endif; ?>
                </td>
                <td>$<?= number_format($row['ocost'], 2) ?></td>
                <td><?= $status ?></td>
                <td><?= isset($row['delivery_status']) ? getDeliveryStatusText($row['delivery_status']) : '' ?></td>
                <td>
                  <?php if ($can_pay): ?>
                    <!-- Show Pay and Cancel buttons if quote is available and order is processing -->
                    <button type="button" class="order-btn" onclick="openPayModal(<?= $oid ?>, <?= $quote_value ?>)">Pay $<?= number_format($quote_value, 2) ?></button>
                    <button type="button" class="order-btn" style="background:#d9534f;" onclick="openDeleteModal(<?= $oid ?>)">Cancel</button>
                  <?php endif; ?>
                  <?php if ($is_paid || $is_completed): ?>
                    <!-- Show Feedback button if order is paid or completed -->
                    <button type="button" class="order-btn" onclick="openFeedbackModal(<?= $oid ?>)">Give Feedback</button>
                  <?php endif; ?>
                </td>
              </tr>
              <!-- Staff Interaction History for this order -->
              <tr>
                <td colspan="10" style="background:#f9f9f9;text-align:left;">
                  <strong>Staff Interaction History:</strong>
                  <div style="margin-top:8px;">
                    <?php
                    // Display all staff responses for this order
                    $sr_res = mysqli_query($conn, "SELECT sr.*, s.sname FROM staff_response sr LEFT JOIN staff s ON sr.sid=s.sid WHERE sr.oid=$oid ORDER BY sr.created_at ASC");
                    if ($sr_res && mysqli_num_rows($sr_res) > 0) {
                    while($sr = mysqli_fetch_assoc($sr_res)) {
                        echo '<div style="margin-bottom:10px;">';
                        echo '<span style="color:#1a7a1a;font-weight:500;">Designer ' . htmlspecialchars($sr['sname']) . ':</span> ';
                        echo nl2br(htmlspecialchars($sr['response_text']));
                        if (!empty($sr['design_image'])) {
                          echo '<br><a href="../staff/uploads/' . htmlspecialchars($sr['design_image']) . '" target="_blank">View Design Image</a>';
                        }
                        echo ' <span style="color:#888;font-size:0.95em;">(' . $sr['created_at'] . ')</span>';
                        echo '</div>';
                    }
                    } else {
                      echo '<span style="color:#888;">No staff replies yet.</span>';
                    }
                    ?>
                  </div>
                  <!-- Customer Feedback History for this order -->
                  <div style="margin-top:16px;">
                    <strong>Customer Feedback History:</strong>
                    <div style="margin-top:8px;">
                      <?php
                      // Display all feedback for this order
                      $fb_res = mysqli_query($conn, "SELECT * FROM feedback WHERE oid=$oid ORDER BY created_at ASC");
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
                      ?>
                    </div>
                  </div>
                </td>
              </tr>
            <?php endwhile; ?>
          </table>
          <?php else: ?>
            <div class="order-empty">No fully customized orders found!</div>
          <?php endif; ?>
        </div>
    </div>

    <!-- Feedback modal for submitting feedback on an order -->
    <div id="feedbackModal" class="feedback-modal"><div class="modal-content"><h3>Give Feedback</h3><form id="feedbackForm" method="POST" action="view_order.php"><input type="hidden" name="oid" id="feedback_oid"><textarea name="feedback_text" id="feedback_text" rows="4" style="width:100%" required></textarea><br><div style="margin:10px 0;">Rating: <span id="starContainer">
      <label><input type="radio" name="rating" value="1" required>★</label>
      <label><input type="radio" name="rating" value="2">★★</label>
      <label><input type="radio" name="rating" value="3">★★★</label>
      <label><input type="radio" name="rating" value="4">★★★★</label>
      <label><input type="radio" name="rating" value="5">★★★★★</label>
    </span></div><button type="submit" class="order-btn">Submit</button> <button type="button" class="order-btn" onclick="closeFeedbackModal()">Cancel</button></form></div></div>
    <script>
    // Feedback modal open/close logic and validation
    function openFeedbackModal(oid) {
      document.getElementById('feedback_oid').value = oid;
      document.getElementById('feedbackModal').classList.add('active');
    }
    function closeFeedbackModal() {
      document.getElementById('feedbackModal').classList.remove('active');
    }
    document.getElementById('feedbackForm').onsubmit = function(e) {
      if(document.getElementById('feedback_text').value.trim().length < 5) { showToast('Please enter at least 5 characters.'); e.preventDefault(); return false; }
      var rating = document.querySelector('input[name="rating"]:checked');
      if(!rating) { showToast('Please select a rating.'); e.preventDefault(); return false; }
    };

    // --- Payment modal and stock check logic ---
    function openPayModal(oid, amount) {
      document.getElementById('pay_oid').value = oid;
      document.getElementById('pay_amount').value = amount;
      document.getElementById('pay_currency').value = 'USD';
      document.getElementById('payModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    function closePayModal() {
      document.getElementById('payModal').classList.remove('active');
      document.body.style.overflow = '';
    }
    document.getElementById('payForm').onsubmit = function(e) {
      var btn = document.getElementById('confirmPayBtn');
      btn.disabled = true;
      btn.innerText = 'Processing...';
      // If currency conversion is used, update pay_amount and pay_currency
      var resultSpan = document.getElementById('converted_result').innerText;
      var match = resultSpan.match(/Converted: ([\d\.]+) (\w+)/);
      if (match) {
        document.getElementById('pay_amount').value = match[1];
        document.getElementById('pay_currency').value = match[2];
      }
      // Double-check stock before submitting payment
      if (oid) {
        e.preventDefault();
        fetch('get_order_info.php?oid=' + oid)
          .then(r => r.json())
          .then(data => {
            if (data.result === 'ok') {
              fetch('get_product_stock.php?pid=' + data.pid)
                .then(r2 => r2.json())
                .then(stockData => {
                  if (stockData.result === 'ok' && stockData.stock >= data.oqty) {
                    document.getElementById('payForm').onsubmit = null;
                    document.getElementById('payForm').submit();
                  } else {
                    showToast('Sorry, not enough stock. Please contact staff.');
                    btn.disabled = false;
                    btn.innerText = 'Confirm Payment';
                  }
                });
            } else {
              showToast('Order not found.');
              btn.disabled = false;
              btn.innerText = 'Confirm Payment';
            }
          });
      }
    };
    </script>

    <!-- Delete confirmation modal for deleting an order -->
    <div id="deleteModal" class="feedback-modal"><div class="modal-content"><h3>Delete Order</h3><p>Are you sure you want to delete this order?</p><form id="deleteForm" method="POST" action="http://localhost/1C-Group6/customer/view_order.php"><input type="hidden" name="delete_oid" id="delete_oid"><button type="submit" class="order-btn" style="background:#d9534f;">Delete</button> <button type="button" class="order-btn" onclick="closeDeleteModal()">Cancel</button></form></div></div>
    <script>
    // Delete modal open/close logic
    function openDeleteModal(oid) {
      document.getElementById('delete_oid').value = oid;
      document.getElementById('deleteModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').classList.remove('active');
      document.body.style.overflow = '';
    }
    </script>

    <!-- Toast notification for showing messages to the user -->
    <div id="toast" class="toast"></div>
    <script>
    function showToast(msg) {
      var toast = document.getElementById('toast');
      toast.innerText = msg;
      toast.className = 'toast show';
      setTimeout(function(){ toast.className = 'toast'; }, 2500);
    }
    </script>

    <!-- Payment modal for confirming payment on an order -->
    <div id="payModal" class="feedback-modal">
      <div class="modal-content">
        <h3>Confirm Payment</h3>
        <div id="currency-info"></div>
        <form id="payForm" method="POST" action="http://localhost/1C-Group6/customer/view_order.php">
          <input type="hidden" name="pay_oid" id="pay_oid">
          <input type="hidden" name="pay_amount" id="pay_amount">
          <input type="hidden" name="pay_currency" id="pay_currency" value="USD">
          <button type="submit" class="order-btn" id="confirmPayBtn">Confirm Pay (USD)</button>
          <button type="button" class="order-btn" onclick="closePayModal()">Cancel</button>
        </form>
      </div>
    </div>
    <script>
    // Currency conversion rates and payment modal logic
    const rates = { HKD: 7.8, JPY: 155, EUR: 0.92 };
    let currentPayOid = null;
    function openPayModal(oid, amount) {
      currentPayOid = oid;
      let html = `<b>Amount:</b> $${amount.toFixed(2)} USD<br>`;
      html += `<b>HKD:</b> $${(amount * rates.HKD).toFixed(2)}<br>`;
      html += `<b>JPY:</b> ¥${(amount * rates.JPY).toFixed(0)}<br>`;
      html += `<b>EUR:</b> €${(amount * rates.EUR).toFixed(2)}<br>`;
      document.getElementById('currency-info').innerHTML = html;
      document.getElementById('pay_oid').value = oid;
      document.getElementById('pay_amount').value = amount;
      document.getElementById('pay_currency').value = 'USD';
      document.getElementById('payModal').classList.add('active');
      document.body.style.overflow = 'hidden';
    }
    function closePayModal() {
      document.getElementById('payModal').classList.remove('active');
      document.body.style.overflow = '';
    }
    </script>
</body>
</html>
<?php
// Close the database connection at the end of the script
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>
