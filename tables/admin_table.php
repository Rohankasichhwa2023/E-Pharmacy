<?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "pharma";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
    }

    $sql = "CREATE TABLE admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    );";

    if ($conn->query($sql) === TRUE) {
    echo "Table admin created successfully";
    } else {
    echo "Error creating table: " . $conn->error;
    }

    $conn->close();
?>