<?php
session_start();
if (!isset($_SESSION['waiter_id']) || empty($_SESSION['cart'])) {
    header("Location: dashboard.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "hotel");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Calculate total amount
$total_amount = array_sum(array_column($_SESSION['cart'], 'total'));

// Insert order into orders table
$order_stmt = $conn->prepare("INSERT INTO orders (waiter_id, total_amount) VALUES (?, ?)");
$order_stmt->bind_param("id", $_SESSION['waiter_id'], $total_amount);
$order_stmt->execute();
$order_id = $order_stmt->insert_id;
$order_stmt->close();

// Insert each item into order_items table
$item_stmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, item_price, quantity, total) VALUES (?, ?, ?, ?, ?)");
foreach ($_SESSION['cart'] as $item) {
    $item_stmt->bind_param("isddi", $order_id, $item['name'], $item['price'], $item['quantity'], $item['total']);
    $item_stmt->execute();
}
$item_stmt->close();

// Clear the cart after storing the order
unset($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill Receipt</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .receipt {
            width: 300px;
            margin: 0 auto;
            padding: 20px;
            border: 1px dashed #333;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .receipt h1 {
            font-size: 20px;
            margin: 0;
            padding: 5px;
        }
        .receipt p, .receipt th, .receipt td {
            font-size: 14px;
        }
        .receipt table {
            width: 100%;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .receipt th, .receipt td {
            padding: 5px;
            border-bottom: 1px solid #ddd;
        }
        .receipt .total {
            font-weight: bold;
        }
        .print-btn {
            margin-top: 20px;
            padding: 8px 16px;
            font-size: 14px;
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .print-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <center>
    <div class="receipt" id="receipt">
        <h1>Royal Grand Inn</h1>
        <p><strong>Order ID:</strong> <?php echo $order_id; ?></p>
        <p><strong>Waiter ID:</strong> <?php echo htmlspecialchars($_SESSION['waiter_id']); ?></p>
        <p><strong>Date:</strong> <?php echo date("Y-m-d H:i:s"); ?></p>
        
        <table>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Total (₹)</th>
            </tr>
            <?php
            // Display items in the receipt
            $item_result = $conn->query("SELECT * FROM order_items WHERE order_id = $order_id");
            while ($item = $item_result->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₹<?php echo number_format($item['total'], 2); ?></td>
                </tr>
            <?php endwhile; ?>
        </table>

        <p class="total">Grand Total: ₹<?php echo number_format($total_amount, 2); ?></p>

        <button class="print-btn" onclick="printReceipt()">Print Bill</button>
    </div>

    <script>
        function printReceipt() {
            var printContents = document.getElementById('receipt').innerHTML;
            var originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.href = 'dashboard.php';
        }
    </script>
    </center>
</body>
</html>

<?php
$conn->close();
?>
