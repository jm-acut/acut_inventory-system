<?php
// edit_customer.php - Edit existing customer
require 'database.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = '';
$success = '';

if (!$id) {
    header('Location: customers.php');
    exit;
}

// Fetch customer
$stmt = $conn->prepare("SELECT * FROM customers WHERE id = ? LIMIT 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: customers.php');
    exit;
}

$customer = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    if (empty($name)) {
        $error = "Customer name is required.";
    } else {
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('sssi', $name, $email, $phone, $id);
            if ($stmt->execute()) {
                $success = "Customer updated successfully!";
                // Update displayed values
                $customer['name'] = $name;
                $customer['email'] = $email;
                $customer['phone'] = $phone;
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
    <title>Edit Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Edit Customer</h1>
    <a href="customers.php">← Back to Customers</a>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post" style="max-width: 500px; margin-top: 20px;">
        <div class="form-row">
            <label for="name">Customer Name *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($customer['name']) ?>" required>
        </div>
        <div class="form-row">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
        </div>
        <div class="form-row">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
        </div>
        <button class="btn btn-primary" type="submit">Update Customer</button>
    </form>
</div>
</body>
</html>
