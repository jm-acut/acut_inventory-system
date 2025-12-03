<?php
// delete_customer.php - Delete customer
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: customers.php');
    exit;
}

// Check if customer exists
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: customers.php');
    exit;
}

$customer = $result->fetch_assoc();
$stmt->close();

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Count orders for this customer
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?");
    $count_stmt->bind_param('i', $id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $order_count = $count_row['count'];
    $count_stmt->close();
    
    // Delete customer (and cascade delete orders and order items)
    $del_stmt = $conn->prepare("DELETE FROM customers WHERE id = ?");
    $del_stmt->bind_param('i', $id);
    
    if ($del_stmt->execute()) {
        $del_stmt->close();
        header('Location: customers.php?success=deleted');
        exit;
    } else {
        $error = "Failed to delete customer: " . $del_stmt->error;
        $del_stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delete Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Delete Customer</h1>
    <a href="customers.php">← Back to Customers</a>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="info" style="margin-top: 20px;">
        <p><strong>Customer:</strong> <?= htmlspecialchars($customer['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($customer['email'] ?? 'N/A') ?></p>
        <p><strong>Phone:</strong> <?= htmlspecialchars($customer['phone'] ?? 'N/A') ?></p>
        <p class="small" style="margin-top: 10px;">
            ⚠️ <strong>Warning:</strong> Deleting this customer will also delete all their orders and order items.
        </p>
    </div>
    
    <form method="post" style="margin-top: 20px;">
        <button class="btn btn-danger" type="submit" name="confirm" value="1">
            Confirm Delete
        </button>
        <a href="customers.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
</body>
</html>
