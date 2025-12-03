<?php
// edit_category.php - Edit existing category
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if (!$id) {
    header('Location: manage_categories.php');
    exit;
}

// Fetch category
$stmt = $conn->prepare("SELECT * FROM categories WHERE categories_id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: manage_categories.php');
    exit;
}

$category = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    
    if (empty($name)) {
        $error = "Category name is required.";
    } else {
        $stmt = $conn->prepare("UPDATE categories SET name = ? WHERE categories_id = ?");
        if ($stmt) {
            $stmt->bind_param('si', $name, $id);
            if ($stmt->execute()) {
                $success = "Category updated successfully!";
                $category['name'] = $name; // Update displayed value
            } else {
                if (strpos($stmt->error, 'Duplicate entry') !== false) {
                    $error = "This category name already exists.";
                } else {
                    $error = "Error: " . $stmt->error;
                }
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
    <title>Edit Category</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Edit Category</h1>
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
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        <button class="btn btn-primary" type="submit">Update Category</button>
    </form>
</div>
</body>
</html>
