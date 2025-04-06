<?php
// Include database connection
include '../db.php';


// Handle delete request
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Check if the medicine has any orders (regardless of status)
    $check_orders = "SELECT * FROM orders WHERE medicine_id = $delete_id";
    $result_orders = mysqli_query($conn, $check_orders);

    if (mysqli_num_rows($result_orders) > 0) {
        // Medicine is referenced in orders, so prevent deletion
        echo "<script>
                alert('Cannot delete this medicine because it has existing orders. You must delete the related orders first.');
                window.location.href = 'view_medicines.php';
              </script>";
    } else {
        // No related orders, safe to delete
        $delete_medicine = "DELETE FROM medicines WHERE id = $delete_id";
        if (mysqli_query($conn, $delete_medicine)) {
            echo "<script>
                    alert('Medicine deleted successfully!');
                    window.location.href = 'view_medicines.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error deleting medicine: " . mysqli_error($conn) . "');
                    window.location.href = 'view_medicines.php';
                  </script>";
        }
    }
}



// Fetch all medicines from the database
$sql = "SELECT * FROM medicines";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Medicines</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #1977F3;
            color: white;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }

        .btn-delete {
            color: red;
            cursor: pointer;
        }
        .btn-update {
            color: green;
            cursor: pointer;
        }
        img {
            border-radius: 5px;
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

    <h1>Medicines List</h1>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>
                            <?php if ($row['image']): ?>
                                <img src="<?php echo $row['image']; ?>" alt="Image" width="50" height="50">
                            <?php else: ?>
                                No Image
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="update_medicine.php?id=<?php echo $row['id']; ?>" class="btn-update">Update</a> |
                            <a href="view_medicines.php?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this medicine?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No medicines found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
