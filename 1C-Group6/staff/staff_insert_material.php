<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
$msg = '';
// Handle form submission for inserting a new material
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form input values
    $mname = trim($_POST['mname']);
    $mqty = intval($_POST['mqty']);
    $mrqty = intval($_POST['mrqty']);
    $munit = trim($_POST['munit']);
    $mreorderqty = intval($_POST['mreorderqty']);
    // Validate all required fields
    if ($mname && $mqty >= 0 && $mrqty >= 0 && $munit && $mreorderqty >= 0) {
        // Insert new material into the database
        $stmt = $conn->prepare("INSERT INTO material (mname, mqty, mrqty, munit, mreorderqty) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siisi", $mname, $mqty, $mrqty, $munit, $mreorderqty);
        $stmt->execute();
        $new_mid = $conn->insert_id;
        // Handle image upload for the new material
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $target = __DIR__ . "/../Sample Images/material/" . $new_mid . ".jpg";
            move_uploaded_file($_FILES['img']['tmp_name'], $target);
        }
        $msg = 'Material added!';
    } else {
        // If validation fails, show error message
        $msg = 'Please enter all required fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insert Material</title>
    <link rel="stylesheet" href="../css/view_order_styles.css">
    <style>
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 40px 30px 30px 30px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        h1 { color: #29487d; margin-bottom: 1em; text-align:center; }
        .back-link { display: inline-block; margin-bottom: 1.5em; color: #4267b2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        .form-group { margin-bottom: 1.2em; }
        label { display:block; margin-bottom:0.4em; font-weight:500; }
        input, select { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; }
        button { background:#4267b2; color:#fff; border:none; border-radius:6px; padding:10px 24px; font-weight:bold; cursor:pointer; }
        button:hover { background:#29487d; }
        .msg { color:green; margin-bottom:1em; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="home.php">&larr; Back to Home</a>
    <h1>Insert New Material</h1>
    <?php if ($msg) echo '<div class="msg">'.htmlspecialchars($msg).'</div>'; ?>
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="mname">Material Name</label>
            <input type="text" id="mname" name="mname" required>
        </div>
        <div class="form-group">
            <label for="mqty">Quantity</label>
            <input type="number" id="mqty" name="mqty" min="0" value="0" required>
        </div>
        <div class="form-group">
            <label for="mrqty">Reserved Quantity</label>
            <input type="number" id="mrqty" name="mrqty" min="0" value="0" required>
        </div>
        <div class="form-group">
            <label for="munit">Unit</label>
            <input type="text" id="munit" name="munit" required>
        </div>
        <div class="form-group">
            <label for="mreorderqty">Reorder Level</label>
            <input type="number" id="mreorderqty" name="mreorderqty" min="0" value="0" required>
        </div>
        <div class="form-group">
            <label for="img">Material Image</label>
            <input type="file" id="img" name="img" accept="image/*">
        </div>
        <button type="submit">Add Material</button>
    </form>
    <script>
    // Validate form fields before submitting
    function validateForm() {
        if (!document.getElementById('mname').value.trim() ||
            !document.getElementById('mqty').value ||
            !document.getElementById('mrqty').value ||
            !document.getElementById('munit').value.trim() ||
            !document.getElementById('mreorderqty').value) {
            alert('Please fill in all fields.');
            return false;
        }
        return true;
    }
    </script>
</div>
</body>
</html> 