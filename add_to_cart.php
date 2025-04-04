<?php
session_start();
include 'navbar.php';

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

// Get product, seller, and quantity from form (with validation)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'], $_POST['quantity'], $_POST['seller_id'])) {
    $product_id = intval($_POST['product_id']);  // Ensure integer
    $quantity = intval($_POST['quantity']);      // Ensure integer
    $user_id = $_SESSION['user_id'];
    $seller_id = intval($_POST['seller_id']);   // Get seller_id

    // Check if the product exists in the cart
    $stmt = $conn->prepare("SELECT quantity FROM cart WHERE user_id = ? AND product_id = ? AND seller_id = ?");
    $stmt->bind_param("iii", $user_id, $product_id, $seller_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ? AND seller_id = ?");
        $stmt->bind_param("iiii", $quantity, $user_id, $product_id, $seller_id);
    } else {
        // Add new item to cart with seller_id
        $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity, seller_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $seller_id);
    }

    if ($stmt->execute()) {
        $message = "✅ Product successfully added to cart!";
    } else {
        $message = "❌ Error adding product to cart: " . $conn->error;
    }

    $stmt->close();
} else {
    $message = "❌ Invalid request.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart Update</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 50%;
            margin: 100px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
        }
        .message {
            font-size: 18px;
            color: green;
            margin-bottom: 20px;
        }
        .error {
            color: red;
        }
        .btn {
            display: inline-block;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
            color: white;
            margin: 10px;
        }
        .btn-primary {
            background: #007bff;
        }
        .btn-primary:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Shopping Cart</h1>
    <p class="message <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
        <?php echo $message; ?>
    </p>
    <a class="btn btn-primary" href="index.php">🛍️ Continue Shopping</a>
    <a class="btn btn-secondary" href="view_cart.php">🛒 View Cart</a>
</div>

</body>
</html>
