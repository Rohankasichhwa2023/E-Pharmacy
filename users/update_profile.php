<?php
// Start session
session_start();

include '../db.php';

// Assuming user is logged in and their ID is stored in session
$user_id = $_SESSION['user_id'];

// Fetch user details
$sql = "SELECT name, email, phone, profile_pic, password FROM users WHERE id = $user_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
} else {
    die("User not found.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $profile_pic = $row['profile_pic'];

    // Handle profile picture upload
    if (!empty($_FILES['profile_pic']['name'])) {
        $target_dir = "../profile_uploads/";
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
        move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file);
        $profile_pic = basename($_FILES["profile_pic"]["name"]);
    }

    // Validate password change
    if (!empty($new_password)) {
        if ($old_password === $row['password']) { // Direct comparison
            $password_update = ", password='$new_password'"; // Store plain text password
        } else {
            die("Incorrect old password.");
        }
    } else {
        $password_update = "";
    }


    // Update user details
    $update_sql = "UPDATE users SET name='$name', email='$email', phone='$phone', profile_pic='$profile_pic' $password_update WHERE id=$user_id";
    if (mysqli_query($conn, $update_sql)) {
        echo "Profile updated successfully.";
        header('Location: profile.php');
        exit();
    } else {
        echo "Error updating profile: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile</title>
</head>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column; /* Align elements vertically */
        justify-content: center;
        align-items: center;
        height: 100vh;
    }

    .back-link {
        display: block;
        width: 100px; /* Match form width */
        text-align: center;
        padding: 10px;
        background-color: #1977F3;
        color: white;
        font-size: 16px;
        font-weight: bold;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
        margin-bottom: 15px;
    }

    .back-link:hover {
        background-color: #1558b1;
    }

    form {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 310px;
        box-sizing: border-box;
    }

    form label {
        display: block;
        font-weight: bold;
        margin-bottom: 5px;
        color: #333;
    }

    form input[type="text"],
    form input[type="email"],
    form input[type="password"],
    form input[type="file"] {
        width: 100%; /* Full width inside form */
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box; /* Fix width calculation issue */
    }

    form button[type="submit"] {
        width: 100%;
        padding: 10px;
        border: none;
        border-radius: 5px;
        background-color: #1977F3;
        color: white;
        font-size: 16px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    form button[type="submit"]:hover {
        background-color: #1558b1;
    }
  
</style>
<body>
    <a href="javascript:window.history.back();" class="back-link">Back</a>
    <form action="" method="post" enctype="multipart/form-data">
        <label>Name:</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($row['name']); ?>" required>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>
        
        <label>Phone:</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($row['phone']); ?>" required>
        
        <label>Profile Picture:</label>
        <input type="file" name="profile_pic">
        
        <label>Old Password:</label>
        <input type="password" name="old_password">
        
        <label>New Password:</label>
        <input type="password" name="new_password">
        
        <button type="submit">Update Profile</button>
    </form>
</body>

</html>

<?php
// Close connection
mysqli_close($conn);
?>
