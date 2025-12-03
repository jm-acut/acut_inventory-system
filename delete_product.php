<?php
// delete_product.php
require 'database.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id) {
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute(); $stmt->close();
}
header('Location: index.php'); exit;
