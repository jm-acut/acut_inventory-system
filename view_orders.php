<?php
// view_orders.php - Display orders list or individual order details
require 'database.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// If viewing a specific order
if ($order_id) {
    // Fetch order and customer
    $order_q = $conn->prepare("SELECT o.*, c.name AS customer_name FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ? LIMIT 1");
    $order_q->bind_param('i', $order_id);
    $order_q->execute();
    $order_res = $order_q->get_result();
    
    if (!$order_res || !$order_res->num_rows) {
        header('Location: view_orders.php');
        exit;
    }
    
    $order = $order_res->fetch_assoc();
    $order_q->close();
    
    // Fetch items
    $items_q = $conn->prepare("SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $items_q->bind_param('i', $order_id);
    $items_q->execute();
    $items = $items_q->get_result();
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Order #<?= $order['id'] ?></title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <div class="container">
        <h1>Order #<?= $order['id'] ?></h1>
        <a href="view_orders.php">← Back to All Orders</a>
        
        <div class="info" style="margin-top: 20px;">
            <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?></p>
            <p><strong>Date:</strong> <?= substr($order['order_date'], 0, 10) ?></p>
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
                <?php $sum = 0; if ($items && $items->num_rows): 
                    while ($it = $items->fetch_assoc()): 
                        $line = $it['quantity'] * $it['price']; 
                        $sum += $line; 
                ?>
                    <tr>
                        <td><?= htmlspecialchars($it['name']) ?></td>
                        <td><?= $it['quantity'] ?></td>
                        <td>₱<?= number_format($it['price'], 2) ?></td>
                        <td>₱<?= number_format($line, 2) ?></td>
                    </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="4">No items.</td></tr>
                <?php endif; ?>
                <tr style="background: #f0f0f0; font-weight: bold;">
                    <td colspan="3" style="text-align: right;">Total:</td>
                    <td>₱<?= number_format($order['total'], 2) ?></td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 20px;">
            <a href="edit_order.php?id=<?= $order['id'] ?>" class="btn btn-primary">Edit Order</a>
            <a href="delete_order.php?id=<?= $order['id'] ?>" class="btn btn-danger" style="margin-left: 10px;">Delete Order</a>
        </div>
    </div>
    </body>
    </html>
    <?php
} else {
    // Display all orders list
    $sql = "SELECT o.id, o.order_date, o.total, c.name AS customer_name,
            (SELECT SUM(quantity) FROM order_items oi WHERE oi.order_id = o.id) AS total_items
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            ORDER BY o.id DESC";
    $res = $conn->query($sql);
    ?>
    <!doctype html>
    <html>
    <head>
        <meta charset="utf-8">
        <title>Orders</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <div class="container">
        <h1>Orders</h1>
        <a href="index.php">← Back to Products</a>
        
        <div class="top-links">
            <a href="add_order.php" class="btn btn-primary">+ Create New Order</a>
        </div>
        
        <h2>All Orders</h2>
        <?php if (isset($_GET['success'])): ?>
            <div class="success">Order deleted successfully!</div>
        <?php endif; ?>
        
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th style="width: 25%;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res && $res->num_rows > 0): ?>
                    <?php while ($o = $res->fetch_assoc()): ?>
                        <tr>
                            <td>#<?= $o['id'] ?></td>
                            <td><?= htmlspecialchars($o['customer_name'] ?? 'Unknown') ?></td>
                            <td><?= substr($o['order_date'], 0, 10) ?></td>
                            <td><?= $o['total_items'] ?? 0 ?></td>
                            <td>₱<?= number_format($o['total'], 2) ?></td>
                            <td>
                                <a href="view_orders.php?id=<?= $o['id'] ?>" class="btn btn-primary" style="padding: 6px 10px; font-size: 0.9em;">View</a>
                                <a href="edit_order.php?id=<?= $o['id'] ?>" class="btn btn-primary" style="padding: 6px 10px; font-size: 0.9em;">Edit</a>
                                <a href="delete_order.php?id=<?= $o['id'] ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.9em;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 20px;">No orders yet. <a href="add_order.php">Create one now</a></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    </body>
    </html>
    <?php
}
?>
