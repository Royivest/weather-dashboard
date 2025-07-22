<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// --- System Summary Reports ---
// Get total number of products in the system (for business scale overview)
$product_count = $conn->query("SELECT COUNT(*) AS cnt FROM product")->fetch_assoc()['cnt'];
// Get total number of materials in the system (for supply chain overview)
$material_count = $conn->query("SELECT COUNT(*) AS cnt FROM material")->fetch_assoc()['cnt'];
// Get total number of orders placed (for sales activity overview)
$order_count = $conn->query("SELECT COUNT(*) AS cnt FROM orders")->fetch_assoc()['cnt'];
// Get total sales amount (for revenue analysis)
$total_sales = $conn->query("SELECT SUM(ocost) AS total FROM orders")->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Generate Report</title>
    <link rel="stylesheet" href="../css/view_order_styles.css">
    <style>
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 40px 30px 30px 30px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        h1 { color: #29487d; margin-bottom: 1em; text-align:center; }
        .back-link { display: inline-block; margin-bottom: 1.5em; color: #4267b2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        .report-box { background:#f7f7fa; border-radius:12px; padding:24px 18px; margin-bottom:18px; box-shadow:0 2px 8px rgba(0,0,0,0.04); font-size:1.2em; }
        .report-label { color:#29487d; font-weight:600; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="home.php">&larr; Back to Home</a>
    <h1>System Report</h1>
    <!-- System summary: shows total counts for products, materials, orders, and total sales -->
    <div class="report-box"><span class="report-label">Total Products:</span> <?= $product_count ?></div>
    <div class="report-box"><span class="report-label">Total Materials:</span> <?= $material_count ?></div>
    <div class="report-box"><span class="report-label">Total Orders:</span> <?= $order_count ?></div>
    <div class="report-box"><span class="report-label">Total Sales (USD):</span> $<?= number_format($total_sales, 2) ?></div>
    
    <!-- Material Inventory Status: shows current stock and highlights low stock materials -->
    <div class="report-box">
        <span class="report-label">Material Inventory Status:</span><br>
        <ul style="margin:10px 0 0 18px;padding:0;list-style:disc;">
        <?php
        // For each material, show name, quantity, unit, reorder level, and low stock warning
        $mat_res = $conn->query("SELECT mname, mqty, munit, mreorderqty FROM material ORDER BY mname");
        if ($mat_res && $mat_res->num_rows > 0) {
            while($mat = $mat_res->fetch_assoc()) {
                $low = ($mat['mqty'] < $mat['mreorderqty']); // Business rule: low stock if below reorder level
                echo '<li>' . htmlspecialchars($mat['mname']) . ': ' . $mat['mqty'] . ' ' . htmlspecialchars($mat['munit']) .
                     ' (Reorder Level: ' . $mat['mreorderqty'] . ')' .
                     ($low ? ' <span style="color:#d9534f;font-weight:bold;">&#9888; Low Stock!</span>' : '') .
                     '</li>';
            }
        } else {
            echo '<li>No materials found.</li>';
        }
        ?>
        </ul>
    </div>
    <!-- Product Inventory Status: shows current stock for each product, highlights low stock -->
    <div class="report-box">
        <span class="report-label">Product Inventory Status:</span><br>
        <ul style="margin:10px 0 0 18px;padding:0;list-style:disc;">
        <?php
        // For each product, calculate stock as the minimum possible based on material composition
        $prod_res = $conn->query("SELECT pid, pname FROM product ORDER BY pname");
        if ($prod_res && $prod_res->num_rows > 0) {
            while($prod = $prod_res->fetch_assoc()) {
                $pid = intval($prod['pid']);
                // Stock is the minimum ratio of material stock to required quantity for each material
                $stock_sql = "SELECT MIN(FLOOR(m.mqty / pm.pmqty)) AS stock FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = $pid";
                $stock_result = $conn->query($stock_sql);
                $stock_row = $stock_result ? $stock_result->fetch_assoc() : null;
                $stock = ($stock_row && $stock_row['stock'] !== null) ? intval($stock_row['stock']) : 0;
                echo '<li>' . htmlspecialchars($prod['pname']) . ': ' . $stock .
                     ($stock < 10 ? ' <span style="color:#d9534f;font-weight:bold;">&#9888; Low Stock!</span>' : '') .
                     '</li>';
            }
        } else {
            echo '<li>No products found.</li>';
        }
        ?>
        </ul>
    </div>
    <!-- Inventory Turnover Report: analyzes sales and stock to show turnover rate for each product -->
    <div class="report-box">
        <span class="report-label">Inventory Turnover (Last 30 Days)</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Sold (Last 30 Days)</th><th>Current Stock</th><th>Turnover Rate</th></tr>
            <?php
            // For each product, show sales in last 30 days, current stock, and turnover rate (sold/stock)
            $sql = "SELECT p.pid, p.pname, 
                           COALESCE(SUM(o.oqty),0) AS sold_qty
                    FROM product p
                    LEFT JOIN orders o ON p.pid = o.pid AND o.odate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.pid, p.pname
                    ORDER BY sold_qty DESC";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $pid = intval($row['pid']);
                    // Calculate current stock as before
                    $stock_sql = "SELECT MIN(FLOOR(m.mqty / pm.pmqty)) AS stock FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = $pid";
                    $stock_result = $conn->query($stock_sql);
                    $stock_row = $stock_result ? $stock_result->fetch_assoc() : null;
                    $stock = ($stock_row && $stock_row['stock'] !== null) ? intval($stock_row['stock']) : 0;
                    // Turnover rate: how many times stock is sold in 30 days
                    $turnover = ($stock > 0) ? round($row['sold_qty'] / $stock, 2) : '—';
                    echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>' . $row['sold_qty'] . '</td><td>' . $stock . '</td><td>' . $turnover . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="4">No product data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Obsolete/Unsold Products Report: finds products with no sales in 90+ days -->
    <div class="report-box">
        <span class="report-label">Obsolete/Unsold Products (No Sales in 90+ Days)</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Last Sold Date</th></tr>
            <?php
            // For each product, find the last sold date; if none or >90 days ago, mark as obsolete
            $sql = "SELECT p.pname, MAX(o.odate) AS last_sold
                    FROM product p
                    LEFT JOIN orders o ON p.pid = o.pid
                    GROUP BY p.pid
                    HAVING last_sold IS NULL OR last_sold < DATE_SUB(NOW(), INTERVAL 90 DAY)";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $last = $row['last_sold'] ? $row['last_sold'] : 'Never Sold';
                    echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>' . $last . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2">No obsolete/unsold products found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <h2 style="color:#d9534f;">Error Orders (Material missing or not enough stock)</h2>
    <?php
    // List all orders with error status (ostatus=9), showing feedback if any
    $res = $conn->query("SELECT o.*, f.feedback_text FROM orders o LEFT JOIN feedback f ON o.oid=f.oid WHERE o.ostatus=9 ORDER BY o.odate DESC");
    if ($res && $res->num_rows > 0) {
        echo '<table class="order-table">';
        echo '<tr><th>Order ID</th><th>Date</th><th>Product ID</th><th>Qty</th><th>Customer ID</th><th>Amount</th><th>Feedback</th></tr>';
        while($row = $res->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['oid'] . '</td>';
            echo '<td>' . $row['odate'] . '</td>';
            echo '<td>' . $row['pid'] . '</td>';
            echo '<td>' . $row['oqty'] . '</td>';
            echo '<td>' . $row['cid'] . '</td>';
            echo '<td>$' . number_format($row['ocost'], 2) . '</td>';
            echo '<td>' . htmlspecialchars($row['feedback_text']) . '</td>';
            echo '</tr>';
        }
        echo '</table>';
    } else {
        echo '<div style="color:#888;">No error orders found.</div>';
    }
    ?>
    <!-- Order Status Report: summarizes order counts by status (processing, paid, completed, etc.) -->
    <div class="report-box">
        <span class="report-label">Order Status Report</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Status</th><th>Order Count</th></tr>
            <?php
            // For each order status, count number of orders (for workflow monitoring)
            $status_map = [
                1 => 'Processing',
                2 => 'Paid',
                3 => 'Completed',
                8 => 'Returned',
                9 => 'Error'
            ];
            $sql = "SELECT ostatus, COUNT(*) AS cnt FROM orders GROUP BY ostatus";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $label = isset($status_map[$row['ostatus']]) ? $status_map[$row['ostatus']] : 'Other';
                    echo '<tr><td>' . $label . '</td><td>' . $row['cnt'] . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2">No order data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Customer Order History Report: shows order count, total spent, and last order date for each customer -->
    <div class="report-box">
        <span class="report-label">Customer Order History Report</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Customer Name</th><th>Order Count</th><th>Total Spent</th><th>Last Order Date</th></tr>
            <?php
            // For each customer, show order count, total spent, and last order date (for customer value analysis)
            $sql = "SELECT c.cname, COUNT(o.oid) AS order_count, COALESCE(SUM(o.ocost),0) AS total_spent, MAX(o.odate) AS last_order
                    FROM customer c
                    LEFT JOIN orders o ON c.cid = o.cid
                    GROUP BY c.cid, c.cname";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $last = $row['last_order'] ? $row['last_order'] : '—';
                    echo '<tr><td>' . htmlspecialchars($row['cname']) . '</td><td>' . $row['order_count'] . '</td><td>$' . number_format($row['total_spent'],2) . '</td><td>' . $last . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="4">No customer data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Order Error/Return Report: lists all returned or error orders with reason/feedback -->
    <div class="report-box">
        <span class="report-label">Order Error/Return Report</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Order ID</th><th>Date</th><th>Product ID</th><th>Quantity</th><th>Status</th><th>Reason/Feedback</th></tr>
            <?php
            // For each order with status returned or error, show details and feedback (for quality control)
            $status_map = [8 => 'Returned', 9 => 'Error'];
            $sql = "SELECT o.oid, o.odate, o.pid, o.oqty, o.ostatus, f.feedback_text
                    FROM orders o
                    LEFT JOIN feedback f ON o.oid = f.oid
                    WHERE o.ostatus IN (8,9)
                    ORDER BY o.odate DESC";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $label = isset($status_map[$row['ostatus']]) ? $status_map[$row['ostatus']] : '—';
                    $reason = $row['feedback_text'] ? htmlspecialchars($row['feedback_text']) : '—';
                    echo '<tr><td>' . $row['oid'] . '</td><td>' . $row['odate'] . '</td><td>' . $row['pid'] . '</td><td>' . $row['oqty'] . '</td><td>' . $label . '</td><td>' . $reason . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="6">No error or return orders found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Demand Forecasting: calculates 30-day moving average sales for each product -->
    <div class="report-box">
        <span class="report-label">Demand Forecasting (30-Day Moving Average)</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Avg Daily Sales (Last 30 Days)</th></tr>
            <?php
            // For each product, calculate average daily sales over the last 30 days (for demand planning)
            $sql = "SELECT p.pname, ROUND(COALESCE(SUM(o.oqty),0)/30,2) AS avg_daily_sales
                    FROM product p
                    LEFT JOIN orders o ON p.pid = o.pid AND o.odate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.pid, p.pname
                    ORDER BY avg_daily_sales DESC";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>' . $row['avg_daily_sales'] . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2">No product data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- ABC Analysis: classifies products by total sales value into A/B/C classes -->
    <div class="report-box">
        <span class="report-label">ABC Analysis (by Total Sales Value)</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Total Sales Value</th><th>ABC Class</th></tr>
            <?php
            // For each product, calculate total sales value, then assign ABC class by cumulative percentage
            $sql = "SELECT p.pname, COALESCE(SUM(o.ocost),0) AS total_sales
                    FROM product p
                    LEFT JOIN orders o ON p.pid = o.pid
                    GROUP BY p.pid, p.pname
                    ORDER BY total_sales DESC";
            $res = $conn->query($sql);
            $products = [];
            $total = 0;
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $products[] = $row;
                    $total += $row['total_sales'];
                }
                // Calculate cumulative percentage and assign ABC
                $cum = 0;
                foreach ($products as $i => $row) {
                    $cum += $row['total_sales'];
                    $percent = $total > 0 ? $cum / $total : 0;
                    if ($percent <= 0.2) {
                        $abc = 'A'; // Top 20% of sales value
                    } elseif ($percent <= 0.5) {
                        $abc = 'B'; // Next 30%
                    } else {
                        $abc = 'C'; // Remaining 50%
                    }
                    echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>$' . number_format($row['total_sales'],2) . '</td><td>' . $abc . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="3">No product data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Inventory Turnover Cycle: estimates how many days to sell current stock for each product -->
    <div class="report-box">
        <span class="report-label">Inventory Turnover Cycle</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Average Days to Sell Inventory</th></tr>
            <?php
            // For each product, estimate turnover cycle as (current stock / avg daily sales)
            $sql = "SELECT p.pname, 
                           (SELECT MIN(FLOOR(m.mqty / pm.pmqty)) FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = p.pid) AS stock,
                           ROUND(COALESCE(SUM(o.oqty),0)/30,2) AS avg_daily_sales
                    FROM product p
                    LEFT JOIN orders o ON p.pid = o.pid AND o.odate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.pid, p.pname
                    ORDER BY p.pname";
            $res = $conn->query($sql);
            $has_data = false;
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $has_data = true;
                    $stock = is_null($row['stock']) ? 0 : intval($row['stock']);
                    $avg = floatval($row['avg_daily_sales']);
                    if ($avg > 0) {
                        $cycle = round($stock / $avg, 1); // Days to sell all stock
                        echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>' . $cycle . ' days</td></tr>';
                    } else {
                        echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>Not enough sales data</td></tr>';
                    }
                }
            }
            if (!$has_data) {
                echo '<tr><td colspan="2">No product data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Order Fulfillment Efficiency: shows fulfillment time for delivered orders -->
    <div class="report-box">
        <span class="report-label">Order Fulfillment Efficiency</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Order ID</th><th>Order Date</th><th>Delivery Date</th><th>Fulfillment Time (days)</th></tr>
            <?php
            // If odeliverdate field exists, show fulfillment time (delivery - order date)
            $sql = "SHOW COLUMNS FROM orders LIKE 'odeliverdate'";
            $colres = $conn->query($sql);
            if ($colres && $colres->num_rows > 0) {
                $sql = "SELECT oid, odate, odeliverdate, DATEDIFF(odeliverdate, odate) AS fulfill_days FROM orders WHERE odeliverdate IS NOT NULL ORDER BY odate DESC LIMIT 50";
                $res = $conn->query($sql);
                $total_days = 0; $count = 0;
                if ($res && $res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        $days = is_null($row['fulfill_days']) ? '—' : $row['fulfill_days'];
                        echo '<tr><td>' . $row['oid'] . '</td><td>' . $row['odate'] . '</td><td>' . $row['odeliverdate'] . '</td><td>' . $days . '</td></tr>';
                        if (is_numeric($days)) { $total_days += $days; $count++; }
                    }
                    if ($count > 0) {
                        echo '<tr><td colspan="4" style="text-align:right;font-weight:bold;">Average Fulfillment Time: ' . round($total_days/$count,2) . ' days</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="4">No delivered orders found.</td></tr>';
                }
            } else {
                echo '<tr><td colspan="4">No odeliverdate field found in orders table. Please add an odeliverdate column to enable this report.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Regional/Channel Sales Report: shows total sales by region if region field exists -->
    <div class="report-box">
        <span class="report-label">Regional/Channel Sales Report</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Region</th><th>Total Sales</th></tr>
            <?php
            // If region field exists, show total sales by region (for market/channel analysis)
            $sql = "SHOW COLUMNS FROM orders LIKE 'region'";
            $colres = $conn->query($sql);
            if ($colres && $colres->num_rows > 0) {
                $sql = "SELECT region, SUM(ocost) AS total_sales FROM orders GROUP BY region ORDER BY total_sales DESC";
                $res = $conn->query($sql);
                if ($res && $res->num_rows > 0) {
                    while($row = $res->fetch_assoc()) {
                        echo '<tr><td>' . htmlspecialchars($row['region']) . '</td><td>$' . number_format($row['total_sales'],2) . '</td></tr>';
                    }
                } else {
                    echo '<tr><td colspan="2">No regional sales data found.</td></tr>';
                }
            } else {
                echo '<tr><td colspan="2">No region field found in orders table. Please add a region column to enable this report.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Gross Margin & Inventory Value Report: shows cost, price, margin, and inventory value for each product -->
    <div class="report-box">
        <span class="report-label">Gross Margin & Inventory Value Report</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Cost</th><th>Price</th><th>Gross Margin</th><th>Current Stock</th><th>Inventory Value</th></tr>
            <?php
            // For each product, show cost, price, gross margin, current stock, and inventory value
            $sql = "SELECT pname, pcost, price, (price-pcost)/price AS gross_margin, 
                           (SELECT MIN(FLOOR(m.mqty / pm.pmqty)) FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = p.pid) AS stock
                    FROM product p";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $stock = is_null($row['stock']) ? 0 : intval($row['stock']);
                    $inv_value = $stock * floatval($row['pcost']);
                    $margin = is_null($row['gross_margin']) ? '—' : (round($row['gross_margin']*100,1) . '%');
                    echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>$' . number_format($row['pcost'],2) . '</td><td>$' . number_format($row['price'],2) . '</td><td>' . $margin . '</td><td>' . $stock . '</td><td>$' . number_format($inv_value,2) . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="6">No product data found.</td></tr>';
            }
            ?>
        </table>
    </div>
    <!-- Auto Replenishment Suggestion: suggests reorder quantity based on avg sales and lead time -->
    <div class="report-box">
        <span class="report-label">Auto Replenishment Suggestion</span><br>
        <table style="width:100%;margin-top:10px;">
            <tr><th>Product Name</th><th>Current Stock</th><th>Avg Daily Sales (Last 30 Days)</th><th>Suggested Reorder Qty</th></tr>
            <?php
            // For each product, suggest reorder quantity: (avg daily sales * lead time) - current stock
            $lead_time = 7; // Assume 7 days lead time
            $sql = "SELECT p.pname, 
                           (SELECT MIN(FLOOR(m.mqty / pm.pmqty)) FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = p.pid) AS stock,
                           ROUND(COALESCE(SUM(o.oqty),0)/30,2) AS avg_daily_sales
                    FROM product p
                    LEFT JOIN orders o ON p.pid = o.pid AND o.odate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    GROUP BY p.pid, p.pname
                    ORDER BY p.pname";
            $res = $conn->query($sql);
            if ($res && $res->num_rows > 0) {
                while($row = $res->fetch_assoc()) {
                    $stock = is_null($row['stock']) ? 0 : intval($row['stock']);
                    $avg = floatval($row['avg_daily_sales']);
                    $suggest = max(0, ceil($avg * $lead_time - $stock));
                    echo '<tr><td>' . htmlspecialchars($row['pname']) . '</td><td>' . $stock . '</td><td>' . $avg . '</td><td>' . $suggest . '</td></tr>';
                }
            } else {
                echo '<tr><td colspan="4">No product data found.</td></tr>';
            }
            ?>
        </table>
    </div>
</div>
</body>
</html> 