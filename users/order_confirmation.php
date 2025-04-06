<?php
session_start();

// Include database connection
include '../db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id']; // Ensure user_id is stored in session on login

// Fetch the last order placed by the user
$sql = "SELECT * FROM orders WHERE user_id = $user_id ORDER BY order_date DESC LIMIT 1";
$result = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($result);

// Fetch the order details
$order_items = [];
if ($order) {
    $order_id = $order['order_id'];
    $sql = "SELECT o.*, m.name, m.price, m.image FROM orders o 
            JOIN medicines m ON o.medicine_id = m.id
            WHERE o.order_id = $order_id";
    $order_items_result = mysqli_query($conn, $sql);
    while ($item = mysqli_fetch_assoc($order_items_result)) {
        $order_items[] = $item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Pharma Website</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #1977F3;
            margin-bottom: 20px;
        }

        .order-summary {
            margin-top: 30px;
        }

        .order-summary h3 {
            color: #1977F3;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .order-summary .order-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            background-color: #fafafa;
        }

        .order-summary .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .order-summary .item-details {
            flex: 1;
            padding-left: 15px;
        }

        .order-summary .item-name {
            font-weight: bold;
            color: #1977F3;
        }

        .order-summary .item-price {
            color: #555;
            font-size: 1.1rem;
            margin-top: 5px;
        }

        .order-summary .item-quantity {
            color: #555;
            font-size: 1.1rem;
            margin-top: 5px;
        }

        .total-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1977F3;
            margin-top: 20px;
            text-align: right;
        }

        .thank-you-message {
            text-align: center;
            margin-top: 30px;
            font-size: 1.2rem;
            color: #333;
        }

        .back-home-btn {
            display: block;
            background-color: #1977F3;
            color: white;
            text-align: center;
            padding: 15px;
            font-size: 1.1rem;
            border-radius: 8px;
            margin-top: 30px;
            text-decoration: none;
            width: 200px;
            margin: 0 auto;
        }

        .back-home-btn:hover {
            background-color: #135abe;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Order Confirmation</h2>

    <?php if ($order): ?>
        <div class="thank-you-message">
            <img src="../buttons/correct.png" style="width:70px;height:70px" alt="Complete" class="complete-icon">
            <p>Thank you for your order! Your order has been successfully placed.</p>
            <p>Your order ID: <strong>#<?php echo $order['order_id']; ?></strong></p>
            <p>We will process your order soon. You can track your order in your account.</p>
        </div>

        <div class="order-summary">
            <h3>Order Summary</h3>

            <?php
            $total_price = 0;
            foreach ($order_items as $item) {
                $total_price += $item['price'] * $item['quantity'];
            }
            ?>

            <?php foreach ($order_items as $item): ?>
                <div class="order-item">
                    <div class="item-image">
                        <img src="<?php echo $item['image'] ?: 'placeholder.png'; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    </div>
                    <div class="item-details">
                        <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                        <div class="item-price">Price: Rs <?php echo $item['price']; ?></div>
                        <div class="item-quantity">Quantity: <?php echo $item['quantity']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="total-price">
                Total Price: Rs <?php echo $total_price; ?>
            </div>
        </div>

        <a href="home.php" class="back-home-btn">Back to Home</a>
    <?php else: ?>
        <p>No order found.</p>
    <?php endif; ?>
</div>

</body>
</html>
