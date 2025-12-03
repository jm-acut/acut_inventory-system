<?php
// view_orders.php
require 'database.php';

// get orders with customer names and total items
$sql = "SELECT o.id, o.order_date, o.status, o.total, c.name AS customer_name,
        (SELECT SUM(quantity) FROM order_items oi WHERE oi.order_id = o.id) AS total_items
        FROM orders o
        LEFT JOIN customers c ON o.customer_id = c.id
        ORDER BY o.id DESC";
$res = $conn->query($sql);
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Orders</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Orders</h1>
  <a href="index.php">← Back</a>
  <table>
    <thead><tr><th>ID</th><th>Customer</th><th>Date</th><th>Total Items</th><th>Total Amount</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if($res && $res->num_rows): while($o = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $o['id'] ?></td>
          <td><?= htmlspecialchars($o['customer_name']) ?></td>
          <td><?= $o['order_date'] ?></td>
          <td><?= $o['total_items'] ?? 0 ?></td>
          <td><?= number_format($o['total'],2) ?></td>
          <td><a href="view_order.php?id=<?= $o['id'] ?>">View</a></td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="6">No orders yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
