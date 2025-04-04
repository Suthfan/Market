<?php include 'navbar.php'; ?>

<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get logged-in seller ID
$seller_id = $_SESSION['user_id'];

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['increase_stock'])) {
        // Update stock for an existing product
        $product_id = $_POST['product_id'];
        $additional_stock = $_POST['additional_stock'];

        $update_stmt = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
        $update_stmt->bind_param("ii", $additional_stock, $product_id);
        if ($update_stmt->execute()) {
            echo "<p class='success'>Stock updated successfully!</p>";
        } else {
            echo "<p class='error'>Error updating stock: " . $update_stmt->error . "</p>";
        }
    } else {
        // Insert new product
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = $_POST['price'];
        $stock = $_POST['stock'];

        // Check if product already exists for the seller
        $stmt = $conn->prepare("SELECT id FROM products WHERE name = ? AND seller_id = ?");
        $stmt->bind_param("si", $name, $seller_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<p class='error'>Product already exists. Use the stock update option below.</p>";
        } else {
            // Insert new product
            $insert_stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, seller_id) VALUES (?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssdii", $name, $description, $price, $stock, $seller_id);
            if ($insert_stmt->execute()) {
                echo "<p class='success'>Product listed successfully!</p>";
            } else {
                echo "<p class='error'>Error adding product: " . $insert_stmt->error . "</p>";
            }
        }
    }
}

// Fetch existing products for the seller
$product_stmt = $conn->prepare("SELECT id, name, stock FROM products");
$product_stmt->execute();
$product_result = $product_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell a Product</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #343a40;
            color: white;
            padding: 20px;
            text-align: center;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .container {
            width: 60%;
            margin: 30px auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            font-weight: bold;
            color: #333;
        }
        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }
        textarea {
            height: 120px;
        }
        button[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background-color: #0056b3;
        }
        .success, .error {
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
        .btn {
            color: #007bff;
            text-decoration: none;
        }
        .btn:hover {
            color: #0056b3;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ccc;
        }
        th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1>Online Marketplace</h1>
    </header>

    <div class="container">
        <h2>Sell a Product</h2>
        <form method="POST">
            <div class="form-group">
                <label for="name">Product Name:</label>
                <input type="text" name="name" id="name" required>
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" required></textarea>
            </div>

            <div class="form-group">
                <label for="price">Price ($):</label>
                <input type="number" name="price" id="price" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="stock">Stock Quantity:</label>
                <input type="number" name="stock" id="stock" required>
            </div>

            <button type="submit">Submit Product</button>
        </form>

        <h2>Update Existing Stock</h2>
        <?php if ($product_result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Product Name</th>
                    <th>Current Stock</th>
                    <th>Increase Stock</th>
                </tr>
                <?php while ($row = $product_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['stock']; ?></td>
                    <td>
                        <form method="POST" style="display: flex; justify-content: center; align-items: center;">
                            <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                            <input type="number" name="additional_stock" min="1" required>
                            <button type="submit" name="increase_stock">Update</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p>No products found. List a new product above.</p>
        <?php endif; ?>

        <p class="back-link"><a href="index.php" class="btn">Back to Home</a></p>
    </div>
</body>
</html>
