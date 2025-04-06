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

// Ensure cart exists for this user
if (!isset($_SESSION['cart'][$user_id])) {
    header('Location: cart.php');
    exit();
}

// Check if prescription is required
$requires_prescription = false;
foreach ($_SESSION['cart'][$user_id] as $medicine_id => $item) {
    $sql = "SELECT prescription_required FROM medicines WHERE id = $medicine_id";
    $result = mysqli_query($conn, $sql);
    $medicine = mysqli_fetch_assoc($result);
    if ($medicine['prescription_required']) {
        $requires_prescription = true;
        break;
    }
}

// Handle the form submission
// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delivery_address = mysqli_real_escape_string($conn, $_POST['delivery_address']);
    $payment_type = mysqli_real_escape_string($conn, $_POST['payment_type']); // 'cash' or 'online'
    
    // Handle prescription file upload if required
    $prescription_path = NULL;
    if ($requires_prescription && !empty($_FILES['prescription']['name'])) {
        $target_dir = "../prescription_uploads/";
        $prescription_path = $target_dir . basename($_FILES['prescription']['name']);
        move_uploaded_file($_FILES['prescription']['tmp_name'], $prescription_path);
    }

    // Generate a unique order_id (timestamp + user_id)
    $order_id = time() . $user_id;

    // Insert each item into the orders table
    foreach ($_SESSION['cart'][$user_id] as $medicine_id => $item) {
        $quantity = intval($item['quantity']);
        
        // Fetch medicine price
        $sql = "SELECT price FROM medicines WHERE id = $medicine_id";
        $result = mysqli_query($conn, $sql);
        $medicine = mysqli_fetch_assoc($result);
        $individual_total_price = $medicine['price'] * $quantity; // Calculate total price for this medicine

        // Store correct total price for each medicine
        $sql = "INSERT INTO orders (order_id, user_id, medicine_id, quantity, total_price, payment_type, order_address, prescription_status, prescription_upload) 
                VALUES ('$order_id', '$user_id', '$medicine_id', '$quantity', '$individual_total_price', '$payment_type', '$delivery_address', '$requires_prescription', '$prescription_path')";

        if (!mysqli_query($conn, $sql)) {
            die("Error inserting order: " . mysqli_error($conn));
        }
    }

    // Clear the cart after placing the order
    unset($_SESSION['cart'][$user_id]);

    // Redirect to the order confirmation page
    header('Location: order_confirmation.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Pharma Website</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }

        .container {
            width: 60%;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #1977F3;
        }

        label {
            font-size: 1.1rem;
            margin-top: 10px;
            display: block;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }

        input[type="submit"] {
            background-color: #1977F3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #135abe;
        }

        .payment-options {
            display: flex;
            justify-content: space-between;
        }

        .payment-options label {
            width: 48%;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Checkout</h2>
        <form action="checkout.php" method="POST" enctype="multipart/form-data">
            <label for="delivery_address">Delivery Address</label>
            <input type="text" id="delivery_address" name="delivery_address" required>

            <label>Payment Type</label>
            <div class="payment-options">
                <label>
                    <input type="radio" name="payment_type" value="cash" required> Cash on Delivery
                </label>
                <label>
                    <input type="radio" name="payment_type" value="online" required> Online Payment (eSewa)
                </label>
            </div>

            <?php if ($requires_prescription): ?>
                <label for="prescription">Upload Prescription</label>
                <input type="file" id="prescription" name="prescription" accept="image/*, .pdf" required>
            <?php endif; ?>

            <input type="submit" value="Place Order">
        </form>
    </div>
</body>
</html>
