<?php 
session_start();
include '../db.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle cancel request
if (isset($_POST['cancel_order_id'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['cancel_order_id']);
    
    // Cancel entire order only if it is still pending
    $sql = "UPDATE orders SET order_status = 'Cancelled' WHERE order_id = '$order_id' AND user_id = $user_id AND order_status = 'Pending'";
    mysqli_query($conn, $sql);
    
    header('Location: order_page.php');
    exit();
}


// Fetch user orders including order_status
$sql = "SELECT o.order_id, o.medicine_id, o.quantity, o.total_price, o.payment_type, o.order_address, 
               o.prescription_status, o.prescription_upload, o.order_date, o.order_status, 
               m.name AS medicine_name, m.price 
        FROM orders o 
        JOIN medicines m ON o.medicine_id = m.id 
        WHERE o.user_id = $user_id 
        ORDER BY o.order_date DESC";
$result = mysqli_query($conn, $sql);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Group orders by order_id
$grouped_orders = [];
foreach ($orders as $order) {
    $grouped_orders[$order['order_id']][] = $order;
}

$cart_count = 0;

foreach ($_SESSION['cart'][$user_id] as $medicine_id => $cart_item) {
    // If the item still exists in the database, increment the count
    $sql_check = "SELECT id FROM medicines WHERE id = $medicine_id";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        $cart_count++;
    } else {
        unset($_SESSION['cart'][$user_id][$medicine_id]);
    }
}

// Fetch user details including profile picture
$sql_user = "SELECT profile_pic FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $sql_user);
$user_data = mysqli_fetch_assoc($result_user);

// Check if profile picture exists; if not, use a default image
$profile_pic = !empty($user_data['profile_pic']) ? "../profile_uploads/" . htmlspecialchars($user_data['profile_pic']) : "../buttons/profile-button.png";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        header {
            background-color: white;
            text-align: center;
        }
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: white;
            padding: 10px 10px 5px 50px;
            position: sticky;
            top: 0;
            width: 92%;
            z-index: 1000;
        }
        .logo img {
            width:80px;
            height:70px;
        }
        .search-bar {
            flex: 1;
            max-width: 400px;
            display: flex;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            align-items: center;
            border: 1px solid rgb(182, 174, 174);
            padding-left:10px;
            margin-left:150px;
        }
        .search-bar input {
            border: none;
            flex: 1;
            padding: 8px;
            font-size: 1rem;
            outline: none;
        }
        .search-bar button {
            background: #1977F3;
            border: none;
            padding: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 40px;
        }
        .search-icon {
            width: 20px;
            height: 20px;
        }
        .cart a {
            font-size: 1.5rem;
            position: relative;
            text-decoration: none;
        }
        .cart img{
            width:60px;
            height:60px;
        }
        .cart-count {
            background:#1977F3;
            color: white;
            font-size: 0.8rem;
            padding: 3px 6px;
            border-radius: 50%;
            position: absolute;
            top: -5px;
            right: -10px;
        }
        .hamburger {
            font-size: 2rem;
            color: white;
            cursor: pointer;
            display: none;
        }
        @media (max-width: 768px) {
            .search-bar {
                max-width: 250px;
            }
        }
        @media (max-width: 576px) {
            .search-bar {
                display: none;
            }
            .hamburger {
                display: block;
            }
        }
        .profile button, .order button, .cart button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .profile button img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center;
        }
        .order button img, .cart button img {
            width: 60px;
            height:60px;
        }
        .rightbar{
            display:flex;
            gap:25px;
        }
        h1 {
            text-align: center;
            color: #1977F3;
            margin-bottom: 20px;
        }
        .table-container {
            margin: 0 20px;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            background-color: #fff;
            overflow: hidden;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #1977F3;
            color: white;
            font-weight: bold;
        }
    
        .status-pending { color: orange; }
        .status-approved { color: blue; }
        .status-shipped { color: purple; }
        .status-delivered { color: green; }
        .status-cancelled { color: red; }
        a.prescription-link {
            color: #1977F3;
            text-decoration: none;
        }
        a.prescription-link:hover {
            text-decoration: underline;
        }
        form input[type="submit"] {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            background-color: #ff4d4d;
            color: white;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #ff1a1a;
        }
        span.not-cancellable {
            color: gray;
        }
        #backbutton{
            display: inline-block;
            padding: 10px 20px;
            margin-bottom: 15px;
            background-color: #1977F3;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="home.php">
                    <img src="../buttons/logo.jpg" alt="Pharma Logo">
                </a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Search for medicines..." id="searchInput">
                <button type="submit" id="searchButton">
                    <img src="../buttons/search-button.png" alt="Search" class="search-icon">
                </button>
            </div>
            <div class="rightbar">
                <div class="profile">
                    <a href="profile.php">
                        <button type="submit">
                            <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-icon">
                        </button>
                    </a>
                </div>
                <div class="order">
                    <a href="order_page.php">
                        <button type="submit">
                            <img src="../buttons/order-button.png" alt="Orders" class="order-icon">
                        </button>
                    </a>
                </div>
                <div class="cart">
                    <a href="cart.php">
                        <button type="submit">
                            <img src="../buttons/cart-button.png" alt="Cart" class="cart-icon">
                        </button> 
                        <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </nav>
    </header>
    <h1>Your Orders</h1>
    <?php if (empty($orders)): ?>
        <p style="text-align:center">You have no orders yet.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Medicine</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Payment Type</th>
                    <th>Order Address</th>
                    <th>Prescription</th>
                    <th>Order Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php
                foreach ($grouped_orders as $order_id => $grouped_order): 
                    $first = true;
                    $rowspan = count($grouped_order);
                    foreach ($grouped_order as $order):
                ?>
                    <tr>
                        <?php if ($first): ?>
                            <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['order_id']); ?></td>
                        <?php endif; ?>
                        <td><?php echo htmlspecialchars($order['medicine_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($order['total_price']); ?></td>
                        <?php if ($first): ?>
                            <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['payment_type']); ?></td>
                            <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['order_address']); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if ($order['prescription_upload']): ?>
                                <a href="<?php echo htmlspecialchars($order['prescription_upload']); ?>" class="prescription-link" target="_blank">View</a>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </td>
                        <?php if ($first): ?>
                            <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($order['order_date']); ?></td>
                            <td rowspan="<?php echo $rowspan; ?>" class="status-<?php echo strtolower($order['order_status']); ?>">
                                <?php echo htmlspecialchars($order['order_status']); ?>
                            </td>
                            <td rowspan="<?php echo $rowspan; ?>">
                                <?php if ($order['order_status'] == 'Pending'): ?>
                                    <form method="POST" action="order_page.php">
                                        <input type="hidden" name="cancel_medicine_id" value="<?php echo $order['medicine_id']; ?>">
                                        <input type="hidden" name="cancel_order_id" value="<?php echo $order['order_id']; ?>">
                                        <input type="submit" value="Cancel" onclick="return confirm('Are you sure you want to cancel this item?');">
                                    </form>
                                <?php else: ?>
                                    <span class="not-cancellable">Not Cancellable</span>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php 
                        $first = false;
                    endforeach; 
                endforeach; 
                ?>
            </table>
        </div>
    <?php endif; ?>
</body>
</html>
