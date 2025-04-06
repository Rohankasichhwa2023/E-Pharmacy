<?php 
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

include '../db.php';

$user_id = intval($_SESSION['user_id']); // Ensure user_id is an integer

$sql = "SELECT name, email, phone, profile_pic FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    die("User not found.");
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
         /* Pofile Button */
        .profile button {
            background: none;
            border: none;
            cursor: pointer;
        }
        .profile button img {
            width: 60px;
            height:60px;
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



        /* Profile Container */
        .profile-container {
            width: 50%;
            max-width: 500px;
            background-color: white;
            padding: 20px;
            margin: 50px auto;
            border-radius: 10px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .profile-container img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #1977F3;
            margin-bottom: 10px;
        }

        .profile-container h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-container p {
            font-size: 18px;
            color: #555;
            margin: 5px 0;
        }

        .profile-container a button {
            background-color: #1977F3;
            color: white;
            border: none;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            transition: 0.3s ease-in-out;
            width: 100%;
            max-width:200px;
        }
        .profile-container a {
            display: block; /* Forces each button link to appear on a new line */
            margin-bottom: 10px; /* Adds spacing between buttons */
        }

        .profile-container a button:hover {
            background-color: #1558b1;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-container {
                width: 80%;
            }

            .back-button {
                margin-left: 10px;
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
    <h1>Your Profile</h1>
    <div class="profile-container">
        <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture">
        <h2><?php echo htmlspecialchars($row['name']); ?></h2>
        <p>Email: <?php echo htmlspecialchars($row['email']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($row['phone']); ?></p>
        <a href="update_profile.php"><button>Update Profile</button></a>
        <a href="order_page.php"><button>Orders</button></a>
        <a href="logout.php"><button>Logout</button></a>
    </div>
</body>

</html>

<?php
// Close connection
mysqli_close($conn);
?>
