<?php
// add_product.php - Add new product
require 'database.php';

$error = '';
$success = '';

// Fetch categories
$cats = $conn->query("SELECT categories_id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)($_POST['category_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    
    if (!$category_id) {
        $error = "Please select a category.";
    } elseif (empty($name)) {
        $error = "Product name is required.";
    } elseif ($price <= 0) {
        $error = "Price must be greater than 0.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, price, stock) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param('isdi', $category_id, $name, $price, $stock);
            if ($stmt->execute()) {
                $success = "Product added successfully!";
                $_POST = []; // Clear form
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $error = "Database error: " . $conn->error;
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Add Product</h1>
    <a href="index.php">← Back to Products</a>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post" style="max-width: 500px; margin-top: 20px;">
        <div class="form-row">
            <label for="category">Category *</label>
            <select id="category" name="category_id" required>
                <option value="">-- Select category --</option>
                <?php if ($cats && $cats->num_rows > 0): ?>
                    <?php while ($c = $cats->fetch_assoc()): ?>
                        <option value="<?= $c['categories_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option disabled>No categories available</option>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-row">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-row">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" required>
        </div>
        
        <div class="form-row">
            <label for="stock">Stock Quantity *</label>
            <input type="number" id="stock" name="stock" min="0" value="0" required>
        </div>
        
        <button class="btn btn-primary" type="submit">Add Product</button>
    </form>
</div>
</body>
</html>
