<?php
// edit_product.php
require 'database.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: index.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];
    $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, price = ? WHERE id = ?");
    $stmt->bind_param('isdi', $category_id, $name, $price, $id);
    $stmt->execute(); $stmt->close();
    header('Location: index.php');
    exit;
}

$cats = $conn->query("SELECT categories_id, name FROM categories ORDER BY name ASC");
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$r = $stmt->get_result();
if (!$r || !$r->num_rows) { echo "Product not found"; exit; }
$product = $r->fetch_assoc();
$stmt->close();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Edit Product</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Edit Product</h1>
  <a href="index.php">← Back</a>
  <form method="post">
    <div class="form-row">
      <label>Category</label>
      <select name="category_id" required>
        <?php while($c = $cats->fetch_assoc()): ?>
          <option value="<?= $c['categories_id'] ?>" <?= $c['categories_id']==$product['category_id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-row">
      <label>Name</label>
      <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>
    <div class="form-row">
      <label>Price</label>
      <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>
    </div>
    <button class="btn btn-primary" type="submit">Save</button>
  </form>
</div>
</body>
</html>
