<?php include 'navbar.php'; ?>

<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user ID from session
$user_id = $_SESSION['user_id'];

// Fetch cart items for the logged-in user
$sql = "SELECT c.product_id, c.seller_id, p.name, p.price, c.quantity
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize total amount
$total_amount = 0;
$cart_items = [];

while ($row = $result->fetch_assoc()) {
    $item_total = $row['price'] * $row['quantity'];
    $total_amount += $item_total;
    $cart_items[] = $row;
}

// If cart is empty, show a message
if (empty($cart_items)) {
    echo "<p>Your cart is empty. <a href='index.php'>Start Shopping</a></p>";
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
            margin: 100px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        .cart-item {
            margin-bottom: 15px;
            padding: 10px;
            background: #f1f1f1;
            border-radius: 5px;
        }
        .cart-item p {
            margin: 5px 0;
        }
        .total {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 20px;
        }
        .btn:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Checkout</h1>

    <!-- Display Cart Items -->
    <?php foreach ($cart_items as $item): ?>
        <div class="cart-item">
            <p><strong><?php echo $item['name']; ?></strong></p>
            <p>Price: $<?php echo number_format($item['price'], 2); ?></p>
            <p>Quantity: <?php echo $item['quantity']; ?></p>
            <p>Seller Id: <?php echo $item['seller_id']; ?></p>
            <p>Total: $<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
        </div>
    <?php endforeach; ?>

    <!-- Display Total Amount -->
    <div class="total">
        <p>Total Amount: $<?php echo number_format($total_amount, 2); ?></p>
    </div>

    <!-- Payment Form -->
    <form action="process_payment.php" method="POST">
        <input type="hidden" name="total_amount" value="<?php echo $total_amount; ?>">
        <button type="submit" class="btn">Proceed to Payment</button>
    </form>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
