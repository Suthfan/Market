<?php
session_start();

// DB connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "online_marketplace";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Auth check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$user_sql = "SELECT username, balance FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Fetch transactions (as seller or buyer)
$txn_sql = "SELECT 
                t.total_amount, 
                t.quantity, 
                t.transaction_time, 
                u1.username AS buyer, 
                u2.username AS seller, 
                p.name AS product_name 
            FROM transactions t
            JOIN users u1 ON t.buyer_id = u1.user_id
            JOIN users u2 ON t.seller_id = u2.user_id
            JOIN products p ON t.product_id = p.id
            WHERE t.buyer_id = ? OR t.seller_id = ?
            ORDER BY t.transaction_time DESC";
$txn_stmt = $conn->prepare($txn_sql);
$txn_stmt->bind_param("ii", $user_id, $user_id);
$txn_stmt->execute();
$txn_result = $txn_stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f8f9fa; }
        .container { max-width: 900px; margin: auto; background: #fff; padding: 20px; border-radius: 10px; }
        h2 { border-bottom: 2px solid #007bff; padding-bottom: 5px; color: #333; }
        .balance { font-size: 22px; font-weight: bold; color: #28a745; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #dee2e6; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #e9ecef; }
    </style>
</head>
<body>

<div class="container">
    <h2>👤 Profile</h2>
    <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
    <p class="balance">💰 Balance: $<?= number_format($user['balance'], 2) ?></p>

    <h2>📜 Transactions</h2>
    <?php if ($txn_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Buyer</th>
                <th>Seller</th>
                <th>Quantity</th>
                <th>Total Amount</th>
                <th>Date</th>
            </tr>
            <?php while ($txn = $txn_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($txn['product_name']) ?></td>
                    <td><?= htmlspecialchars($txn['buyer']) ?></td>
                    <td><?= htmlspecialchars($txn['seller']) ?></td>
                    <td><?= (int)$txn['quantity'] ?></td>
                    <td>$<?= number_format($txn['total_amount'], 2) ?></td>
                    <td><?= $txn['transaction_time'] ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No transactions found.</p>
    <?php endif; ?>
</div>

</body>
</html>
