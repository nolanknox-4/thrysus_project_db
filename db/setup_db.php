<?php
include 'db_config.php';

$query = "
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($db_connect, $query)) {
    echo "Users table created successfully.";
} else {
    echo "Error creating table: " . mysqli_error($db_connect);
}
?>
