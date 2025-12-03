<?php
// delete_order.php - Delete order
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: view_orders.php');
    exit;
}

// Check if order exists
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

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete order items first (cascade)
        $del_items = $conn->prepare("DELETE FROM order_items WHERE order_id = ?");
        $del_items->bind_param('i', $id);
        if (!$del_items->execute()) {
            throw new Exception("Failed to delete order items");
        }
        $del_items->close();
        
        // Delete order
        $del_order = $conn->prepare("DELETE FROM orders WHERE id = ?");
        $del_order->bind_param('i', $id);
        if (!$del_order->execute()) {
            throw new Exception("Failed to delete order");
        }
        $del_order->close();
        
        // Commit transaction
        $conn->commit();
        header('Location: view_orders.php?success=deleted');
        exit;
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delete Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Delete Order</h1>
    <a href="view_orders.php">← Back to Orders</a>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="info" style="margin-top: 20px;">
        <p><strong>Order ID:</strong> #<?= $order['id'] ?></p>
        <p><strong>Customer:</strong> <?= htmlspecialchars($order['customer_name'] ?? 'Unknown') ?></p>
        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
        <p><strong>Total:</strong> ₱<?= number_format($order['total'], 2) ?></p>
        <p class="small" style="margin-top: 10px;">
            ⚠️ <strong>Warning:</strong> Deleting this order will also delete all items in this order.
        </p>
    </div>
    
    <form method="post" style="margin-top: 20px;">
        <button class="btn btn-danger" type="submit" name="confirm" value="1">
            Confirm Delete
        </button>
        <a href="view_orders.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
</body>
</html>
