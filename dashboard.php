<?php
session_start();

// Check if waiter is logged in
if (!isset($_SESSION['waiter_id'])) {
    header("Location: login.php");
    exit;
}

// Establish database connection
$conn = new mysqli("localhost", "root", "", "hotel");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Retrieve selected category items if a category is chosen
$selected_category = isset($_GET['category']) ? $_GET['category'] : null;
$items = [];

if ($selected_category) {
    $stmt = $conn->prepare("SELECT * FROM menu_items WHERE category = ?");
    $stmt->bind_param("s", $selected_category);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
}

// Handle adding items to cart
if (isset($_POST['add_to_cart'])) {
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    $found = false;
    foreach ($_SESSION['cart'] as &$cart_item) {
        if ($cart_item['name'] == $item_name) {
            $cart_item['quantity'] += 1;
            $cart_item['total'] = $cart_item['price'] * $cart_item['quantity'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $_SESSION['cart'][] = [
            'name' => $item_name,
            'price' => $item_price,
            'quantity' => 1,
            'total' => $item_price
        ];
    }
}

// Handle removing items from cart
if (isset($_POST['remove_item'])) {
    $item_name = $_POST['item_name'];
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($cart_item) use ($item_name) {
        return $cart_item['name'] != $item_name;
    });
    $show_cart = true; // Stay on cart page after removing an item
} else {
    $show_cart = isset($_POST['show_cart']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="stylesheet2.css">
</head>
<body>
    <center>
<div class="dashboard-container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['waiter_id']); ?>!</h1>

    <?php if (!$show_cart): ?>
        <!-- Category Selection Links -->
        <div class="category-container">
            <a href="dashboard.php?category=Breakfast"><img  width ="250px"  height="140px" src="breakfast.jpeg" alt="Breakfast"></a>
            <a href="dashboard.php?category=Lunch"><img width="250px"  height="140px" src="lunch.jpeg" alt="Lunch"></a>
            <a href="dashboard.php?category=Dinner"><img width="250px"  height="140px" src="dinner.jpeg" alt="Dinner"></a>
            <a href="dashboard.php?category=Beverages"><img width="250px"  height="140px" src="beverages.jpeg" alt="Beverages"></a>
        </div>

        <!-- Display items based on selected category -->
        <?php if ($selected_category): ?>
            <h2><?php echo htmlspecialchars($selected_category); ?> Menu</h2>
            <table>
                <tr>
                    <th>Item</th>
                    <th>Price (₹)</th>
                    <th>Select</th>
                </tr>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>₹<?php echo number_format($item['price'], 2); ?></td>
                        <td>
                            <form method="POST" action="dashboard.php?category=<?php echo urlencode($selected_category); ?>">
                                <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['name']); ?>">
                                <input type="hidden" name="item_price" value="<?php echo htmlspecialchars($item['price']); ?>">
                                <button type="submit" name="add_to_cart">Select</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        
    <div class="one">
    
        <form method="POST">
            <button type="submit" name="show_cart">Add to Cart</button>
        </form>
    </div>
    <?php else: ?>
        <!-- Cart Display -->
        <div id="cart-container">
            <h2>Cart</h2>
            <?php if (!empty($_SESSION['cart'])): ?>
                <center>
                <table>
                    <tr>
                        <th>Item</th>
                        <th>Price (₹)</th>
                        <th>Quantity</th>
                        <th>Total (₹)</th>
                        <th>Remove</th>
                    </tr>
                    <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                        <tr>
                            <td><center><?php echo htmlspecialchars($item['name']); ?></center></td>
                            <td><center>₹<?php echo number_format($item['price'], 2); ?></center></td>
                            <td><center><?php echo $item['quantity']; ?></center></td>
                            <td><center>₹<?php echo number_format($item['total'], 2); ?></center></td>
                            <td>
                                <form method="POST" action="dashboard.php">
                                    <input type="hidden" name="item_name" value="<?php echo htmlspecialchars($item['name']); ?>">
                                    <input type="hidden" name="show_cart" value="1">
                                   <center> <button type="submit" name="remove_item">Remove</button></center>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                    </center>
                <p><strong>Grand Total: ₹<?php echo number_format(array_sum(array_column($_SESSION['cart'], 'total')), 2); ?></strong></p>
                
                <!-- Confirm Order Button -->
                <form method="POST" action="bill.php">
                    <button type="submit" name="confirm_order">Confirm Order</button>
                </form>
            <?php else: ?>
                <p>Your cart is empty.</p>
            <?php endif; ?>
           
            <form method="POST" action="dashboard.php">
                <button type="submit">Back to Menu</button>
            </form>
        </div>
    <?php endif; ?>
</div>
            </center>
</body>
</html>
