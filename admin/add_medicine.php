<?php 
// Include database connection
include '../db.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize form data
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $manufacturer = mysqli_real_escape_string($conn, $_POST['manufacturer']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $quantity = mysqli_real_escape_string($conn, $_POST['quantity']);
    $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
    $age_group = mysqli_real_escape_string($conn, $_POST['age_group']);
    $side_effects = mysqli_real_escape_string($conn, $_POST['side_effects']);
    $usages = mysqli_real_escape_string($conn, $_POST['usages']);
    $precautions = mysqli_real_escape_string($conn, $_POST['precautions']);
    $storage_info = mysqli_real_escape_string($conn, $_POST['storage_info']);
    $prescription_required = isset($_POST['prescription_required']) ? (int)$_POST['prescription_required'] : 0;
    $image = null;

    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Directory to store uploaded images
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imagePath;
        } else {
            echo "Error uploading the image.";
        }
    }

    // Insert medicine into the database
    $sql = "INSERT INTO medicines 
            (name, category, manufacturer, price, quantity, dosage, age_group, side_effects, usages, precautions, storage_info, prescription_required, image) 
            VALUES 
            ('$name', '$category', '$manufacturer', '$price', '$quantity', '$dosage', '$age_group', '$side_effects', '$usages', '$precautions', '$storage_info', '$prescription_required', '$image')";

    if (mysqli_query($conn, $sql)) {
        echo "Medicine added successfully!";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Add Medicine</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
      padding: 20px;
      line-height: 1.6;
    }
    h1 {
      text-align: center;
      color: #1977F3;
      margin-bottom: 20px;
    }
    form {
      max-width: 400px;  /* Reduced max width for a more compact form */
      margin: 0 auto;
      background: #fff;
      padding: 20px;
      border-radius: 6px;
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }
    label {
      font-weight: bold;
      display: block;
      margin-bottom: 5px;
    }
    input, textarea, select {
      width: 100%;
      padding: 8px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    button {
      width: 100%;
      padding: 10px;
      background-color: #007BFF;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 1rem;
    }
    button:hover {
      background-color: #0056b3;
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
  </style>
</head>
<body>
  <a href="dashboard.php" class="back-link">Back</a>
  <h1>Add Medicine</h1>
  <form action="add_medicine.php" method="POST" enctype="multipart/form-data">
    <label for="name">Medicine Name:</label>
    <input type="text" id="name" name="name" required>

    <label for="category">Category:</label>
    <input type="text" id="category" name="category">

    <label for="manufacturer">Manufacturer:</label>
    <input type="text" id="manufacturer" name="manufacturer">

    <label for="price">Price (in Rs):</label>
    <input type="number" step="0.01" id="price" name="price" required>

    <label for="quantity">Quantity:</label>
    <input type="number" id="quantity" name="quantity" required>

    <label for="dosage">Dosage:</label>
    <input type="text" id="dosage" name="dosage">

    <label for="age_group">Age Group:</label>
    <select id="age_group" name="age_group">
      <option value="">Select Age Group</option>
      <option value="0-10">0-10</option>
      <option value="10-20">10-20</option>
      <option value="20-40">20-40</option>
      <option value="40+">40+</option>
      <option value="all">All</option>
    </select>

    <label for="side_effects">Side Effects:</label>
    <textarea id="side_effects" name="side_effects" rows="3"></textarea>

    <label for="usages">Usages:</label>
    <textarea id="usages" name="usages" rows="3"></textarea>

    <label for="precautions">Precautions:</label>
    <textarea id="precautions" name="precautions" rows="3"></textarea>

    <label for="storage_info">Storage Information:</label>
    <input type="text" id="storage_info" name="storage_info">

    <label for="prescription_required">Prescription Required:</label>
    <select id="prescription_required" name="prescription_required" required>
      <option value="">Select</option>
      <option value="1">Yes</option>
      <option value="0">No</option>
    </select>

    <label for="image">Medicine Image (optional):</label>
    <input type="file" id="image" name="image" accept="image/*">

    <button type="submit">Add Medicine</button>
  </form>
</body>
</html>
