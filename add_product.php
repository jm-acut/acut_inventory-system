<?php
// add_product.php
require 'database.php';

$cats = $conn->query("SELECT categories_id, name FROM categories ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $price = (float)$_POST['price'];

    $stmt = $conn->prepare("INSERT INTO products (category_id, name, price) VALUES (?, ?, ?)");
    $stmt->bind_param('isd', $category_id, $name, $price);
    if ($stmt->execute()) {
        header('Location: index.php');
        exit;
    } else {
        $error = $stmt->error;
    }
    $stmt->close();
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Add Product</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Add Product</h1>
  <a href="index.php">← Back</a>
  <form method="post">
    <div class="form-row">
      <label>Category</label>
      <select name="category_id" required>
        <option value="">-- choose category --</option>
        <?php while($c = $cats->fetch_assoc()): ?>
          <option value="<?= $c['categories_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="form-row">
      <label>Name</label>
      <input type="text" name="name" required>
    </div>
    <div class="form-row">
      <label>Price</label>
      <input type="number" step="0.01" name="price" required>
    </div>
    <button class="btn btn-primary" type="submit">Add Product</button>
    <?php if(!empty($error)): ?><p class="small">Error: <?=htmlspecialchars($error)?></p><?php endif; ?>
  </form>
</div>
</body>
</html>
