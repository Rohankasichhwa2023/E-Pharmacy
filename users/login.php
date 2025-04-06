<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_id'] = $user['id'];
        header('Location: home.php');
        exit();
    } else {
        echo "<p class='error'>Invalid email or password!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
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

.login-container {
    background: #fff;
    padding: 20px 40px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    width: 60%;
    max-width: 300px;
    text-align: center;
}

.login-container img {
    max-width: 100px; /* Adjust the size as needed */
    height: auto;
    display: block;
    margin: 0 auto 15px; /* Centers the image and adds spacing below */
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

input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 10px;
    margin-bottom: 20px;
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

/* Style for the Sign Up link */
.signup-link {
    margin-top: 25px;
    font-size: 0.9rem;
    color: #555;
}

.signup-link a {
    color: #1977F3;
    text-decoration: none;
    font-weight: bold;
}

.signup-link a:hover {
    text-decoration: underline;
}

@media (max-width: 480px) {
    .login-container {
        padding: 20px;
    }
}

    </style>
</head>
<body>
    <div class="login-container">
        <img src="../buttons/logo.jpg" alt="Pharma Logo">
        <h1>Login</h1>
        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Log In</button>
        </form>

        <p class="signup-link">Haven't signed up yet? <a href="signup.php">Sign Up</a></p>
    </div>

</body>
</html>
