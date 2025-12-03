<?php
// edit_order.php - View and manage existing order details
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if (!$id) {
    header('Location: view_orders.php');
    exit;
}

// Fetch order with customer
$stmt = $conn->prepare("SELECT o.*, c.name as customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: view_orders.php');
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Fetch order items
$items_stmt = $conn->prepare("SELECT oi.*, p.name, p.price as current_price FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$items_stmt->bind_param('i', $id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}
$items_stmt->close();

// Fetch available products
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name ASC");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Currently view-only
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Edit Order #<?= $order['id'] ?></h1>
    <a href="view_order.php?id=<?= $order['id'] ?>">← Back to Order</a>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <div class="info" style="margin-top: 20px;">
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?></p>
        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
        <p><strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?></p>
    </div>
    
    <h2 style="margin-top: 30px;">Order Items</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Line Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($order_items)): ?>
                <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>₱<?= number_format($item['price'], 2) ?></td>
                        <td>₱<?= number_format($item['quantity'] * $item['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No items in this order.</td></tr>
            <?php endif; ?>
            <tr style="background: #f0f0f0; font-weight: bold;">
                <td colspan="3">Total:</td>
                <td>₱<?= number_format($order['total'], 2) ?></td>
            </tr>
        </tbody>
    </table>
    
    <p class="small" style="margin-top: 20px; color: #666;">
        Note: To add or remove items from this order, you would need to create a new order or modify individual items directly in the database.
    </p>
</div>
</body>
</html>
