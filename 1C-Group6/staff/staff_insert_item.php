<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// --- Fetch all product categories for selection ---
// This block initializes an empty array to store product categories, which will be populated from the database.
$categories = [];
$catres = $conn->query("SELECT * FROM category");
if ($catres) {
    while ($catrow = $catres->fetch_assoc()) {
        $categories[] = $catrow;
    }
}
// --- Fetch all materials for material composition selection ---
$materials = [];
$matres = $conn->query("SELECT * FROM material");
while ($matres && $matrow = $matres->fetch_assoc()) {
    $materials[$matrow['mid']] = $matrow;
}
$prodmat = [];
$default_mid = 0;
// --- Handle form submission for inserting a new product ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form input values
    $pname = trim($_POST['pname']);
    $pdesc = trim($_POST['pdesc']);
    $pcost = floatval($_POST['pcost']);
    $price = isset($_POST['price']) ? floatval($_POST['price']) : $pcost; // fallback if only one price field
    $pmqtys = isset($_POST['material_pmqty']) ? $_POST['material_pmqty'] : [];
    $default_mid = isset($_POST['default_mid']) ? intval($_POST['default_mid']) : 0;
    // Validate required fields: name, cost, and default material
    if ($pname && $pcost > 0 && $default_mid > 0) {
        // Insert new product into the database
        $stmt = $conn->prepare("INSERT INTO product (pname, pdesc, pcost, price, default_mid) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssddi", $pname, $pdesc, $pcost, $price, $default_mid);
        $stmt->execute();
        $new_pid = $conn->insert_id;
        // Insert product-material composition (prodmat)
        foreach ($pmqtys as $mid => $pmqty) {
            $mid = intval($mid);
            $pmqty = intval($pmqty);
            if ($mid > 0 && $pmqty > 0) {
                $stmt2 = $conn->prepare("INSERT INTO prodmat (pid, mid, pmqty) VALUES (?, ?, ?)");
                $stmt2->bind_param("iii", $new_pid, $mid, $pmqty);
                $stmt2->execute();
            }
        }
        // Handle image upload for the new product
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION);
            $target = __DIR__ . "/../Sample Images/product/" . $new_pid . ".jpg";
            move_uploaded_file($_FILES['img']['tmp_name'], $target);
        }
        $msg = 'Product and material composition added!';
    } else {
        // If validation fails, show error message
        $msg = 'Please enter all required fields and set material composition.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insert Item - Staff</title>
    <link rel="stylesheet" href="../css/view_order_styles.css">
    <style>
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 40px 30px 30px 30px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        h1 { color: #29487d; margin-bottom: 1em; text-align:center; }
        .back-link { display: inline-block; margin-bottom: 1.5em; color: #4267b2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        .form-group { margin-bottom: 1.2em; }
        label { display:block; margin-bottom:0.4em; font-weight:500; }
        input, textarea, select { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; }
        button { background:#4267b2; color:#fff; border:none; border-radius:6px; padding:10px 24px; font-weight:bold; cursor:pointer; }
        button:hover { background:#29487d; }
        .msg { color:green; margin-bottom:1em; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="home.php">&larr; Back to Home</a>
    <h1>Insert New Product</h1>
    <?php if (isset($msg)) echo '<div class="msg">'.htmlspecialchars($msg).'</div>'; ?>
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <div class="form-group">
            <label for="pname">Product Name</label>
            <input type="text" id="pname" name="pname" required>
        </div>
        <div class="form-group">
            <label for="pdesc">Description</label>
            <textarea id="pdesc" name="pdesc" rows="3" required></textarea>
        </div>
        <div class="form-group">
            <label for="pcost">Price (USD)</label>
            <input type="number" id="pcost" name="pcost" min="0" step="0.01" required>
        </div>
        <div class="form-group">
            <label>Material Composition</label>
            <div style="color:#29487d;font-size:1.05em;margin-bottom:10px;">
                Set the quantity of each material required to produce one unit of this product. You must set at least one material.
            </div>
            <table class="order-table" style="margin-bottom:10px;">
                <tr><th>Picture</th><th>Name</th><th>Current Inventory</th><th>Unit</th><th>Required per Product</th></tr>
                <?php foreach ($materials as $mid => $mat): ?>
                <tr>
                    <td><img src="../Sample Images/material/<?= $mat['mid'] ?>.jpg" alt="<?= htmlspecialchars($mat['mname']) ?>" style="width:40px;height:40px;object-fit:cover;" onerror="this.onerror=null;this.src='../Sample Images/material/default.jpg';"></td>
                    <td><?= htmlspecialchars($mat['mname']) ?></td>
                    <td><?= $mat['mqty'] ?></td>
                    <td><?= htmlspecialchars($mat['munit']) ?></td>
                    <td><input type="number" name="material_pmqty[<?= $mat['mid'] ?>]" min="0" value="<?= isset($prodmat[$mat['mid']]) ? $prodmat[$mat['mid']] : '' ?>" style="width:60px;"></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="form-group">
            <label for="default_mid">Default Material</label>
            <select id="default_mid" name="default_mid" required>
                <option value="">Select Default Material</option>
                <?php foreach ($materials as $mid => $mat): ?>
                <option value="<?= $mid ?>" <?= ($default_mid == $mid) ? 'selected' : '' ?>><?= htmlspecialchars($mat['mname']) ?></option>
                <?php endforeach; ?>
            </select>
            <div style="color:#29487d;font-size:0.95em;margin-top:4px;">This material will be shown as the default for this product in customer ordering.</div>
        </div>
        <div class="form-group">
            <label for="img">Product Image</label>
            <input type="file" id="img" name="img" accept="image/*" required>
        </div>
        <button type="submit">Add Product</button>
    </form>
    <script>
    // Validate form fields before submitting
    function validateForm() {
        if (!document.getElementById('pname').value.trim() ||
            !document.getElementById('pdesc').value.trim() ||
            !document.getElementById('pcost').value ||
            !document.getElementById('img').value) {
            alert('Please fill in all fields and select an image.');
            return false;
        }
        // At least one material must be set
        var hasMaterial = false;
        document.querySelectorAll('input[name^="material_pmqty"]').forEach(function(input) {
            if (parseInt(input.value) > 0) hasMaterial = true;
        });
        if (!hasMaterial) {
            alert('Please set at least one material composition.');
            return false;
        }
        if (!document.getElementById('default_mid').value) {
            alert('Please select a default material.');
            return false;
        }
        return true;
    }
    </script>
</div>
</body>
</html> 