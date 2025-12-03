<?php
// manage_categories.php
require 'database.php';

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->close();
        header('Location: manage_categories.php');
        exit;
    }
}

// Edit category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE categories_id = ?");
        $stmt->bind_param('si', $name, $id);
        $stmt->execute(); $stmt->close();
        header('Location: manage_categories.php');
        exit;
    }
}

// Delete category
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    // Optional: check if products exist for this category before deleting (not done here)
    $stmt = $conn->prepare("DELETE FROM categories WHERE categories_id = ?");
    $stmt->bind_param('i', $del_id);
    $stmt->execute(); $stmt->close();
    header('Location: manage_categories.php');
    exit;
}

// Fetch All
$cats = $conn->query("SELECT * FROM categories ORDER BY categories_id DESC");
$edit_cat = null;
if (isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM categories WHERE categories_id = ? LIMIT 1");
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $r = $stmt->get_result();
    $edit_cat = $r->fetch_assoc();
    $stmt->close();
}
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Manage Categories</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Manage Categories</h1>
  <a href="index.php">← Back to Products</a>
  <hr>

  <?php if($edit_cat): ?>
    <h2>Edit Category</h2>
    <form method="post">
      <input type="hidden" name="id" value="<?= $edit_cat['categories_id'] ?>">
      <div class="form-row">
        <label>Name</label>
        <input type="text" name="name" value="<?= htmlspecialchars($edit_cat['name']) ?>">
      </div>
      <button class="btn btn-primary" name="edit_category" type="submit">Update</button>
    </form>
  <?php else: ?>
    <h2>Add Category</h2>
    <form method="post">
      <div class="form-row">
        <label>Name</label>
        <input type="text" name="name" placeholder="Category name">
      </div>
      <button class="btn btn-primary" name="add_category" type="submit">Add</button>
    </form>
  <?php endif; ?>

  <hr>
  <h2>All Categories</h2>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
      <?php if($cats && $cats->num_rows): while($c = $cats->fetch_assoc()): ?>
        <tr>
          <td><?= $c['categories_id'] ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td>
            <a href="?edit=<?= $c['categories_id'] ?>">Edit</a> |
            <a href="?delete=<?= $c['categories_id'] ?>" onclick="return confirm('Delete category?')">Delete</a>
          </td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="3">No categories.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
