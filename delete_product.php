<?php
// delete_product.php - Delete product
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: index.php');
    exit;
}

// Check if product exists
$stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.categories_id WHERE p.id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Count order items that reference this product
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
    $count_stmt->bind_param('i', $id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $item_count = $count_row['count'];
    $count_stmt->close();
    
    // Delete product (and cascade delete order items)
    $del_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $del_stmt->bind_param('i', $id);
    
    if ($del_stmt->execute()) {
        $del_stmt->close();
        header('Location: index.php?success=deleted');
        exit;
    } else {
        $error = "Failed to delete product: " . $del_stmt->error;
        $del_stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delete Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Delete Product</h1>
    <a href="index.php">← Back to Products</a>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="info" style="margin-top: 20px;">
        <p><strong>Product:</strong> <?= htmlspecialchars($product['name']) ?></p>
        <p><strong>Category:</strong> <?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></p>
        <p><strong>Price:</strong> $<?= number_format($product['price'], 2) ?></p>
        <p class="small" style="margin-top: 10px;">
            ⚠️ <strong>Warning:</strong> Deleting this product will also delete all order items that reference this product.
        </p>
    </div>
    
    <form method="post" style="margin-top: 20px;">
        <button class="btn btn-danger" type="submit" name="confirm" value="1">
            Confirm Delete
        </button>
        <a href="index.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
</body>
</html>
