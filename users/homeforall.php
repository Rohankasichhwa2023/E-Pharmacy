<?php
session_start();

// Include database connection
include '../db.php';

// Check if user is logged in
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Initialize cart for guests
if (!isset($_SESSION['cart'][$user_id])) {
    $_SESSION['cart'][$user_id] = [];
}

// Fetch all medicines
$sql = "SELECT * FROM medicines";
$result = mysqli_query($conn, $sql);

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    $medicine_id = $_POST['medicine_id'];
    $quantity = 1;

    if (isset($_SESSION['cart'][$user_id][$medicine_id])) {
        $_SESSION['cart'][$user_id][$medicine_id]['quantity'] += $quantity;
    } else {
        $_SESSION['cart'][$user_id][$medicine_id] = [
            'id' => $medicine_id,
            'quantity' => $quantity
        ];
    }

    header('Location: homeforall.php');
    exit();
}

// Cart count
$cart_count = count($_SESSION['cart'][$user_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Pharma Website</title>
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        /* Header */
        header {
            background-color: white;
            text-align: center;
        }

        /* Navbar */
        .navbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: white;
            padding: 10px 10px 5px 50px;
            width: 92%;
        }

        /* Logo */
        .logo img {
            height: 40px;
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

        /* Rightbar (Login & Signup Buttons) */
        .rightbar {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .rightbar button {
            padding: 15px 25px;
            border: none;
            background-color: #1977F3;
            color: white;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .rightbar button:hover {
            background-color: #135abe;
            transform: scale(1.05);
        }

        .rightbar a {
            text-decoration: none;
        }

        /* Medicine Container */
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
            text-decoration: none; /* Ensure all links inside medicine-name have no underline */
            color: inherit; 
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
                <a href="homeforall.php">
                    <img style="width:80px;height:70px" src="../buttons/logo.jpg" alt="Pharma Logo">
                </a>
            </div>

            <div class="search-bar">
                <input type="text" placeholder="Search for medicines..." id="searchInput">
                <button type="submit" id="searchButton">
                    <img src="../buttons/search-button.png" alt="Search" class="search-icon">
                </button>
            </div>

            <div class="rightbar">
                <div class="login">
                    <a href="login.php">
                        <button>Login</button>
                    </a>
                </div>  
                <div class="signup">
                    <a href="signup.php">
                        <button>Signup</button>
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
                        <a href="medicine_detail.php?id=<?php echo $row['id']; ?>">
                            <div class="image-container">
                                <img src="<?php echo $row['image'] ?: 'placeholder.png'; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <?php if ($row['prescription_required']): ?>
                                    <div class="prescription-label">Prescription Required</div>
                                <?php endif; ?>
                            </div>
                            <div class="medicine-name"><?php echo htmlspecialchars($row['name']); ?></div>
                        </a>
                        <div class="price-cart-container">
                            <div class="price">Rs <?php echo htmlspecialchars($row['price']); ?></div>
                            <?php if ($row['quantity'] > 0): ?>
                                
                                <input type="hidden" name="medicine_id" value="<?php echo $row['id']; ?>">
                                <button class="add-to-cart" type="submit" name="add_to_cart" onclick="alert('Please login or sign up to add items to your cart.')">Add to Cart</button>
                                
                            <?php else: ?>
                                <button class="add-to-cart" style="background-color: gray; cursor: not-allowed;" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No medicines available.</p>
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
