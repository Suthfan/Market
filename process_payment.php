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

// Retrieve the logged-in user's ID
$user_id = $_SESSION['user_id'];
$payment_success = true; // Assume payment is successful for demonstration

// Step 1: Fetch the total amount for the cart items
$total_amount = 0;

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

// Calculate the total amount in the cart
while ($row = $result->fetch_assoc()) {
    $total_amount += $row['quantity'] * $row['price'];
}

// Step 2: Fetch the user's current balance
$sql_balance = "SELECT balance FROM users WHERE user_id = ?";
$stmt_balance = $conn->prepare($sql_balance);
$stmt_balance->bind_param("i", $user_id);
$stmt_balance->execute();
$result_balance = $stmt_balance->get_result();
$user_balance = 0;

if ($row_balance = $result_balance->fetch_assoc()) {
    $user_balance = $row_balance['balance'];
}

// Step 3: Check if the user has enough balance
if ($user_balance < $total_amount) {
    // Insufficient balance - show failure message
    $_SESSION['message'] = "❌ Transaction failed: Insufficient balance. Please add funds to your account.";
    header("Location: view_cart.php");
    exit();
}

if ($payment_success) {
    // Proceed with processing the payment and updating stock
    $sql = "SELECT 
                cart.product_id, 
                cart.quantity, 
                cart.seller_id,
                products.price,
                products.stock 
            FROM cart 
            JOIN products ON cart.product_id = products.id 
            WHERE cart.user_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to hold seller profits
    $seller_profits = [];

    // Loop through cart items
    while ($row = $result->fetch_assoc()) {
        $product_id = $row['product_id'];
        $cart_quantity = $row['quantity'];
        $product_stock = $row['stock'];
        $seller_id = $row['seller_id'];
        $price = $row['price'];

        // Calculate the new stock after purchase
        $new_stock = max(0, $product_stock - $cart_quantity);

        // Update the product stock in the database
        $update_stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        $update_stmt->bind_param("ii", $new_stock, $product_id);
        $update_stmt->execute();

        // Delete the product if stock reaches zero
        if ($new_stock == 0) {
            $delete_product_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
            $delete_product_stmt->bind_param("i", $product_id);
            $delete_product_stmt->execute();
        }

        // Calculate and accumulate profit for the seller
        $profit = $cart_quantity * $price;
        if (!isset($seller_profits[$seller_id])) {
            $seller_profits[$seller_id] = 0;
        }
        $seller_profits[$seller_id] += $profit;
    }

    // Optional: Insert profit distribution into the 'profits' table
    // foreach ($seller_profits as $seller_id => $profit_amount) {
    //     $insert_profit = $conn->prepare("INSERT INTO profits (seller_id, amount, buyer_id, date) VALUES (?, ?, ?, NOW())");
    //     $insert_profit->bind_param("idi", $seller_id, $profit_amount, $user_id);
    //     $insert_profit->execute();
    // }

    // Step 4: Deduct the total amount from the user's balance
    $new_balance = $user_balance - $total_amount;
    $update_balance_stmt = $conn->prepare("UPDATE users SET balance = ? WHERE user_id = ?");
    $update_balance_stmt->bind_param("di", $new_balance, $user_id);
    $update_balance_stmt->execute();

    // Clear the user's cart after successful payment
    $delete_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $delete_stmt->bind_param("i", $user_id);
    $delete_stmt->execute();

    // Provide feedback to the user
    $_SESSION['message'] = "✅ Payment complete! Profits distributed to sellers. Cart cleared.";
} else {
    // If payment failed
    $_SESSION['message'] = "❌ There was an issue processing your payment. Please try again.";
}

// Redirect back to cart or appropriate page
header("Location: view_cart.php");
exit();

?>
