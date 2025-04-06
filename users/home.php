<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Include database connection
include '../db.php';

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Initialize user's cart in session if not set
if (!isset($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = [];
}

// Fetch all medicines
$sql = "SELECT * FROM medicines";
$result = mysqli_query($conn, $sql);

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $medicine_id = $_POST['medicine_id'];
    $quantity = 1; // Default quantity is 1

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
    
    // Redirect to stay on the same page after adding to cart
    header('Location: home.php');
    exit();
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
    <title>Pharma Website</title>
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

        /* Container for Medicines */
        .container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            padding: 20px;
        }

        /* Ensure uniform height for medicine posts */
        .medicine-post {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: calc(33.333% - 40px);
            max-width: 300px;
            margin-bottom: 20px;
            text-align: center;
            transition: transform 0.3s ease;
            padding-bottom: 10px;
            height: 280px; 
        }

        .medicine-post:hover {
            transform: scale(1.05);
        }

        /* Fix Image Height */
        .medicine-post .image-container {
            width: 100%;
            height: 200px; /* Fixed height for images */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden; /* Ensures no extra space */
        }

        .medicine-post .image-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Keeps aspect ratio and prevents distortion */
        }

        /* Keep Name and Buttons Aligned */
        .medicine-name {
            flex-grow: 1;
            padding: 10px;
            font-size: 1.1rem;
            font-weight: bold;
            color: #1977F3;
        }

        .price-cart-container {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            align-items: center;
        }

        /* Responsive Fix */
        @media (max-width: 768px) {
            .medicine-post {
                width: calc(50% - 40px);
            }
        }

        @media (max-width: 480px) {
            .medicine-post {
                width: 100%;
            }
        }

        .price {
            font-size: 1rem;
            font-weight: bold;
            color: #1977F3;
        }

        .add-to-cart {
            background-color: #1977F3;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .image-container {
            position: relative;
        }

        .prescription-label {
            position: absolute;
            bottom: 0;
            right: 0;
            background-color: green;
            color: white;
            font-size: 0.8rem;
            font-weight: bold;
            padding: 5px 10px;
            border-top-left-radius: 5px;
        }

        .add-to-cart:hover {
            background-color: #135abe;
        }

        @media (max-width: 768px) {
            .medicine-post {
                width: calc(50% - 40px);
            }
        }

        @media (max-width: 480px) {
            .medicine-post {
                width: 100%;
            }
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
                        </button> <span class="cart-count"><?php echo $cart_count; ?></span>
                    </a>
                </div>
            </div>
        </nav>
    </header>


    <main>
        <div class="container">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <div class="medicine-post">
                        <a href="medicine_detail.php?id=<?php echo $row['id']; ?>" style="text-decoration: none; color: inherit;">

                            <div class="image-container">
                                <?php if (!empty($row['image'])): ?>
                                    <img src="<?php echo $row['image']; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <?php else: ?>
                                    <img src="placeholder.png" alt="No Image Available">
                                <?php endif; ?>

                                <!-- Prescription Label -->
                                <?php if ($row['prescription_required']): ?>
                                    <div class="prescription-label">Prescription Required</div>
                                <?php endif; ?>
                            </div>

                            <div class="medicine-name">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </div>

                            <div class="price-cart-container">
                                <div class="price">Rs <?php echo htmlspecialchars($row['price']); ?></div>
                                <?php if ($row['quantity'] > 0): ?>
                                    <form action="home.php" method="POST">
                                        <input type="hidden" name="medicine_id" value="<?php echo $row['id']; ?>">
                                        <button class="add-to-cart" type="submit" name="add_to_cart">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <button class="add-to-cart" style="background-color: gray; cursor: not-allowed;" disabled>Out of Stock</button>
                                <?php endif; ?>
                            </div>

                        </a>
                    </div>


                <?php endwhile; ?>
            <?php else: ?>
                <p>No medicines are available.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
<script>
    document.getElementById('searchInput').addEventListener('keydown', function(event) {
        if (event.key === 'Enter') {
            searchMedicines();
        }
    });

    document.getElementById('searchButton').addEventListener('click', function() {
        searchMedicines();
    });

    function searchMedicines() {
        const query = document.getElementById('searchInput').value;
        const mainContainer = document.querySelector('.container');

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `search.php?query=${encodeURIComponent(query)}`, true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                mainContainer.innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>

</html>
