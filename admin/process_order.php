<?php
session_start();
include '../db.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin_login.php');
    exit();
}

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id'], $_POST['order_status'])) {
    $order_id = mysqli_real_escape_string($conn, $_POST['order_id']);
    $new_status = mysqli_real_escape_string($conn, $_POST['order_status']);

    // Get current order details
    $query = "SELECT medicine_id, quantity, order_status FROM orders WHERE order_id = '$order_id'";
    $result = mysqli_query($conn, $query);
    $order = mysqli_fetch_assoc($result);

    if ($order) {
        $medicine_id = $order['medicine_id'];
        $order_quantity = $order['quantity'];
        $current_status = $order['order_status'];

        // If the new status is Approved, Shipped, or Delivered and the stock hasn't been deducted before
        if (in_array($new_status, ['Approved', 'Shipped', 'Delivered']) && !in_array($current_status, ['Approved', 'Shipped', 'Delivered'])) {
            // Reduce stock from medicines table
            $update_stock = "UPDATE medicines SET quantity = GREATEST(quantity - $order_quantity, 0) WHERE id = '$medicine_id'";
            mysqli_query($conn, $update_stock);
        }

        // Update order status
        $update_order = "UPDATE orders SET order_status = '$new_status' WHERE order_id = '$order_id'";
        if (mysqli_query($conn, $update_order)) {
            $_SESSION['success_message'] = "Order status updated successfully!";
        } else {
            $_SESSION['error_message'] = "Error updating order: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Order not found.";
    }
    
    header("Location: order.php"); 
    exit();
} else {
    $_SESSION['error_message'] = "Invalid request.";
    header("Location: order.php");
    exit();
}
?>
