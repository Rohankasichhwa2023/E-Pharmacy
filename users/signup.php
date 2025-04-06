<?php

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $checkEmail = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $checkEmail);

    if (mysqli_num_rows($result) > 0) {
        echo "<p class='error'>Email is already registered!</p>";
    } else {
        $sql = "INSERT INTO users (name, email, phone, password) VALUES ('$name', '$email', '$phone', '$password')";
        if (mysqli_query($conn, $sql)) {
            header('Location: login.php');
        } else {
            echo "<p class='error'>Error: " . mysqli_error($conn) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>User Sign-Up</title>
    <link rel="stylesheet" href="styles.css"> <!-- Linking external CSS -->
</head>
<style>
    body {
        background: #f9f9f9;
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        height: 100vh;
        align-items: center;
        justify-content: center;
    }


    .signup-container {
        background: #fff;
        padding: 20px 40px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        width: 60%;
        max-width: 300px;
        height:500px;
        text-align: center;
    }

    .signup-container img {
        max-width: 100px; /* Adjust logo size */
        display: block;
        margin: 0 auto 15px;
    }

    h1 {
        color: #333;
        margin-bottom: 20px;
    }

    form {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    label {
        color: #555;
        margin-bottom: 5px;
        font-size: 0.9rem;
        align-self: flex-start;
    }

    input[type="text"],
    input[type="email"],
    input[type="number"],
    input[type="password"] {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    button {
        background: #1977F3;
        color: #fff;
        padding: 10px;
        border: none;
        border-radius: 4px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.3s ease;
        width: 100%;
    }

    button:hover {
        background: #145abf;
    }

    /* Style for the Login link */
    .login-link {
        margin-top: 15px;
        font-size: 0.9rem;
        color: #555;
    }

    .login-link a {
        color: #1977F3;
        text-decoration: none;
        font-weight: bold;
    }

    .login-link a:hover {
        text-decoration: underline;
    }


    @media (max-width: 480px) {
        .signup-container {
            padding: 20px;
        }
    }
</style>
<body>
    <div class="signup-container">
        <img src="../buttons/logo.jpg" alt="Pharma Logo">
        <h1>Sign Up</h1>
        <form method="POST" action="signup.php">
            <label for="name">Name:</label>
            <input type="text" name="name" required>

            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="phone">Phone:</label>
            <input type="number" name="phone" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p class="login-link">Already registered? <a href="login.php">Log In</a></p>
    </div>
</body>
</html>
