<?php
// create_order.php
require 'database.php';

// fetch customers and products
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name");
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)$_POST['customer_id'];
    $status = 'Pending';
    $items = $_POST['product_id'] ?? [];
    $qtys = $_POST['quantity'] ?? [];

    // Validate
    if ($customer_id && count($items)) {
        // calculate total
        $total = 0.0;
        // start transaction
        $conn->begin_transaction();

        // create order (total temporarily 0)
        $stmt = $conn->prepare("INSERT INTO orders (customer_id, status, total) VALUES (?, ?, ?)");
        $stmt->bind_param('isd', $customer_id, $status, $total);
        $stmt->execute();
        $order_id = $stmt->insert_id;
        $stmt->close();

        // insert order_items
        $insert_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        for ($i=0; $i<count($items); $i++) {
            $pid = (int)$items[$i];
            $q = (int)$qtys[$i];
            if ($q <= 0) continue;
            // get product price
            $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ? LIMIT 1");
            $stmt_price->bind_param('i', $pid);
            $stmt_price->execute();
            $r = $stmt_price->get_result();
            if (!$r || !$r->num_rows) continue;
            $product_row = $r->fetch_assoc();
            $price = (float)$product_row['price'];
            $stmt_price->close();
            $line_total = $price * $q;
            $total += $line_total;

            $insert_item->bind_param('iiid', $order_id, $pid, $q, $price);
            $insert_item->execute();
        }
        $insert_item->close();

        // update order with real total
        $stmt2 = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
        $stmt2->bind_param('di', $total, $order_id);
        $stmt2->execute(); $stmt2->close();

        $conn->commit();
        header('Location: view_order.php?id=' . $order_id);
        exit;
    } else {
        $error = "Select a customer and at least one product with quantity.";
    }
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Create Order</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Create Order</h1>
  <a href="index.php">← Back</a>
  <?php if(!empty($error)): ?><p class="small"><?=htmlspecialchars($error)?></p><?php endif; ?>
  <form method="post">
    <div class="form-row">
      <label>Customer</label>
      <select name="customer_id" required>
        <option value="">-- choose customer --</option>
        <?php while($c = $customers->fetch_assoc()): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <h3>Products</h3>
    <p class="small">Enter quantity for each product you want in the order</p>
    <table>
      <thead><tr><th>Pick</th><th>Product</th><th>Price</th><th>Quantity</th></tr></thead>
      <tbody>
        <?php if($products && $products->num_rows): while($p = $products->fetch_assoc()): ?>
          <tr>
            <td><input type="checkbox" class="chk" onchange="toggle(this, <?= $p['id'] ?>)"></td>
            <td><?= htmlspecialchars($p['name']) ?></td>
            <td><?= number_format($p['price'],2) ?></td>
            <td>
              <input type="hidden" name="product_id[]" value="<?= $p['id'] ?>" id="pid_<?= $p['id'] ?>">
              <input type="number" name="quantity[]" value="0" min="0" id="qty_<?= $p['id'] ?>">
            </td>
          </tr>
        <?php endwhile; else: ?>
          <tr><td colspan="4">No products.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <button class="btn btn-primary" type="submit">Create Order</button>
  </form>
</div>

<script>
// JS to quickly help annotate selected items (optional UX)
// When checkbox unchecked: set quantity to 0
function toggle(cb, pid){
  var qty = document.getElementById('qty_'+pid);
  if(!cb.checked) qty.value = 0;
  if(cb.checked && qty.value == 0) qty.value = 1;
}
</script>
</body>
</html>
