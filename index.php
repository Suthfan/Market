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

// Fetch products with seller_id
$sql = "SELECT * FROM products";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products | Online Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
            text-align: center;
        }
        h1 {
            margin: 20px 0;
            color: #333;
        }
        .products {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .product-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .product-card h3 {
            margin: 10px 0;
            color: #333;
        }
        .product-card p {
            font-size: 14px;
            color: #666;
            margin: 5px 0;
        }
        .product-card input {
            width: 60px;
            padding: 5px;
            margin: 10px 0;
            text-align: center;
        }
        .product-card button {
            background: #28a745;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }
        .product-card button:hover {
            background: #218838;
        }
        .cart-link {
            display: block;
            margin-top: 20px;
            font-size: 16px;
            color: #007bff;
            text-decoration: none;
        }
        .cart-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome to the Online Marketplace</h1>
    <div class="products">
        <?php while ($product = $result->fetch_assoc()): ?>
            <div class="product-card">
                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                <p><?php echo htmlspecialchars($product['description']); ?></p>
                <p><strong>Price:</strong> $<?php echo htmlspecialchars($product['price']); ?></p>
                <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stock']); ?></p>
                <p><strong>Seller ID:</strong> <?php echo htmlspecialchars($product['seller_id']); ?></p>
                <p><strong>Product ID:</strong> <?php echo htmlspecialchars($product['id']); ?></p>
                
                <form method="POST" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <input type="hidden" name="seller_id" value="<?php echo $product['seller_id']; ?>"> <!-- Added seller_id -->
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>" required>
                    <button type="submit">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <a class="cart-link" href="view_cart.php">🛒 Check Cart</a>
</div>

</body>
</html>
