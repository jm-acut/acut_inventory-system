<?php
// customers.php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_customer'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    if ($name !== '') {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $name, $email, $phone);
        $stmt->execute(); $stmt->close();
        header('Location: customers.php');
        exit;
    }
}
$res = $conn->query("SELECT * FROM customers ORDER BY id DESC");
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Customers</title><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
  <h1>Customers</h1>
  <a href="index.php">← Back</a>
  <h2>Add Customer</h2>
  <form method="post">
    <div class="form-row"><label>Name</label><input type="text" name="name" required></div>
    <div class="form-row"><label>Email</label><input type="text" name="email"></div>
    <div class="form-row"><label>Phone</label><input type="text" name="phone"></div>
    <button class="btn btn-primary" name="add_customer" type="submit">Add</button>
  </form>
  <hr>
  <h2>All Customers</h2>
  <table>
    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th></tr></thead>
    <tbody>
      <?php if($res && $res->num_rows): while($c = $res->fetch_assoc()): ?>
        <tr>
          <td><?= $c['id'] ?></td>
          <td><?= htmlspecialchars($c['name']) ?></td>
          <td><?= htmlspecialchars($c['email']) ?></td>
          <td><?= htmlspecialchars($c['phone']) ?></td>
        </tr>
      <?php endwhile; else: ?>
        <tr><td colspan="4">No customers yet.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
