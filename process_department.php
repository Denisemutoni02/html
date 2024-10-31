<?php
include 'db_connect.php'; // Database connection

// Collect form data
$name = $_POST['name'];
$head = $_POST['head'];

// Prepare SQL insert query
$sql = "INSERT INTO Department (name, head) VALUES (?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind and execute
$stmt->bind_param("ss", $name, $head);
if ($stmt->execute()) {
    echo "Department added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
