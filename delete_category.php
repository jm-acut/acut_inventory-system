<?php
// delete_category.php - Delete category
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id) {
    header('Location: manage_categories.php');
    exit;
}

// Check if category exists
$stmt = $conn->prepare("SELECT * FROM categories WHERE categories_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = 'Category not found.';
    header('Location: manage_categories.php');
    exit;
}

$category = $result->fetch_assoc();
$stmt->close();

// Handle confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    // Count products in this category
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $count_stmt->bind_param('i', $id);
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $product_count = $count_row['count'];
    $count_stmt->close();
    
    // Delete category (and cascade delete products)
    $del_stmt = $conn->prepare("DELETE FROM categories WHERE categories_id = ?");
    $del_stmt->bind_param('i', $id);
    
    if ($del_stmt->execute()) {
        $del_stmt->close();
        header('Location: manage_categories.php?success=deleted');
        exit;
    } else {
        $error = "Failed to delete category: " . $del_stmt->error;
        $del_stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delete Category</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Delete Category</h1>
    <a href="manage_categories.php">← Back to Categories</a>
    
    <?php if (isset($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <div class="info" style="margin-top: 20px;">
        <p><strong>Category:</strong> <?= htmlspecialchars($category['name']) ?></p>
        <p class="small" style="margin-top: 10px;">
            ⚠️ <strong>Warning:</strong> Deleting this category will also delete all products in this category.
        </p>
    </div>
    
    <form method="post" style="margin-top: 20px;">
        <button class="btn btn-danger" type="submit" name="confirm" value="1">
            Confirm Delete
        </button>
        <a href="manage_categories.php" class="btn btn-secondary" style="margin-left: 10px;">Cancel</a>
    </form>
</div>
</body>
</html>
