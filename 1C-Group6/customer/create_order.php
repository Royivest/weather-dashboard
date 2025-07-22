<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create Order - Smile & Sunshine Toy Co. Ltd</title>
  <link rel="stylesheet" href="../css/styles.css">
  <style>
    /* Navigation bar style */
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
    /* Product table style */
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
    .order-table img {
      width: 60px;
      height: 60px;
      object-fit: contain;
    }
  </style>
</head>
<body>
  <!-- Navigation bar -->
  <div class="navbar">
    <a href="home.php">Home</a>
    <a href="create_order.php">Create Order</a>
    <a href="view_order.php">View Orders</a>
    <a href="update_profile.php">Update Profile</a>
  </div>
  <div class="container">
    <div class="header">
      <h1>Create Order</h1>
      <h2>Your Toys</h2>
    </div>
    <?php
    /**
     * Load database connection
     * Get all material and product data for order selection
     */
    require_once('../includes/db_connect.php');
    // Get all material
    $materials = [];
    $result = $conn->query("SELECT * FROM material");
    while ($row = $result->fetch_assoc()) {
      $materials[] = $row;
    }
    // Get all products (from database)
    $products = [];
    $result2 = $conn->query("SELECT * FROM product WHERE is_deleted = 0");
    while ($row = $result2->fetch_assoc()) {
      $img_path = "../Sample Images/product/" . $row['pid'] . ".jpg";
      if (!file_exists($img_path)) {
        $img_path = "../Sample Images/product/default.jpg";
      }

      // Calculate the maximum production stock for the product
      $stock_sql = "SELECT MIN(FLOOR(m.mqty / pm.pmqty)) AS stock
                    FROM prodmat pm
                    JOIN material m ON pm.mid = m.mid
                    WHERE pm.pid = " . intval($row['pid']);
      $stock_result = $conn->query($stock_sql);
      $stock_row = $stock_result->fetch_assoc();
      $stock = $stock_row && $stock_row['stock'] !== null ? intval($stock_row['stock']) : 0;

      $products[] = [
        'id' => $row['pid'],
        'name' => $row['pname'],
        'img' => $img_path,
        'price' => $row['pcost'],
        'stock' => $stock // Here is the correct stock
      ];
    }
    // Fetch default material for each product using product.default_mid
    foreach ($products as &$p) {
        $sql = "SELECT default_mid FROM product WHERE pid = " . intval($p['id']);
        $res = $conn->query($sql);
        $row = $res->fetch_assoc();
        $default_mid = $row ? intval($row['default_mid']) : 0;
        $matname = '';
        if ($default_mid > 0) {
            $matres = $conn->query("SELECT mname FROM material WHERE mid = $default_mid");
            if ($matrow = $matres->fetch_assoc()) {
                $matname = $matrow['mname'];
            }
        }
        $p['default_material'] = $matname;
        $p['default_mid'] = $default_mid;
    }
    unset($p);
    // For each product, fetch allowed materials from prodmat
    $prodmat_materials = [];
    foreach ($products as $p) {
        $pid = intval($p['id']);
        $sql = "SELECT pm.mid, m.mname FROM prodmat pm JOIN material m ON pm.mid = m.mid WHERE pm.pid = $pid";
        $res = $conn->query($sql);
        $prodmat_materials[$pid] = [];
        while ($row = $res->fetch_assoc()) {
            $prodmat_materials[$pid][] = [
                'mid' => $row['mid'],
                'mname' => $row['mname']
            ];
        }
    }
    // Fetch all available materials for the modal
    $all_materials = [];
    $matres = $conn->query("SELECT mid, mname FROM material WHERE is_deleted = 0");
    while ($row = $matres->fetch_assoc()) {
        $all_materials[] = [
            'mid' => $row['mid'],
            'mname' => $row['mname']
        ];
    }
    ?>
    <!-- Order creation form -->
    <form action="submit_order.php" method="POST" id="orderForm">
      <table class="order-table">
        <tr>
          <th>ID</th>
          <th>Picture</th>
          <th>Name</th>
          <th>Price</th>
          <th>Stock</th>
          <th>Default Material</th>
          <th>Qty</th>
          <th>Half Customize</th>
        </tr>
        <?php foreach ($products as $p): ?>
        <tr>
          <td><?php echo $p['id']; ?></td>
          <td><img src="<?php echo $p['img']; ?>" alt="<?php echo $p['name']; ?>"></td>
          <td><?php echo $p['name']; ?></td>
          <td class="price" data-id="<?php echo $p['id']; ?>"><?php echo $p['price']; ?></td>
          <td><?php echo $p['stock']; ?></td>
          <td><?php echo htmlspecialchars($p['default_material']); ?></td>
          <td><input type="number" name="qty[<?php echo $p['id']; ?>]" min="0" max="<?php echo $p['stock']; ?>" value="0" style="width:60px;" class="qty-input" data-id="<?php echo $p['id']; ?>"></td>
          <td>
            <button type="button" class="change-material-btn" data-pid="<?php echo $p['id']; ?>">Change Material</button>
            <span class="material-selected-label" id="material_label_<?php echo $p['id']; ?>" style="margin-left:8px;color:#2a7ae2;font-weight:bold;"></span>
            <input type="hidden" name="material_selected[<?php echo $p['id']; ?>]" id="material_selected_<?php echo $p['id']; ?>" value="<?php echo $p['default_mid']; ?>">
            <div class="color-allocation" id="color_alloc_<?php echo $p['id']; ?>" style="display:none;margin-top:8px;">
              <div style="font-size:0.95em;color:#29487d;margin-bottom:4px;">Allocate quantity for each color:</div>
              <label style="margin-right:8px;">
                Original/No Color: <input type="number" min="0" value="0" name="color_qty[<?php echo $p['id']; ?>][Original]" class="color-qty-input" data-pid="<?php echo $p['id']; ?>">
              </label>
              <?php $colors = ['Red','Blue','Green','Yellow','White','Black']; foreach ($colors as $color): ?>
                <label style="margin-right:8px;">
                  <?php echo $color; ?>: <input type="number" min="0" value="0" name="color_qty[<?php echo $p['id']; ?>][<?php echo $color; ?>]" class="color-qty-input" data-pid="<?php echo $p['id']; ?>">
                </label>
              <?php endforeach; ?>
              <span id="color_sum_msg_<?php echo $p['id']; ?>" style="color:#d9534f;font-size:0.95em;margin-left:8px;"></span>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
      <div style="color:#29487d;font-size:1.05em;margin-bottom:10px;">
        <strong>Note:</strong> For each product, you can choose a different material and allocate quantities for each color. The sum of all color quantities must match the total quantity for that product.
      </div>
      <!-- Material Modal -->
      <div id="materialModal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.4);z-index:999;align-items:center;justify-content:center;">
        <div style="background:#fff;padding:30px 20px;border-radius:12px;max-width:600px;margin:60px auto;">
          <h3>Choose Material</h3>
          <table style="width:100%;border-collapse:collapse;">
            <tr><th>ID</th><th>Picture</th><th>Name</th><th>Choose</th></tr>
            <tbody class="material-options" id="material_options_modal" style="display:none;">
              <?php foreach ($all_materials as $m): ?>
              <tr>
                <td><?php echo htmlspecialchars($m['mid']); ?></td>
                <td>
                  <img src="../Sample Images/material/<?php echo htmlspecialchars($m['mid']); ?>.jpg"
                       alt="Material Image"
                       style="width:40px;height:40px;object-fit:cover;"
                       onerror="this.onerror=null;this.src='../Sample Images/material/<?php echo htmlspecialchars($m['mid']); ?>.png';"
                       onerror="this.onerror=null;this.src='../Sample Images/material/default.jpg';">
                </td>
                <td><?php echo htmlspecialchars($m['mname']); ?></td>
                <td><button type="button" class="select-material-btn" data-mid="<?php echo $m['mid']; ?>" data-mname="<?php echo htmlspecialchars($m['mname']); ?>">Select</button></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <div style="text-align:right;margin-top:10px;"><button type="button" id="closeMaterialModal">Close</button></div>
        </div>
      </div>
      <!-- Order amount display -->
      <div class="form-group">
        <label for="totalAmount">Total Amount ($)</label>
        <input type="text" id="totalAmount" name="totalAmount" readonly value="0.00">
      </div>
      <!-- Half-customized block (reserved) -->
      <div class="form-group">
        <!-- Removed label -->
      </div>
      <!-- Fully Customized Order Section -->
      <div style="margin-top:32px;margin-bottom:8px;">
        <span style="font-size:1.35em;font-weight:bold;text-decoration:underline;">Fully Customized Order</span>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <label>Don't see what you want? Describe your dream toy!</label>
        <textarea name="custom_desc" id="custom_desc" rows="3" placeholder="Describe your idea (e.g. I want a robot with wings)"></textarea>
        <div id="desc_hint" style="color:red;font-size:0.9em;">*Please enter at least 100 characters</div>
      </div>
      <div class="form-group">
        <label>Budget (USD)</label>
        <input type="number" name="custom_budget" min="0" step="0.01" placeholder="e.g. 50">
      </div>
      <div class="form-group">
        <label>Quantity</label>
        <input type="number" name="custom_qty" min="1" max="99" placeholder="e.g. 1">
      </div>
      <div class="form-group">
        <label>Expected Delivery Date</label>
        <input type="date" name="custom_deadline">
      </div>
      <!-- Customer information fields -->
      <div class="form-group">
        <label for="customer_name">Your Name</label>
        <input type="text" id="customer_name" name="customer_name" required>
      </div>
      <div class="form-group">
        <label for="contact">Contact Number</label>
        <input type="text" id="contact" name="contact" required>
      </div>
      <div class="form-group">
        <label for="address">Delivery Address</label>
        <input type="text" id="address" name="address" required>
      </div>
      <div class="form-group">
        <label for="company">Company Name</label>
        <input type="text" id="company" name="company" required>
      </div>
      <button type="submit" class="logout-btn">Submit Order</button>
    </form>
  </div>
  <script>
  // Material modal logic
  let currentPid = null;
  document.querySelectorAll('.change-material-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      currentPid = this.getAttribute('data-pid');
      // Only show one material_options_modal
      document.getElementById('material_options_modal').style.display = '';
      document.getElementById('materialModal').style.display = 'flex';
    });
  });
  document.querySelectorAll('.select-material-btn').forEach(btn => {
    btn.addEventListener('click', function() {
      const mid = this.getAttribute('data-mid');
      const mname = this.getAttribute('data-mname');
      const pid = currentPid;
      document.getElementById('material_selected_' + pid).value = mid;
      document.getElementById('material_label_' + pid).textContent = 'Selected: ' + mname;
      document.getElementById('materialModal').style.display = 'none';
      document.getElementById('material_options_modal').style.display = 'none';
    });
  });
  document.getElementById('closeMaterialModal').onclick = function() {
    document.getElementById('materialModal').style.display = 'none';
    document.getElementById('material_options_modal').style.display = 'none';
  };
  // Auto-calculate total amount
  function updateTotal() {
    let total = 0;
    document.querySelectorAll('.qty-input').forEach(function(input) {
      const id = input.getAttribute('data-id');
      const qty = parseInt(input.value) || 0;
      const price = parseFloat(document.querySelector('.price[data-id="'+id+'"]')?.textContent) || 0;
      total += qty * price;
    });
    document.getElementById('totalAmount').value = total.toFixed(2);
  }
  document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('input', updateTotal);
  });
  // Strict validation for order types
  document.getElementById('orderForm').onsubmit = function(e) {
    // Check if any product is ordered
    let hasProduct = false;
    document.querySelectorAll('.qty-input').forEach(function(input) {
      if (parseInt(input.value) > 0) hasProduct = true;
    });
    let totalAmount = parseFloat(document.getElementById('totalAmount').value);
    let customDesc = document.getElementById('custom_desc').value.trim();
    let customBudget = document.querySelector('[name="custom_budget"]').value.trim();
    let customQty = document.querySelector('[name="custom_qty"]').value.trim();
    let customDeadline = document.querySelector('[name="custom_deadline"]').value.trim();

    // Cannot submit both product and fully customized order
    if (hasProduct && customDesc.length > 0) {
      alert('You cannot order products and fully customized order at the same time.');
      e.preventDefault(); return false;
    }
    // Normal/half-customized order validation
    if (hasProduct) {
      if (totalAmount <= 0) {
        alert('Total Amount must be greater than 0.');
        e.preventDefault(); return false;
      }
      if (customDesc.length > 0 || customBudget || customQty || customDeadline) {
        alert('Please do not fill in the fully customized order section when ordering products.');
        e.preventDefault(); return false;
      }
    }
    // Fully customized order validation
    if (customDesc.length > 0) {
      if (customDesc.length < 100) {
        alert('Please enter at least 100 characters for your custom description.');
        e.preventDefault(); return false;
      }
      if (!customBudget || parseFloat(customBudget) <= 0) {
        alert('Please enter a valid budget for your custom order.');
        e.preventDefault(); return false;
      }
      if (!customQty || parseInt(customQty) <= 0) {
        alert('Please enter a valid quantity for your custom order.');
        e.preventDefault(); return false;
      }
      if (!customDeadline) {
        alert('Please enter an expected delivery date for your custom order.');
        e.preventDefault(); return false;
      }
      if (hasProduct || totalAmount > 0) {
        alert('For fully customized order, product quantity must be 0 and total amount must be 0.');
        e.preventDefault(); return false;
      }
    }
  };
  document.querySelectorAll('.qty-input').forEach(function(input) {
    input.addEventListener('input', function() {
      var pid = this.getAttribute('data-id');
      var qty = parseInt(this.value) || 0;
      var allocDiv = document.getElementById('color_alloc_' + pid);
      if (qty > 0) {
        allocDiv.style.display = '';
      } else {
        allocDiv.style.display = 'none';
      }
      // Reset color qty if qty is 0
      if (qty === 0) {
        allocDiv.querySelectorAll('.color-qty-input').forEach(function(cinput) { cinput.value = 0; });
      }
    });
  });
  document.querySelectorAll('.color-qty-input').forEach(function(input) {
    input.addEventListener('input', function() {
      var pid = this.getAttribute('data-pid');
      var totalQty = parseInt(document.querySelector('.qty-input[data-id="'+pid+'"]')?.value) || 0;
      var sum = 0;
      document.querySelectorAll('.color-qty-input[data-pid="'+pid+'"]').forEach(function(cinput) {
        sum += parseInt(cinput.value) || 0;
      });
      var msg = document.getElementById('color_sum_msg_' + pid);
      if (sum !== totalQty) {
        msg.textContent = 'Sum of color quantities must match total quantity ('+totalQty+').';
      } else {
        msg.textContent = '';
      }
    });
  });
  </script>
</body>
</html> 