<?php
// edit_product.php - Edit existing product
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if (!$id) {
    header('Location: index.php');
    exit;
}

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php');
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Fetch categories
$categories = $conn->query("SELECT categories_id, name FROM categories ORDER BY name ASC");

// Handle form submission
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
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, price = ?, stock = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('isdii', $category_id, $name, $price, $stock, $id);
            if ($stmt->execute()) {
                $success = "Product updated successfully!";
                // Update displayed values
                $product['category_id'] = $category_id;
                $product['name'] = $name;
                $product['price'] = $price;
                $product['stock'] = $stock;
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
    <title>Edit Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Edit Product</h1>
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
                <?php if ($categories && $categories->num_rows > 0): ?>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?= $cat['categories_id'] ?>" 
                                <?= $cat['categories_id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name']) ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>
        </div>
        
        <div class="form-row">
            <label for="name">Product Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
        </div>
        
        <div class="form-row">
            <label for="price">Price *</label>
            <input type="number" id="price" name="price" step="0.01" min="0.01" 
                   value="<?= htmlspecialchars($product['price']) ?>" required>
        </div>
        
        <div class="form-row">
            <label for="stock">Stock Quantity *</label>
            <input type="number" id="stock" name="stock" min="0" 
                   value="<?= htmlspecialchars($product['stock'] ?? 0) ?>" required>
        </div>
        
        <button class="btn btn-primary" type="submit">Update Product</button>
    </form>
</div>
</body>
</html>
