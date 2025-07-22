<?php
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// Get product ID from GET parameter, default to 0 if not set
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($pid <= 0) {
    // If product ID is invalid, stop execution
    die('Invalid product ID.');
}
// Fetch product data for editing
$stmt = $conn->prepare("SELECT * FROM product WHERE pid=?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$result = $stmt->get_result();
if (!$row = $result->fetch_assoc()) {
    // If product not found, stop execution
    die('Product not found.');
}
// Fetch all materials for material composition selection
$materials = [];
$matres = $conn->query("SELECT * FROM material");
while ($matres && $matrow = $matres->fetch_assoc()) {
    $materials[$matrow['mid']] = $matrow;
}
// Fetch current product-material mapping (composition)
$prodmat = [];
$pmres = $conn->query("SELECT * FROM prodmat WHERE pid = $pid");
while ($pmres && $pmrow = $pmres->fetch_assoc()) {
    $prodmat[$pmrow['mid']] = $pmrow['pmqty'];
}
// Determine the default material for this product
$default_mid = isset($row['default_mid']) ? $row['default_mid'] : 0;
$prodmat_mids = array_keys($prodmat);
if (!$default_mid && count($prodmat_mids) > 0) {
    $default_mid = $prodmat_mids[0];
}
// Handle form submission for updating product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form input values
    $pname = trim($_POST['pname']);
    $pdesc = trim($_POST['pdesc']);
    $pcost = floatval($_POST['pcost']);
    $new_default_mid = isset($_POST['default_mid']) ? intval($_POST['default_mid']) : 0;
    $pmqtys = isset($_POST['material_pmqty']) ? $_POST['material_pmqty'] : [];
    // Validate required fields
    if ($pname && $pcost > 0) {
        // Update product data in the database
        $stmt2 = $conn->prepare("UPDATE product SET pname=?, pdesc=?, pcost=?, default_mid=? WHERE pid=?");
        $stmt2->bind_param("ssdii", $pname, $pdesc, $pcost, $new_default_mid, $pid);
        $stmt2->execute();
        // Update material composition: remove old, insert new
        $conn->query("DELETE FROM prodmat WHERE pid = $pid");
        foreach ($pmqtys as $mid => $pmqty) {
            $mid = intval($mid);
            $pmqty = intval($pmqty);
            if ($mid > 0 && $pmqty > 0) {
                $stmt = $conn->prepare("INSERT INTO prodmat (pid, mid, pmqty) VALUES (?, ?, ?)");
                $stmt->bind_param("iii", $pid, $mid, $pmqty);
                $stmt->execute();
            }
        }
        // Handle image upload if a new image is provided
        if (isset($_FILES['img']) && $_FILES['img']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
            // Only allow JPG and PNG images for product
            if ($ext === 'jpg' || $ext === 'jpeg' || $ext === 'png') {
                $target = __DIR__ . "/../Sample Images/product/" . $pid . "." . $ext;
                move_uploaded_file($_FILES['img']['tmp_name'], $target);
            } else {
                $msg = 'Only JPG and PNG images are allowed.';
            }
        }
        $msg = 'Product and material composition updated!';
        // Refresh product data after update
        $stmt = $conn->prepare("SELECT * FROM product WHERE pid=?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        // Refresh product-material mapping
        $prodmat = [];
        $pmres = $conn->query("SELECT * FROM prodmat WHERE pid = $pid");
        while ($pmres && $pmrow = $pmres->fetch_assoc()) {
            $prodmat[$pmrow['mid']] = $pmrow['pmqty'];
        }
        // Refresh default_mid
        $default_mid = isset($row['default_mid']) ? $row['default_mid'] : 0;
    } else {
        // If validation fails, show error message
        $msg = 'Please enter valid product name and price.';
    }
}
// Determine which image to display for the product (JPG, PNG, or default)
$img_path_jpg = "../Sample Images/product/{$row['pid']}.jpg";
$img_path_png = "../Sample Images/product/{$row['pid']}.png";
if (file_exists($img_path_jpg)) {
    $img_path = $img_path_jpg;
} elseif (file_exists($img_path_png)) {
    $img_path = $img_path_png;
} else {
    $img_path = "../Sample Images/product/default.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
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
        img { max-width:100px; max-height:100px; margin-bottom:10px; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="staff_view_items.php">&larr; Back to Product List</a>
    <h1>Edit Product</h1>
    <?php if (isset($msg)) echo '<div class="msg">'.htmlspecialchars($msg).'</div>'; ?>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="pname">Product Name</label>
            <input type="text" id="pname" name="pname" value="<?= htmlspecialchars($row['pname']) ?>" required>
        </div>
        <div class="form-group">
            <label for="pdesc">Description</label>
            <textarea id="pdesc" name="pdesc" rows="3"><?= htmlspecialchars($row['pdesc']) ?></textarea>
        </div>
        <div class="form-group">
            <label for="pcost">Price (USD)</label>
            <input type="number" id="pcost" name="pcost" min="0" step="0.01" value="<?= $row['pcost'] ?>" required>
        </div>
        <div class="form-group">
            <label>Material Composition</label>
            <div style="color:#29487d;font-size:1.05em;margin-bottom:10px;">
                Set the quantity of each material required to produce one unit of this product. The system will automatically calculate the maximum producible stock based on current material inventory and these ratios.
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
                <?php foreach ($prodmat as $mid => $pmqty): ?>
                <option value="<?= $mid ?>" <?= ($default_mid == $mid) ? 'selected' : '' ?>><?= htmlspecialchars($materials[$mid]['mname']) ?></option>
                <?php endforeach; ?>
            </select>
            <div style="color:#29487d;font-size:0.95em;margin-top:4px;">This material will be shown as the default for this product in customer ordering.</div>
        </div>
        <div class="form-group">
            <label>Current Image</label><br>
            <img src="<?= $img_path ?>" alt="Product Image" onerror="this.src='../Sample Images/product/default.jpg';">
        </div>
        <div class="form-group">
            <label for="img">Change Image</label>
            <input type="file" id="img" name="img" accept="image/*">
        </div>
        <button type="submit">Update Product</button>
    </form>
</div>
<script>
// Real-time stock calculation: calculates the maximum producible stock based on current material inventory and required ratios
function calcMaxStock() {
    var maxStock = null;
    <?php foreach ($materials as $mid => $mat): ?>
    var qty = parseInt(document.querySelector('input[name="material_pmqty[<?= $mat['mid'] ?>]"]').value) || 0;
    var inv = <?= $mat['mqty'] ?>;
    if (qty > 0) {
        var possible = Math.floor(inv / qty);
        if (maxStock === null || possible < maxStock) maxStock = possible;
    }
    <?php endforeach; ?>
    document.getElementById('stockCalcResult').textContent = (maxStock !== null) ? ('Maximum producible stock (based on current material inventory and ratio): ' + maxStock) : 'Set material ratios to see producible stock.';
}
document.querySelectorAll('input[name^="material_pmqty"]').forEach(function(input) {
    input.addEventListener('input', calcMaxStock);
});
window.onload = calcMaxStock;
</script>
</body>
</html> 