<?php
// add_category.php - Add new category
require 'database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");
            if ($stmt) {
                $stmt->bind_param('s', $name);
                if ($stmt->execute()) {
                    $success = "Category added successfully!";
                    // Redirect after 1.5 seconds
                    header("Refresh: 1.5; url=manage_categories.php?success=added");
                    exit;
                }
                $stmt->close();
            } else {
                $error = "Database error: " . $conn->error;
            }
        } catch (mysqli_sql_exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $error = "This category name already exists.";
            } else {
                $error = "Error adding category: " . $e->getMessage();
            }
        }
    }
}
?>
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Category</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Add New Category</h1>
    <a href="manage_categories.php">← Back to Categories</a>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post" style="max-width: 500px; margin-top: 20px;">
        <div class="form-row">
            <label for="name">Category Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
        </div>
        <button class="btn btn-primary" type="submit">Add Category</button>
    </form>
</div>
</body>
</html>
