<?php
// manage_categories.php - List categories with links to add/edit/delete
require 'database.php';

// Fetch All categories
$cats = $conn->query("SELECT * FROM categories ORDER BY categories_id DESC");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Manage Categories</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Manage Categories</h1>
    <a href="index.php">← Back to Products</a>
    
    <div class="top-links">
        <a href="add_category.php" class="btn btn-primary">+ Add New Category</a>
    </div>
    
    <h2>All Categories</h2>
    <?php if (isset($_GET['success'])): ?>
        <div class="success">Category operation completed successfully!</div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($cats && $cats->num_rows > 0): ?>
                <?php while ($c = $cats->fetch_assoc()): ?>
                    <tr>
                        <td><?= $c['categories_id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td>
                            <a href="edit_category.php?id=<?= $c['categories_id'] ?>" class="btn btn-primary" style="padding: 6px 10px; font-size: 0.9em;">Edit</a>
                            <a href="delete_category.php?id=<?= $c['categories_id'] ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.9em;">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px;">No categories yet. <a href="add_category.php">Add one now</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
