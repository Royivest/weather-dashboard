<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// Get material ID from GET parameter, default to 0 if not set
$mid = isset($_GET['mid']) ? intval($_GET['mid']) : 0;
if ($mid <= 0) {
    // If material ID is invalid, stop execution
    die('Invalid material ID.');
}
// Fetch material data for editing
$stmt = $conn->prepare("SELECT * FROM material WHERE mid=?");
$stmt->bind_param("i", $mid);
$stmt->execute();
$result = $stmt->get_result();
if (!$row = $result->fetch_assoc()) {
    // If material not found, stop execution
    die('Material not found.');
}
// Handle form submission for updating material
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form input values
    $mname = trim($_POST['mname']);
    $mqty = intval($_POST['mqty']);
    $mrqty = intval($_POST['mrqty']);
    $munit = trim($_POST['munit']);
    $mreorderqty = intval($_POST['mreorderqty']);
    // Validate all required fields
    if ($mname && $mqty >= 0 && $mrqty >= 0 && $munit && $mreorderqty >= 0) {
        // Update material data in the database
        $stmt2 = $conn->prepare("UPDATE material SET mname=?, mqty=?, mrqty=?, munit=?, mreorderqty=? WHERE mid=?");
        $stmt2->bind_param("siisii", $mname, $mqty, $mrqty, $munit, $mreorderqty, $mid);
        $stmt2->execute();
        // Handle image upload if a new image is provided
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            // Only allow JPG and PNG images for material
            if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png') {
                $target = __DIR__ . "/../Sample Images/material/" . $mid . "." . $ext;
                move_uploaded_file($_FILES['img']['tmp_name'], $target);
            } else {
                $msg = 'Only JPG and PNG images are allowed.';
            }
        }
        $msg = 'Material updated!';
        // Re-query latest data to display updated info
        $stmt = $conn->prepare("SELECT * FROM material WHERE mid=?");
        $stmt->bind_param("i", $mid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
    } else {
        // If validation fails, show error message
        $msg = 'Please enter all required fields.';
    }
}
// Determine which image to display for the material (JPG, PNG, or default)
$img_path_jpg = "../Sample Images/material/{$row['mid']}.jpg";
$img_path_png = "../Sample Images/material/{$row['mid']}.png";
if (file_exists($img_path_jpg)) {
    $img_path = $img_path_jpg;
} elseif (file_exists($img_path_png)) {
    $img_path = $img_path_png;
} else {
    $img_path = "../Sample Images/material/default.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Material</title>
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
        img { max-width:100px; max-height:100px; margin-bottom:10px; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="staff_view_material.php">&larr; Back to Material List</a>
    <h1>Edit Material</h1>
    <div style="color:#29487d;font-size:1.1em;margin-bottom:18px;">
        <strong>Note:</strong> Product stock is always calculated automatically based on material quantities. Staff only need to manage material stock (Quantity). Product availability will update automatically according to material levels. There is no risk of data inconsistency.
    </div>
    <?php if (isset($msg)) echo '<div class="msg">'.htmlspecialchars($msg).'</div>'; ?>
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="mname">Material Name</label>
            <input type="text" id="mname" name="mname" value="<?= htmlspecialchars($row['mname']) ?>" required>
        </div>
        <div class="form-group">
            <label for="mqty">Quantity</label>
            <input type="number" id="mqty" name="mqty" min="0" value="<?= $row['mqty'] ?>" required>
        </div>
        <div class="form-group">
            <label for="mrqty">Reserved Quantity</label>
            <input type="number" id="mrqty" name="mrqty" min="0" value="<?= $row['mrqty'] ?>" required>
        </div>
        <div class="form-group">
            <label for="munit">Unit</label>
            <input type="text" id="munit" name="munit" value="<?= htmlspecialchars($row['munit']) ?>" required>
        </div>
        <div class="form-group">
            <label for="mreorderqty">Reorder Level</label>
            <input type="number" id="mreorderqty" name="mreorderqty" min="0" value="<?= $row['mreorderqty'] ?>" required>
        </div>
        <div class="form-group">
            <label>Current Image</label><br>
            <img id="previewImg" src="<?= $img_path ?>" alt="Material Image" onerror="this.onerror=null;this.src='../Sample Images/material/default.jpg';">
        </div>
        <div class="form-group">
            <label for="img">Change Image</label>
            <input type="file" id="img" name="img" accept="image/*" onchange="previewImage(event)">
        </div>
        <button type="submit">Update Material</button>
        <a href="staff_view_material.php" class="green-button" style="margin-left:16px;">Back</a>
    </form>
    <script>
    // Preview the selected image before uploading
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            document.getElementById('previewImg').src = reader.result;
        };
        if(event.target.files[0]) reader.readAsDataURL(event.target.files[0]);
    }
    // Validate form fields before submitting
    function validateForm() {
        const mqty = document.getElementById('mqty').value;
        const mrqty = document.getElementById('mrqty').value;
        const mreorderqty = document.getElementById('mreorderqty').value;
        if (mqty < 0 || mrqty < 0 || mreorderqty < 0) {
            alert('Quantity, Reserved, and Reorder Level must be non-negative!');
            return false;
        }
        return true;
    }
    </script>
</div>
</body>
</html> 