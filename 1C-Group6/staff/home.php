<?php
/**
 * Staff Home Page
 * This page displays a welcome message and function entry points for staff after login.
 * Includes session validation, navigation bar, logout link, etc.
 */
session_start();
if (!isset($_SESSION['staffID'])) {
    // If not logged in, redirect to login page
    header("Location: login.php?error=session");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #4267b2;
            font-family: 'Roboto', Arial, sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.13);
            padding: 2.5em 2.5em 2em 2.5em;
            min-width: 340px;
            max-width: 380px;
            text-align: center;
        }
        .header h2 {
            margin: 0 0 0.2em 0;
            font-size: 1.3em;
            color: #4267b2;
            font-weight: 700;
        }
        .header h1 {
            margin: 0 0 1.2em 0;
            font-size: 2em;
            font-weight: 700;
            color: #222;
        }
        .welcome {
            font-size: 1.1em;
            margin-bottom: 1.5em;
            color: #333;
        }
        .nav-list {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5em 0;
        }
        .nav-list li {
            margin: 0.5em 0;
        }
        .nav-link {
            display: block;
            background: #4267b2;
            color: #fff;
            text-decoration: none;
            padding: 1.1em 0;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.25em;
            margin-bottom: 0.7em;
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
            box-shadow: 0 2px 8px rgba(66,103,178,0.07);
            letter-spacing: 0.5px;
        }
        .nav-link:hover {
            background: #29487d;
            box-shadow: 0 4px 16px rgba(66,103,178,0.13);
            transform: translateY(-2px) scale(1.03);
        }
        .logout-link {
            display: inline-block;
            margin-top: 1em;
            color: #4267b2;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .logout-link:hover {
            color: #29487d;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Smile & Sunshine Toy Co. Ltd</h2>
            <h1>Staff Home</h1>
        </div>
        <div class="welcome">
            Welcome, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!
        </div>
        <!-- Navigation bar -->
        <ul class="nav-list">
            <li><a class="nav-link" href="staff_fully_custom_orders.php">Fully Customized Orders</a></li>
            <li><a class="nav-link" href="staff_view_order.php">View Orders</a></li>
            <!-- <li><a class="nav-link" href="staff_view_material.php">View Materials</a></li> -->
            <li><a class="nav-link" href="staff_insert_item.php">Insert Item</a></li>
            <li><a class="nav-link" href="staff_insert_material.php">Insert Material</a></li>
            <li><a class="nav-link" href="staff_view_material.php">Edit Material</a></li>
            <li><a class="nav-link" href="staff_view_items.php">Edit Product</a></li>
            <li><a class="nav-link" href="staff_generate_report.php">Generate Report</a></li>
        </ul>
        <!-- Logout link -->
        <a class="logout-link" href="logout.php">Logout</a>
    </div>
</body>
</html> 