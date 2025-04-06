<?php 
// Include database connection
include '../db.php';
session_start();

// Fetch medicine details
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch the medicine details from the database
    $sql = "SELECT * FROM medicines WHERE id = $id";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $medicine = mysqli_fetch_assoc($result);
    } else {
        die("Medicine not found!");
    }
} else {
    die("Invalid medicine ID!");
}

// Check if user is logged in
$logged_in = isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;

if ($logged_in) {
    $user_id = $_SESSION['user_id'];

    // Initialize user's cart in session if not set
    if (!isset($_SESSION['cart'][$user_id])) {
        $_SESSION['cart'][$user_id] = [];
    }

    // Handle Add to Cart
    if (isset($_POST['add_to_cart']) && isset($_POST['medicine_id']) && is_numeric($_POST['medicine_id'])) {
        $medicine_id = $_POST['medicine_id'];
        $quantity = 1; // Default quantity is 1

        // Check if the medicine is in stock
        if ($medicine['quantity'] > 0) {
            // If the cart already contains the item, increase the quantity
            if (isset($_SESSION['cart'][$user_id][$medicine_id])) {
                $_SESSION['cart'][$user_id][$medicine_id]['quantity'] += $quantity;
            } else {
                // Otherwise, add the medicine to the cart
                $_SESSION['cart'][$user_id][$medicine_id] = [
                    'id' => $medicine_id,
                    'quantity' => $quantity
                ];
            }

            // Redirect back to avoid form resubmission
            header('Location: medicine_detail.php?id=' . $medicine_id);
            exit();
        } else {
            $out_of_stock_message = "This medicine is out of stock.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Details</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
        background-color: #f4f4f4;
        text-align: center;
    }

    .container {
        max-width: 800px;
        margin: 20px auto;
        padding: 20px;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: left;
    }

    .medicine-image {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 20px;
    }

    .medicine-image img {
        width: 100%;
        max-width: 400px;
        height: auto;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .medicine-details h2 {
        text-align: center;
        color: #333;
        margin-bottom: 10px;
    }

    .medicine-details {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .medicine-details p {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 16px;
        color: #555;
        border-bottom: 1px solid #ddd;
        padding: 8px 0;
    }

    .medicine-details strong {
        min-width: 450px;
        display: inline-block;
        font-weight: bold;
        color: #333;
    }

    .out-of-stock {
        color: red;
        font-weight: bold;
        text-align: center;
    }

    .add-to-cart {
        display: block;
        width: 100%;
        max-width: 200px;
        margin: 10px auto;
        padding: 10px;
        background-color: #1977F3;
        color: white;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .add-to-cart:hover {
        background-color: #135abe;
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
    }

    @media (max-width: 600px) {
        .medicine-details p {
            flex-direction: column;
            align-items: flex-start;
        }
        .medicine-details strong {
            margin-bottom: 4px;
        }
    }

    </style>
</head>
<body>
    <a href="javascript:window.history.back();" class="back-link">Back</a>

    <div class="container">
        <div class="medicine-image">
            <?php if (!empty($medicine['image'])): ?>
                <img src="<?php echo $medicine['image']; ?>" alt="<?php echo htmlspecialchars($medicine['name']); ?>">
            <?php else: ?>
                <img src="placeholder.png" alt="No Image Available">
            <?php endif; ?>
        </div>
        <div class="medicine-details">
            <h2><?php echo htmlspecialchars($medicine['name']); ?></h2>
            <p><strong>Price:</strong> Rs. <?php echo number_format($medicine['price'], 2); ?></p>      
            <p><strong>Category:</strong> <?php echo htmlspecialchars($medicine['category']); ?></p>
            <p><strong>Manufacturer:</strong> <?php echo htmlspecialchars($medicine['manufacturer']); ?></p>
            <p><strong>Dosage:</strong> <?php echo htmlspecialchars($medicine['dosage']); ?></p>
            <p><strong>Age Group:</strong> <?php echo htmlspecialchars($medicine['age_group']); ?></p>
            <p><strong>Side Effects:</strong> <?php echo htmlspecialchars($medicine['side_effects']); ?></p>
            <p><strong>Usage:</strong> <?php echo htmlspecialchars($medicine['usages']); ?></p>
            <p><strong>Precautions:</strong> <?php echo htmlspecialchars($medicine['precautions']); ?></p>
            <p><strong>Storage Info:</strong> <?php echo htmlspecialchars($medicine['storage_info']); ?></p>
            <p><strong>Prescription Required:</strong> <?php echo $medicine['prescription_required'] ? 'Yes' : 'No'; ?></p>
        </div>

        <?php if ($medicine['quantity'] > 0 && $logged_in): ?>
            <form action="medicine_detail.php?id=<?php echo $medicine['id']; ?>" method="POST">
                <input type="hidden" name="medicine_id" value="<?php echo $medicine['id']; ?>">
                <button class="add-to-cart" type="submit" name="add_to_cart">Add to Cart</button>
            </form>
        <?php elseif (!$logged_in): ?>
            <p><strong><a href="login.php">Log in</a> to add this item to your cart.</strong></p>
        <?php else: ?>
            <p class="out-of-stock">Out of Stock</p>
        <?php endif; ?>
    </div>
</body>
</html>
