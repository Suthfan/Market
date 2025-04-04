<?php
session_start();
session_unset();  // Clear all session variables
session_destroy(); // Destroy the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .logout-container {
            text-align: center;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 300px;
            transition: transform 0.3s ease;
        }
        .logout-container h2 {
            color: #4caf50;
            margin-bottom: 20px;
        }
        .logout-container p {
            color: #777;
            margin-bottom: 30px;
        }
        .logout-container button {
            background-color: #4caf50;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .logout-container button:hover {
            background-color: #45a049;
        }
        .logout-container a {
            color: #4caf50;
            text-decoration: none;
            font-size: 14px;
            display: block;
            margin-top: 15px;
        }
        .logout-container a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        // Redirect to login page after 2 seconds
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 2000); // 2000 ms = 2 seconds
    </script>
</head>
<body>
    <div class="logout-container">
        <h2>Successfully Logged Out</h2>
        <p>Thank you for using our platform. You have been logged out.</p>
        <p>Redirecting to login page...</p>
    </div>
</body>
</html>
