<?php
// view_order.php
require 'database.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: view_orders.php'); exit; }

// Fetch order and customer
$order_q = $conn->prepare("SELECT o.*, c.name AS customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ? LIMIT 1");
$order_q->bind_param('i', $id);
$order_q->execute();
$order_res = $order_q->get_result();
if (!$order_res || !$order_res->num_rows) { echo "Order not found"; exit; }
$order = $order_res->fetch_assoc();
$order_q->close();

// Fetch items
$items_q = $conn->prepare("SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_q->bind_param('i', $id);
$items_q->execute();
$items = $items_q->get_result();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Order #<?= $order['id'] ?></title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Order #<?= $order['id'] ?></h1>
  <a href="view_orders.php">← Back to Orders</a>
  <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
  <p><strong>Date:</strong> <?= $order['order_date'] ?></p>
  <p><strong>Status:</strong> <?= htmlspecialchars($order['status']) ?></p>
  <hr>
  <h2>Items</h2>
  <table>
    <thead><tr><th>Product</th><th>Quantity</th><th>Price</th><th>Line Total</th></tr></thead>
    <tbody>
      <?php $sum = 0; if($items && $items->num_rows): while($it = $items->fetch_assoc()): ?>
        <?php $line = $it['quantity'] * $it['price']; $sum += $line; ?>
        <tr>
          <td><?= htmlspecialchars($it['name']) ?></td>
          <td><?= $it['quantity'] ?></td>
          <td><?= number_format($it['price'],2) ?></td>
          <td><?= number_format($line,2) ?></td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="4">No items.</td></tr>
      <?php endif; ?>
      <tr>
        <td colspan="3" style="text-align:right"><strong>Total</strong></td>
        <td><strong><?= number_format($sum,2) ?></strong></td>
      </tr>
    </tbody>
  </table>
</div>
</body>
</html>
