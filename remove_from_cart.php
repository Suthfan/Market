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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cart_id'])) {
    $cart_id = $_POST['cart_id'];

    // Remove item from cart
    $sql = "DELETE FROM cart WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cart_id);
    if ($stmt->execute()) {
        header("Location: view_cart.php");
        exit();
    } else {
        echo "Error removing item.";
    }
}
?>
