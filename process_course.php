<?php
include 'db_connect.php'; // Database connection

// Collect form data
$code = $_POST['code'];
$name = $_POST['name'];
$year = $_POST['year'];
$semester = $_POST['semester'];
$department_name = $_POST['department_name'];

// Prepare SQL insert query
$sql = "INSERT INTO Course (code, name, year, semester, department_name) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind and execute
$stmt->bind_param("ssiis", $code, $name, $year, $semester, $department_name);
if ($stmt->execute()) {
    echo "Course added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
