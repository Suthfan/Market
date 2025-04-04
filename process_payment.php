<?php
session_start();

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_marketplace";

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$payment_success = true;
$total_amount = 0;
$status = "";
$message = "";

// Calculate total cart value
$sql = "SELECT 
            cart.product_id, 
            cart.quantity, 
            cart.seller_id,
            products.price
        FROM cart 
        JOIN products ON cart.product_id = products.id 
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $total_amount += $row['quantity'] * $row['price'];
    $cart_items[] = $row;
}

// Fetch current balance
$stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res_balance = $stmt->get_result();
$row_balance = $res_balance->fetch_assoc();
$user_balance = $row_balance['balance'];

if ($user_balance < $total_amount) {
    $status = "fail";
    $message = "❌ Transaction failed: Insufficient balance. Please add funds to your account.";
} elseif ($payment_success && count($cart_items) > 0) {
    foreach ($cart_items as $row) {
        $product_id = $row['product_id'];
        $cart_quantity = $row['quantity'];
        $seller_id = $row['seller_id'];
        $price = $row['price'];

        // Get stock
        $stmt_stock = $conn->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt_stock->bind_param("i", $product_id);
        $stmt_stock->execute();
        $res_stock = $stmt_stock->get_result();
        $stock_row = $res_stock->fetch_assoc();
        $stock = $stock_row['stock'];

        $new_stock = max(0, $stock - $cart_quantity);

        // Update stock
        $stmt_update = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $stmt_update->bind_param("ii", $new_stock, $product_id);
        $stmt_update->execute();

        // Delete if stock is zero
        if ($new_stock == 0) {
            // First delete from cart to satisfy foreign key constraint
            $stmt_clear_product_cart = $conn->prepare("DELETE FROM cart WHERE product_id = ?");
            $stmt_clear_product_cart->bind_param("i", $product_id);
            $stmt_clear_product_cart->execute();
        
            // Then delete from products table
            $delete_product_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $delete_product_stmt->bind_param("i", $product_id);
            $delete_product_stmt->execute();
        }
        

        // Log transaction
        $total_price = $cart_quantity * $price;
        $stmt_txn = $conn->prepare("INSERT INTO transactions (buyer_id, seller_id, product_id, quantity, total_amount, transaction_time) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt_txn->bind_param("iiiid", $user_id, $seller_id, $product_id, $cart_quantity, $total_price);
        $stmt_txn->execute();

        // Update seller balance
        $stmt_add = $conn->prepare("UPDATE users SET balance = balance + ? WHERE user_id = ?");
        $stmt_add->bind_param("di", $total_price, $seller_id);
        $stmt_add->execute();
    }

    // Deduct total from buyer balance
    $stmt_deduct = $conn->prepare("UPDATE users SET balance = ? WHERE user_id = ?");
    $new_balance = $user_balance - $total_amount;
    $stmt_deduct->bind_param("di", $new_balance, $user_id);
    $stmt_deduct->execute();

    // Clear cart
    $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt_clear->bind_param("i", $user_id);
    $stmt_clear->execute();

    $status = "success";
    $message = "✅ Payment successful! Transactions completed, cart cleared.";
} else {
    $status = "fail";
    $message = "❌ Transaction failed. Please try again.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction Status</title>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: Arial, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }
        .message-box {
            padding: 30px 40px;
            border-radius: 10px;
            background-color: <?= $status === "success" ? "#d4edda" : "#f8d7da" ?>;
            color: <?= $status === "success" ? "#155724" : "#721c24" ?>;
            border: 2px solid <?= $status === "success" ? "#c3e6cb" : "#f5c6cb" ?>;
            text-align: center;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .message-box h2 {
            margin: 0 0 10px;
        }
        .back-link {
            margin-top: 20px;
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            background-color: #007bff;
            color: #fff;
            border-radius: 6px;
            transition: 0.3s;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="message-box">
    <h2><?= $message ?></h2>
    <a href="view_cart.php" class="back-link">🔙 Back to Cart</a>
</div>
</body>
</html>
