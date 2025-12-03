<?php
// index.php - list products with categories (Read)
require 'database.php';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Products - ACUT</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <h1>Products</h1>
  <div class="top-links">
    <a href="manage_categories.php">Manage Categories</a>
    <a href="add_product.php">Add Product</a>
    <a href="customers.php">Customers</a>
    <a href="create_order.php">Create Order</a>
    <a href="view_orders.php">View Orders</a>
  </div>

  <?php
  $sql = "SELECT p.id, p.name AS product_name, p.price, c.name AS category_name
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.categories_id
          ORDER BY p.id DESC";
  $res = $conn->query($sql);
  ?>

  <table>
    <thead>
      <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Actions</th></tr>
    </thead>
    <tbody>
    <?php if($res && $res->num_rows): while($row = $res->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($row['id'])?></td>
        <td><?=htmlspecialchars($row['product_name'])?></td>
        <td><?=htmlspecialchars($row['category_name'] ?? '—')?></td>
        <td><?=number_format($row['price'],2)?></td>
        <td>
          <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a> |
          <a href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
      </tr>
    <?php endwhile; else: ?>
      <tr><td colspan="5">No products found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
