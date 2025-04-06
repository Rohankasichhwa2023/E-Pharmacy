<?php
// Include database connection
include '../db.php';

// Get the medicine ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch medicine details
    $sql = "SELECT * FROM medicines WHERE id = $id";
    $result = mysqli_query($conn, $sql);
    $medicine = mysqli_fetch_assoc($result);
}

// Handle update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $manufacturer = $_POST['manufacturer'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $dosage = $_POST['dosage'];
    $age_group = $_POST['age_group'];
    $side_effects = $_POST['side_effects'];
    $usages = $_POST['usages'];
    $precautions = $_POST['precautions'];
    $storage_info = $_POST['storage_info'];
    $prescription_required = isset($_POST['prescription_required']) ? 1 : 0;
    $image = $medicine['image'];

    // Handle image upload if provided
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
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

    // Update medicine details in the database
    $sql = "UPDATE medicines SET 
                name='$name', 
                category='$category', 
                manufacturer='$manufacturer', 
                price='$price', 
                quantity='$quantity', 
                dosage='$dosage', 
                age_group='$age_group', 
                side_effects='$side_effects', 
                usages='$usages', 
                precautions='$precautions', 
                storage_info='$storage_info', 
                prescription_required='$prescription_required', 
                image='$image' 
            WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        echo "Medicine updated successfully!";
        header('Location: view_medicines.php');
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Medicine</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            line-height: 1.6;
        }
        form {
            max-width: 600px;
            margin: auto;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }
        input, textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        h1{
            color: #007bff;
            text-align:center;
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
    <h1>Update Medicine</h1>
    <form method="POST" action="update_medicine.php?id=<?php echo $id; ?>" enctype="multipart/form-data">
        <label for="name">Medicine Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($medicine['name']); ?>" required>

        <label for="category">Category:</label>
        <input type="text" id="category" name="category" value="<?php echo htmlspecialchars($medicine['category']); ?>">

        <label for="manufacturer">Manufacturer:</label>
        <input type="text" id="manufacturer" name="manufacturer" value="<?php echo htmlspecialchars($medicine['manufacturer']); ?>">

        <label for="price">Price (in USD):</label>
        <input type="number" step="0.01" id="price" name="price" value="<?php echo $medicine['price']; ?>" required>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo $medicine['quantity']; ?>" required>

        <label for="dosage">Dosage:</label>
        <input type="text" id="dosage" name="dosage" value="<?php echo htmlspecialchars($medicine['dosage']); ?>">

        <label for="age_group">Age Group:</label>
        <input type="text" id="age_group" name="age_group" value="<?php echo htmlspecialchars($medicine['age_group']); ?>">

        <label for="side_effects">Side Effects:</label>
        <textarea id="side_effects" name="side_effects" rows="4"><?php echo htmlspecialchars($medicine['side_effects']); ?></textarea>

        <label for="usages">Usages:</label>
        <textarea id="usages" name="usages" rows="4"><?php echo htmlspecialchars($medicine['usages']); ?></textarea>

        <label for="precautions">Precautions:</label>
        <textarea id="precautions" name="precautions" rows="4"><?php echo htmlspecialchars($medicine['precautions']); ?></textarea>

        <label for="storage_info">Storage Info:</label>
        <input type="text" id="storage_info" name="storage_info" value="<?php echo htmlspecialchars($medicine['storage_info']); ?>">

        <label for="prescription_required">Prescription Required:</label>
        <input type="checkbox" id="prescription_required" name="prescription_required" <?php echo $medicine['prescription_required'] ? 'checked' : ''; ?>>

        <label for="image">Medicine Image:</label>
        <input type="file" id="image" name="image" accept="image/*">
        <br>
        Current Image: 
        <?php if ($medicine['image']): ?>
            <img src="<?php echo $medicine['image']; ?>" alt="Image" width="50" height="50">
        <?php else: ?>
            No Image
        <?php endif; ?>

        <button type="submit">Update Medicine</button>
    </form>
</body>
</html>
