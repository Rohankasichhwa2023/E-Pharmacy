<?php
session_start();
include '../db.php';

$query = $_GET['query'] ?? '';
$sql = "SELECT * FROM medicines";

if ($query !== '') {
    $query = mysqli_real_escape_string($conn, $query);
    $sql .= " WHERE name LIKE '%$query%' 
              OR category LIKE '%$query%' 
              OR price LIKE '%$query%'
              OR usages LIKE '%$query%'
              OR manufacturer LIKE '%$query%'";
}

$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<div class="medicine-post">';
        echo '<a href="medicine_detail.php?id='.htmlspecialchars($row['id']).'" style="text-decoration: none; color: inherit;">';
        echo '<div class="image-container">';
        
        if (!empty($row['image'])) {
            echo '<img src="'.htmlspecialchars($row['image']).'" alt="'.htmlspecialchars($row['name']).'">';
        } else {
            echo '<img src="placeholder.png" alt="No Image Available">';
        }

        if ($row['prescription_required']) {
            echo '<div class="prescription-label">Prescription Required</div>';
        }

        echo '</div>';
        echo '<div class="medicine-name">'.htmlspecialchars($row['name']).'</div>';
        echo '<div class="price-cart-container">';
        echo '<div class="price">Rs '.htmlspecialchars($row['price']).'</div>';
        echo '<form action="home.php" method="POST">';
        echo '<input type="hidden" name="medicine_id" value="'.htmlspecialchars($row['id']).'">';
        echo '<button class="add-to-cart" type="submit" name="add_to_cart">Add to Cart</button>';
        echo '</form>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }
} else {
    echo '<p>No medicines found for your search.</p>';
}

mysqli_close($conn);
?>
