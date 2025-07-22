<?php
/**
 * Staff Material View Page
 * This page displays all materials and supports search, delete, edit, and other functions.
 * Includes session validation, database queries, and UI block explanations.
 */
session_start();
// Session validation: Only allow access if staff is logged in
if (!isset($_SESSION['staffID'])) {
    header("Location: login.php?error=session");
    exit();
}
require_once('../includes/db_connect.php');
// Query all materials (not deleted) for display
$result = $conn->query("SELECT * FROM material WHERE is_deleted = 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Materials - Staff</title>
    <link rel="stylesheet" href="../css/view_order_styles.css">
    <style>
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 16px; padding: 40px 30px 30px 30px; box-shadow: 0 2px 16px rgba(0,0,0,0.08); }
        h1 { color: #29487d; margin-bottom: 1em; text-align:center; }
        .back-link { display: inline-block; margin-bottom: 1.5em; color: #4267b2; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="container">
    <a class="back-link" href="home.php">&larr; Back to Home</a>
    <h1>Material Inventory</h1>
    <input type="text" id="searchInput" placeholder="Search by name, unit, reorder level..." style="width:100%;padding:8px;margin-bottom:16px;">
    <table class="order-table" id="materialTable">
        <tr><th>ID</th><th>Picture</th><th>Name</th><th>Quantity</th><th>Reserved</th><th>Unit</th><th>Reorder Level</th><th>Action</th></tr>
        <?php while($row = $result->fetch_assoc()): ?>
        <tr class="material-row"<?= ($row['mqty'] < $row['mreorderqty']) ? ' style="background:#ffeaea;"' : '' ?>>
            <td><?= $row['mid'] ?></td>
            <td><img src="../Sample Images/material/<?= $row['mid'] ?>.jpg" alt="<?= htmlspecialchars($row['mname']) ?>" style="width:50px;height:50px;object-fit:cover;" onerror="this.onerror=null;this.src='../Sample Images/material/default.jpg';"></td>
            <td><?= htmlspecialchars($row['mname']) ?>
                <?php if ($row['mqty'] < $row['mreorderqty']): ?>
                    <span title="Low Stock" style="color:#d9534f;font-weight:bold;margin-left:8px;">&#9888; Low Stock!</span>
                <?php endif; ?>
            </td>
            <td><?= $row['mqty'] ?></td>
            <td><?= $row['mrqty'] ?></td>
            <td><?= $row['munit'] ?></td>
            <td><?= $row['mreorderqty'] ?></td>
            <td>
                <a href="staff_edit_material.php?mid=<?= $row['mid'] ?>" class="green-button">Edit</a>
                <button type="button" class="red-button" onclick="showDeleteModal(<?= $row['mid'] ?>, '<?= htmlspecialchars(addslashes($row['mname'])) ?>')">Delete</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <!-- Delete Modal -->
    <div id="deleteModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);z-index:999;align-items:center;justify-content:center;">
      <div style="background:#fff;padding:30px 24px;border-radius:12px;max-width:350px;margin:60px auto;text-align:center;box-shadow:0 4px 24px rgba(0,0,0,0.18);">
        <h3 style="margin-bottom:18px;">Delete Material</h3>
        <div id="deleteModalText" style="margin-bottom:18px;font-size:1.1em;"></div>
        <form id="deleteForm" method="post" action="staff_delete_material.php" style="display:inline;">
          <input type="hidden" name="mid" id="deleteMid">
          <button type="submit" class="red-button" style="min-width:80px;">Delete</button>
          <button type="button" class="green-button" style="min-width:80px;margin-left:12px;" onclick="closeDeleteModal()">Cancel</button>
        </form>
      </div>
    </div>
    <script>
    // Search functionality and delete confirmation popup
    // This block adds an event listener to the search input field to filter materials in real time as the user types.
    document.getElementById('searchInput').addEventListener('input', function() {
        var filter = this.value.toLowerCase();
        var rows = document.querySelectorAll('#materialTable .material-row');
        for (var i = 0; i < rows.length; i++) {
            var text = rows[i].textContent.toLowerCase();
            rows[i].style.display = text.includes(filter) ? '' : 'none';
        }
    });
    function showDeleteModal(mid, mname) {
      document.getElementById('deleteMid').value = mid;
      document.getElementById('deleteModalText').textContent = 'Are you sure you want to delete "' + mname + '"?';
      document.getElementById('deleteModal').style.display = 'flex';
      document.body.style.overflow = 'hidden';
    }
    function closeDeleteModal() {
      document.getElementById('deleteModal').style.display = 'none';
      document.body.style.overflow = '';
    }
    </script>
</div>
</body>
</html> 