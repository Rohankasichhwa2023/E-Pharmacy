<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pharma";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE TABLE orders (
        order_id VARCHAR(50) NOT NULL,  -- Unique order ID for each cart
        user_id INT NOT NULL,
        medicine_id INT NOT NULL,
        quantity INT NOT NULL,
        total_price DECIMAL(10,2) NOT NULL,
        payment_type varchar(50),
        order_address TEXT NOT NULL,
        prescription_status Boolean,
        prescription_upload varchar(255), -- path to store prescriptions
        order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        order_status ENUM('Pending', 'Approved', 'Shipped', 'Delivered', 'Cancelled') DEFAULT 'Pending',
        PRIMARY KEY (order_id, medicine_id),  -- Composite key to allow multiple medicines in the same order
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (medicine_id) REFERENCES medicines(id)
    );";

    if ($conn->query($sql) === TRUE) {
    echo "Table orders created successfully";
    } else {
    echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>