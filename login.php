<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "hotel";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $waiter_id = $_POST['waiter_id'];
    $waiter_password = $_POST['waiter_password'];

    // SQL query to check the id and password
    $sql = "SELECT * FROM waiters WHERE waiter_id = '$waiter_id' AND waiter_password = '$waiter_password'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Successful login
        $_SESSION['waiter_id'] = $waiter_id;
        echo "<script>alert('Login successful!');</script>";
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit;
    } else {
        $error = "Invalid ID or Password!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Management Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .logo-container {
            margin-bottom: 20px;
            text-align: center;
        }

        .logo-container img {
            width: 120px;
            height: auto;
            border-radius: 20px;
            margin-bottom: 10px;
        }

        .login-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
            font-weight: 700;
            font-size: 24px;
            text-align: center;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
        }

        .error {
            color: #dc3545;
            margin-bottom: 15px;
            font-weight: 500;
            text-align: center;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 480px) {
            body {
                padding: 15px;
            }

            .logo-container img {
                width: 100px;
            }

            .login-container {
                padding: 20px;
            }

            h2 {
                font-size: 20px;
                margin-bottom: 20px;
            }

            .input-group input {
                padding: 10px 12px;
                font-size: 14px;
            }

            .btn {
                padding: 12px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="logo-container">
        <img src="mytiffen.jpeg" alt="Hotel Logo">
    </div>
    
    <div class="login-container">
        <h2>Hotel Management</h2>

        <?php if ($error) : ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label for="waiter_id">Waiter ID</label>
                <input type="text" id="waiter_id" name="waiter_id" required>
            </div>
            <div class="input-group">
                <label for="waiter_password">Password</label>
                <input type="password" id="waiter_password" name="waiter_password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </div>
</body>
</html>
