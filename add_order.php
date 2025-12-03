<?php
// add_order.php - Create new order (improved version of create_order.php)
require 'database.php';

$error = '';
$success = '';

// Fetch customers and products
$customers = $conn->query("SELECT id, name FROM customers ORDER BY name ASC");
$products = $conn->query("SELECT id, name, price FROM products ORDER BY name ASC");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id = (int)($_POST['customer_id'] ?? 0);
    $product_ids = $_POST['product_id'] ?? [];
    $quantities = $_POST['quantity'] ?? [];
    
    // Validate
    if (!$customer_id) {
        $error = "Please select a customer.";
    } elseif (empty($product_ids)) {
        $error = "Please select at least one product.";
    } else {
        // Filter valid items
        $items = [];
        for ($i = 0; $i < count($product_ids); $i++) {
            $pid = (int)$product_ids[$i];
            $qty = (int)$quantities[$i];
            if ($qty > 0 && $pid > 0) {
                $items[] = ['product_id' => $pid, 'quantity' => $qty];
            }
        }
        
        if (empty($items)) {
            $error = "Please enter valid quantities for selected products.";
        } else {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                $total = 0.0;
                
                // Create order
                $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, total) VALUES (?, ?)");
                $stmt_order->bind_param('id', $customer_id, $total);
                
                if (!$stmt_order->execute()) {
                    throw new Exception("Failed to create order: " . $stmt_order->error);
                }
                
                $order_id = $stmt_order->insert_id;
                $stmt_order->close();
                
                // Insert order items
                $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt_price = $conn->prepare("SELECT price FROM products WHERE id = ?");
                
                foreach ($items as $item) {
                    $stmt_price->bind_param('i', $item['product_id']);
                    $stmt_price->execute();
                    $result = $stmt_price->get_result();
                    
                    if ($result->num_rows === 0) {
                        throw new Exception("Product not found: " . $item['product_id']);
                    }
                    
                    $price_row = $result->fetch_assoc();
                    $price = (float)$price_row['price'];
                    $line_total = $price * $item['quantity'];
                    $total += $line_total;
                    
                    $stmt_item->bind_param('iiid', $order_id, $item['product_id'], $item['quantity'], $price);
                    
                    if (!$stmt_item->execute()) {
                        throw new Exception("Failed to add item: " . $stmt_item->error);
                    }
                }
                
                $stmt_price->close();
                $stmt_item->close();
                
                // Update order total
                $stmt_total = $conn->prepare("UPDATE orders SET total = ? WHERE id = ?");
                $stmt_total->bind_param('di', $total, $order_id);
                
                if (!$stmt_total->execute()) {
                    throw new Exception("Failed to update total: " . $stmt_total->error);
                }
                
                $stmt_total->close();
                
                // Commit transaction
                $conn->commit();
                $success = "Order created successfully!";
                
                // Redirect after 2 seconds
                header("Refresh: 2; url=view_orders.php?id=" . $order_id);
                
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Error creating order: " . $e->getMessage();
            }
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add New Order</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
    <h1>Create New Order</h1>
    <a href="view_orders.php">← Back to Orders</a>
    
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    
    <?php if (!empty($success)): ?>
        <div class="success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    
    <form method="post" style="margin-top: 20px;">
        <div class="form-row">
            <label for="customer">Select Customer *</label>
            <select id="customer" name="customer_id" required>
                <option value="">-- Choose a customer --</option>
                <?php if ($customers && $customers->num_rows > 0): ?>
                    <?php while ($c = $customers->fetch_assoc()): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endwhile; ?>
                <?php else: ?>
                    <option disabled>No customers available</option>
                <?php endif; ?>
            </select>
        </div>
        
        <h2>Select Products *</h2>
        <p class="small">Enter quantity for each product you want to add to this order</p>
        
        <?php if ($products && $products->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th style="width: 10%;">Include</th>
                        <th>Product Name</th>
                        <th style="width: 15%;">Price</th>
                        <th style="width: 15%;">Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $products->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="product_id[]" value="<?= $p['id'] ?>" 
                                       onchange="toggleQuantity(this, 'qty_<?= $p['id'] ?>')">
                            </td>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td>₱<?= number_format($p['price'], 2) ?></td>
                            <td>
                                <input type="number" id="qty_<?= $p['id'] ?>" name="quantity[]" 
                                       value="1" min="0" max="999" disabled>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="error">No products available. <a href="add_product.php">Add products first</a></p>
        <?php endif; ?>
        
        <button class="btn btn-primary" type="submit" style="margin-top: 20px;">Create Order</button>
    </form>
</div>

<script>
function toggleQuantity(checkbox, qtyId) {
    var qtyInput = document.getElementById(qtyId);
    if (checkbox.checked) {
        qtyInput.disabled = false;
        if (qtyInput.value == 0) qtyInput.value = 1;
    } else {
        qtyInput.disabled = true;
        qtyInput.value = 0;
    }
}
</script>
</body>
</html>
