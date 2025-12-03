<?php
// customers.php - List all customers with links to add/edit/delete
require 'database.php';

$res = $conn->query("SELECT * FROM customers ORDER BY id DESC");
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Customers</h1>
    <a href="index.php">← Back to Products</a>
    
    <div class="top-links">
        <a href="add_customer.php" class="btn btn-primary">+ Add New Customer</a>
    </div>
    
    <h2>All Customers</h2>
    <?php if (isset($_GET['success'])): ?>
        <div class="success">Operation completed successfully!</div>
    <?php endif; ?>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th style="width: 20%;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($res && $res->num_rows > 0): ?>
                <?php while ($c = $res->fetch_assoc()): ?>
                    <tr>
                        <td><?= $c['id'] ?></td>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                        <td>
                            <a href="edit_customer.php?id=<?= $c['id'] ?>" class="btn btn-primary" style="padding: 6px 10px; font-size: 0.9em;">Edit</a>
                            <a href="delete_customer.php?id=<?= $c['id'] ?>" class="btn btn-danger" style="padding: 6px 10px; font-size: 0.9em;">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 20px;">No customers yet. <a href="add_customer.php">Add one now</a></td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
