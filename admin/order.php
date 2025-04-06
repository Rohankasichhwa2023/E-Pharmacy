<?php   
session_start();
include '../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

$filter = isset($_GET['status']) ? $_GET['status'] : 'All';

$sql = "SELECT o.order_id, o.medicine_id, o.quantity, o.total_price, o.payment_type, 
               o.order_address, o.prescription_status, o.prescription_upload, o.order_date, 
               o.order_status, 
               u.name AS user_name, u.phone AS user_phone, 
               m.name AS medicine_name, m.price 
        FROM orders o 
        JOIN users u ON o.user_id = u.id
        JOIN medicines m ON o.medicine_id = m.id";

if ($filter !== 'All') {
    $sql .= " WHERE o.order_status = '$filter'";
}

$sql .= " ORDER BY o.order_date DESC, o.order_id";

$result = mysqli_query($conn, $sql);
$orders = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Group orders by order_id for rowspan
$order_count = [];
foreach ($orders as $order) {
    $order_id = $order['order_id'];
    if (!isset($order_count[$order_id])) {
        $order_count[$order_id] = 0;
    }
    $order_count[$order_id]++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - All Orders</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        /* Navbar */
        .navbar {
            background-color: #1977F3;
            padding: 10px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .navbar .active {
            background-color: #fff;
            color: #1977F3;
            font-weight: bold;
        }

        /* Container */
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #1977F3;
        }

        /* Responsive table container */
        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            min-width: 800px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #1977F3;
            color: white;
        }

        /* Status Colors */
        .status-pending { color: orange; }
        .status-approved { color: blue; }
        .status-shipped { color: purple; }
        .status-delivered { color: green; }
        .status-cancelled { color: red; }

        /* Prescription Link */
        .prescription-link {
            color: blue;
            text-decoration: none;
        }

        .prescription-link:hover {
            text-decoration: underline;
        }

        /* Back Button */
        .back-button {
            background-color: #1977F3;
            text-decoration: none;
            padding: 10px;
            margin-top: 10px;
            width: 40px;
            height: 15px;
            cursor: pointer;
        }

        .back-button a {
            text-decoration: none;
            color: white;
        }
        .back-link {
            display: inline-block;
            padding: 10px 20px;
            background-color: #1977F3;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            border: none;
            cursor: pointer;
            margin-bottom: 20px;
            margin-top:10px;
        }
    </style>
</head>
<body>
    
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="order.php?status=All" class="<?php echo ($filter == 'All') ? 'active' : ''; ?>">All</a>
        <a href="order.php?status=Pending" class="<?php echo ($filter == 'Pending') ? 'active' : ''; ?>">Pending</a>
        <a href="order.php?status=Approved" class="<?php echo ($filter == 'Approved') ? 'active' : ''; ?>">Approved</a>
        <a href="order.php?status=Shipped" class="<?php echo ($filter == 'Shipped') ? 'active' : ''; ?>">Shipped</a>
        <a href="order.php?status=Delivered" class="<?php echo ($filter == 'Delivered') ? 'active' : ''; ?>">Delivered</a>
        <a href="order.php?status=Cancelled" class="<?php echo ($filter == 'Cancelled') ? 'active' : ''; ?>">Cancelled</a>
    </div>
    <a href="dashboard.php" class="back-link">Back</a>

    <div class="container">
        <?php if (empty($orders)): ?>
            <p>No orders found for this category.</p>
        <?php else: ?>
            <div class="table-container">
                <table>
                    <tr>
                        <th>Order ID</th>
                        <th>User Name</th>
                        <th>Phone No</th>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Payment Type</th>
                        <th>Order Address</th>
                        <th>Prescription</th>
                        <th>Order Date</th>
                        <th>Status</th>
                        <th>Update Status</th>
                    </tr>
                    <?php 
                    $previous_order_id = null;
                    foreach ($orders as $order): 
                        $order_id = $order['order_id'];
                    ?>
                        <tr>
                            <?php if ($order_id !== $previous_order_id): ?>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <?php echo htmlspecialchars($order_id); ?>
                                </td>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <?php echo htmlspecialchars($order['user_name']); ?>
                                </td>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <?php echo htmlspecialchars($order['user_phone']); ?>
                                </td>
                            <?php endif; ?>
                            
                            <!-- Medicine details per row -->
                            <td><?php echo htmlspecialchars($order['medicine_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['total_price']); ?></td>
                            
                            <?php if ($order_id !== $previous_order_id): ?>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <?php echo htmlspecialchars($order['payment_type']); ?>
                                </td>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <?php echo htmlspecialchars($order['order_address']); ?>
                                </td>
                            <?php endif; ?>
                            
                            <!-- Prescription: left per medicine if needed -->
                            <td>
                                <?php if ($order['prescription_upload']): ?>
                                    <a href="<?php echo htmlspecialchars($order['prescription_upload']); ?>" class="prescription-link" target="_blank">View</a>
                                <?php else: ?>
                                    Not needed
                                <?php endif; ?>
                            </td>
                            
                            <?php if ($order_id !== $previous_order_id): ?>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <?php echo htmlspecialchars($order['order_date']); ?>
                                </td>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>" class="status-<?php echo strtolower($order['order_status']); ?>">
                                    <?php echo htmlspecialchars($order['order_status']); ?>
                                </td>
                                <td rowspan="<?php echo $order_count[$order_id]; ?>">
                                    <form method="POST" action="process_order.php">
                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                        <select name="order_status">
                                            <?php
                                            // Define the allowed statuses based on current order status
                                            $allowedStatuses = [];
                                            switch ($order['order_status']) {
                                                case 'Pending':
                                                    $allowedStatuses = ['Pending', 'Approved', 'Shipped', 'Delivered', 'Cancelled'];
                                                    break;
                                                case 'Approved':
                                                    // Do not allow reverting back to 'Pending'
                                                    $allowedStatuses = ['Approved', 'Shipped', 'Delivered', 'Cancelled'];
                                                    break;
                                                case 'Shipped':
                                                    // Do not allow 'Pending' or 'Approved'
                                                    $allowedStatuses = ['Shipped', 'Delivered', 'Cancelled'];
                                                    break;
                                                case 'Delivered':
                                                    // Do not allow 'Pending', 'Approved', or 'Shipped'
                                                    $allowedStatuses = ['Delivered', 'Cancelled'];
                                                    break;
                                                default:
                                                    // If the status is something else (e.g., 'Cancelled'), you can define a default behavior.
                                                    $allowedStatuses = ['Pending', 'Approved', 'Shipped', 'Delivered', 'Cancelled'];
                                                    break;
                                            }
                                            
                                            // Generate the dropdown options
                                            foreach ($allowedStatuses as $status) {
                                                $selected = ($order['order_status'] === $status) ? 'selected' : '';
                                                echo "<option value=\"$status\" $selected>$status</option>";
                                            }
                                            ?>
                                        </select>
                                        <input type="submit" value="Update">
                                    </form>

                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php 
                        $previous_order_id = $order_id;
                    endforeach; 
                    ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
