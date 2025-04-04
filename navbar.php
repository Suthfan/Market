<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Marketplace</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .navbar {
            background-color: #333;
            padding: 14px 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            font-size: 16px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .navbar a:hover {
            background-color: #575757;
            color: #f4f4f4;
            border-radius: 4px;
        }
        .navbar .right {
            display: flex;
            align-items: center;
        }
        .navbar .right a {
            margin-left: 15px;
        }
        .navbar .user-info {
            font-size: 14px;
            color: #ddd;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
            .navbar a {
                padding: 12px 20px;
                width: 100%;
                text-align: left;
            }
            .navbar .right {
                margin-top: 10px;
                width: 100%;
                justify-content: flex-start;
            }
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="left">
        <a href="index.php">Home</a>
        <a href="sell_product.php">Sell</a>
        <a href="view_cart.php">Cart</a>
    </div>

    <div class="right">
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
            <a href="profile.php"><span class="user-info">Welcome, <?php echo $_SESSION['username']; ?>!</span></a>
            <?php
                if (isset($_SESSION['user_id'])) {
                    $conn = new mysqli("localhost", "root", "", "online_marketplace");
                    if (!$conn->connect_error) {
                        $stmt = $conn->prepare("SELECT balance FROM users WHERE user_id = ?");
                        $stmt->bind_param("i", $_SESSION['user_id']);
                        $stmt->execute();
                        $stmt->bind_result($updated_balance);
                        if ($stmt->fetch()) {
                            echo '<span class="user-info">Balance: ' . $updated_balance . '</span>';
                            $_SESSION['balance'] = $updated_balance; // Update session balance
                        }
                        $stmt->close();
                        $conn->close();
                    }
                }
                ?>

        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="sign_up.php">Sign Up</a>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
