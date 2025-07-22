<?php
/**
 * Staff Product View Page
 * This page displays all products and supports search, delete, edit, and other functions.
 * Includes session validation, database queries, and UI block explanations.
 */
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// Query all products (not deleted) and join with category for display
$result = $conn->query("SELECT p.*, c.cname FROM product p LEFT JOIN category c ON p.cid = c.cid WHERE p.is_deleted = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Products - Staff</title>
    <link rel="stylesheet" href="../css/view_order_styles.css">
    <style>
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 40px 30px 30px 30px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        h1 { color: #29487d; margin-bottom: 1em; text-align:center; }
        .back-link { display: inline-block; margin-bottom: 1.5em; color: #4267b2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1.5em; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #f5f5f5; }
        .empty { text-align:center; color:#888; font-size:1.2em; margin:40px 0; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="home.php">&larr; Back to Home</a>
    <h1>Product List</h1>
    <input type="text" id="searchInput" placeholder="Search by name, description, or category..." style="width:100%;padding:8px;margin-bottom:16px;">
    <?php if ($result && $result->num_rows > 0): ?>
    <table id="productTable">
        <tr><th>ID</th><th>Picture</th><th>Name</th><th>Description</th><th>Price (USD)</th><th>Stock</th><th>Default Material</th><th>Action</th></tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['pid'] ?></td>
            <td><img src="../Sample Images/product/<?= $row['pid'] ?>.jpg" alt="<?= htmlspecialchars($row['pname']) ?>" style="width:50px;height:50px;object-fit:cover;" onerror="this.onerror=null;this.src='../Sample Images/product/default.jpg';"></td>
            <td><?= htmlspecialchars($row['pname']) ?></td>
            <td><?= htmlspecialchars($row['pdesc']) ?></td>
            <td>$<?= number_format($row['pcost'], 2) ?></td>
            <td>
                <?php
                // Calculate stock as the minimum possible based on material composition
                $stock_sql = "SELECT MIN(FLOOR(m.mqty / pm.pmqty)) AS stock FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = " . intval($row['pid']);
                $stock_result = $conn->query($stock_sql);
                $stock_row = $stock_result->fetch_assoc();
                $stock = $stock_row && $stock_row['stock'] !== null ? intval($stock_row['stock']) : 0;
                echo $stock;
                if ($stock < 10) {
                    echo ' <span title="Low Stock" style="color:#d9534f;font-weight:bold;margin-left:8px;">&#9888; Low Stock!</span>';
                }
                ?>
            </td>
            <td>
                <?php
                // Show the default material name for this product
                $mid = isset($row['default_mid']) ? intval($row['default_mid']) : 0;
                $matname = '';
                if ($mid > 0) {
                    $matres = $conn->query("SELECT mname FROM material WHERE mid = $mid");
                    if ($matrow = $matres->fetch_assoc()) {
                        $matname = $matrow['mname'];
                    }
                }
                echo htmlspecialchars($matname);
                ?>
            </td>
            <td>
                <a href="staff_edit_product.php?pid=<?= $row['pid'] ?>" class="green-button">Edit</a>
                <button type="button" class="red-button" onclick="showDeleteModal(<?= $row['pid'] ?>, '<?= htmlspecialchars(addslashes($row['pname'])) ?>')">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <!-- Delete Modal -->
    <div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:999;align-items:center;justify-content:center;">
      <div style="background:#fff;padding:30px 24px;border-radius:12px;max-width:350px;margin:60px auto;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.18);">
        <h3 style="margin-bottom:18px;">Delete Product</h3>
        <div id="deleteModalText" style="margin-bottom:18px;font-size:1.1em;"></div>
        <form id="deleteForm" method="post" action="staff_delete_product.php" style="display:inline;">
          <input type="hidden" name="pid" id="deletePid">
          <button type="submit" class="red-button" style="min-width:80px;">Delete</button>
          <button type="button" class="green-button" style="min-width:80px;margin-left:12px;" onclick="closeDeleteModal()">Cancel</button>
        </form>
      </div>
    </div>
    <script>
    // Search functionality and delete confirmation popup
    // This block adds an event listener to the search input field to filter products in real time as the user types.
    document.getElementById('searchInput').addEventListener('input', function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('#productTable tr');
        for (var i = 1; i < rows.length; i++) {
            var text = rows[i].textContent.toLowerCase();
            rows[i].style.display = text.includes(filter) ? '' : 'none';
        }
    });
    function showDeleteModal(pid, pname) {
      document.getElementById('deletePid').value = pid;
      document.getElementById('deleteModalText').textContent = 'Are you sure you want to delete "' + pname + '"?';
      document.getElementById('deleteModal').style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
      document.body.style.overflow = '';
    }
    </script>
    <?php else: ?>
        <div class="empty">No products found!</div>
    <?php endif; ?>
</div>
</body>
</html> 