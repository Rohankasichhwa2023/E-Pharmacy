<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pharma";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE TABLE medicines (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        category VARCHAR(50),
        manufacturer VARCHAR(100),
        price DECIMAL(10, 2) NOT NULL,
        quantity INT NOT NULL,
        dosage VARCHAR(50),
        age_group VARCHAR(50),
        side_effects TEXT,
        usages TEXT,
        precautions TEXT,
        storage_info VARCHAR(255),
        prescription_required BOOLEAN,
        image VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    if ($conn->query($sql) === TRUE) {
    echo "Table medicine created successfully";
    } else {
    echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>