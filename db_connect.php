<?php
$servername = "localhost";
$username = "root"; // Default username in XAMPP
$password = "";     // Leave blank for the default XAMPP configuration
$dbname = "school_db"; // Name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
