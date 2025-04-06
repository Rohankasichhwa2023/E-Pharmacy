<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pharma";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone VARCHAR(10),
        profile_pic varchar(255),
        password VARCHAR(255) NOT NULL
    );";

    if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully";
    } else {
    echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>