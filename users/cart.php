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
    $_SESSION['cart'][$user_id] = [];
}

// Handle removing items from the cart
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$user_id][$id]);
}

// Handle updating quantity
if (isset($_POST['update'])) {
    foreach ($_POST['quantity'] as $id => $quantity) {
        if (isset($_SESSION['cart'][$user_id][$id])) {
            $_SESSION['cart'][$user_id][$id]['quantity'] = max(1, intval($quantity));
        }
    }
}

// Fetch cart items for this user
$cartItems = $_SESSION['cart'][$user_id] ?? [];

$medicines = [];
if (!empty($cartItems)) {
    $ids = implode(',', array_keys($cartItems));
    $sql = "SELECT * FROM medicines WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    $medicines = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

$cart_count =0;

foreach ($_SESSION['cart'][$user_id] as $medicine_id => $cart_item) {
    // If the item still exists in the database, increment the count
    $sql_check = "SELECT id FROM medicines WHERE id = $medicine_id";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        // Item still exists in the database, so include it in the count
        $cart_count++;
    } else {
        // Item is deleted, so remove it from the session cart
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
    <title>Cart - Pharma Website</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        
        h1 {
            text-align: center;
            color: #1977F3;
            margin-bottom: 20px;
        }
        .cart-container {
            width: 80%;
            margin: 20px auto;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 10px;
        }

        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }

        .item-details {
            flex: 1;
            padding-left: 15px;
        }

        .item-name {
            font-weight: bold;
            color: #1977F3;
        }

        .price {
            font-size: 1.2rem;
            color: #1977F3;
            font-weight: bold;
        }

        .quantity-container {
            display: flex;
            align-items: center;
        }

        .quantity-container input {
            width: 50px;
            padding: 5px;
            text-align: center;
            font-size: 1rem;
        }

        .remove-button {
            background-color: red;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 1rem;6
        }

        .remove-button:hover {
            background-color: darkred;
        }

        .cart-summary {
            margin-top: 30px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-item {
            display: flex;
            flex-direction: column;
            margin-top: 10px;
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .medicine-total-price {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 1rem;
            font-weight: normal;
            color: #333;
            border-bottom: 1px solid #ddd;
        }

        .medicine-name {
            font-weight: bold;
            color: #1977F3;
        }

        .medicine-price {
            font-weight: normal;
            color: #1977F3;
        }

        .total-price-summary {
            display: flex;
            justify-content: space-between;
            padding-top: 12px;
            font-size: 1.2rem;
            font-weight: bold;
            color: #1977F3;
            margin-top: 12px;
        }

        .total-label {
            font-size: 1.4rem;
        }

        .total-amount {
            font-size: 1.4rem;
        }

        .checkout-button {
            background-color: #1977F3;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
            font-size: 1.2rem;
            display: block;
            margin: 20px auto; /* Centers the button */
            text-align: center;
            width: fit-content; /* Makes button width fit the content */
        }


        .checkout-button:hover {
            background-color: #135abe;
        }
        /* navbar */
        header {
            background-color: white;
            color: white;
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

        /* Logo */
        .logo img {
            width:80px;
            height:70px;
        }

        /* Search Bar */
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
            height: 100%; /* Make the button fill the entire height */
            width: 40px; /* Adjust width if needed */
        }

        .search-icon {
            width: 20px;
            height: 20px;
        }

      

        /* Cart */
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
            background:#1977F3 ;
            color: white;
            font-size: 0.8rem;
            padding: 3px 6px;
            border-radius: 50%;
            position: absolute;
            top: -5px;
            right: -10px;
        }

        /* Hamburger Menu for Mobile */
        .hamburger {
            font-size: 2rem;
            color: white;
            cursor: pointer;
            display: none;
        }

        /* Responsive Navbar */
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
         /* Pofile Button */
        .profile button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .profile button img {
            width: 60px;
            height: 60px;
            border-radius: 50%; /* Optional: Makes it circular */
            object-fit: cover; /* Ensures the image covers the area without distortion */
            object-position: center; /* Keeps the subject centered */
        }

         /* Order Button */
        .order button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .order button img {
            width: 60px;
            height:60px;
        }

        /* Cart Button */
        .cart button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .rightbar{
            display:flex;
            gap:25px;
        }
        /* navbar end */
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
                        </button> <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </nav>
    </header>
    <h1>Your Cart</h1>
    <div class="cart-container">
        <?php if (!empty($medicines)): ?>
            <form action="cart.php" method="POST">
                <?php foreach ($medicines as $medicine): ?>
                    <div class="cart-item">
                        <div class="item-image">
                            <img src="<?php echo $medicine['image'] ?: 'placeholder.png'; ?>" alt="<?php echo htmlspecialchars($medicine['name']); ?>">
                        </div>
                        <div class="item-details">
                            <div class="item-name"> <?php echo htmlspecialchars($medicine['name']); ?> </div>
                            <div class="price"> Rs <?php echo htmlspecialchars($medicine['price']); ?> </div>
                        </div>
                        <div class="quantity-container">
                            <input type="number" name="quantity[<?php echo $medicine['id']; ?>]" value="<?php echo $cartItems[$medicine['id']]['quantity']; ?>" min="1">
                            <a href="cart.php?remove=<?php echo $medicine['id']; ?>" class="remove-button">Remove</a>
                        </div>
                    </div>
                    <!-- Display total price for each medicine -->

                <?php endforeach; ?>
                <button type="submit" name="update" class="checkout-button">Update Cart</button>
            </form>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h2>Cart Summary</h2>
                <?php
                    $total = 0;
                    foreach ($medicines as $medicine) {
                        $quantity = $cartItems[$medicine['id']]['quantity'];
                        $total += $medicine['price'] * $quantity;
                    }
                ?>
                <div class="summary-item">
                    <?php foreach ($medicines as $medicine): ?>
                        <div class="medicine-total-price">
                            <span class="medicine-name"><?php echo htmlspecialchars($medicine['name']); ?>:</span>
                            <span class="medicine-price">Rs <?php echo $medicine['price'] * $cartItems[$medicine['id']]['quantity']; ?></span>
                        </div>
                    <?php endforeach; ?>
                    <div class="total-price-summary">
                        <span class="total-label">Total Price</span>
                        <span class="total-amount">Rs <?php echo $total; ?></span>
                    </div>
                </div>
                <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
            </div>
        <?php else: ?>
            <p style="text-align:center;margin-top:60px">Your cart is empty. <a href="home.php">Browse Products</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
