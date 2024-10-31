<?php
include 'db_connect.php'; // Database connection

// Collect form data
$name = $_POST['name'];
$access_no = $_POST['access_no'];
$contact = $_POST['contact'];
$program = $_POST['program'];
$department_name = $_POST['department']; // Variable name to match the column in the database
$address = $_POST['address'];
$email = $_POST['email'];
$sex = $_POST['sex'];
$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hashing the password
$age = $_POST['age'];

// Check if the department exists
$checkDepartment = $conn->prepare("SELECT * FROM department WHERE name = ?");
$checkDepartment->bind_param("s", $department_name);
$checkDepartment->execute();
$result = $checkDepartment->get_result();

if ($result->num_rows == 0) {
    // The department doesn't exist, insert it
    $insertDepartment = $conn->prepare("INSERT INTO department (name) VALUES (?)");
    $insertDepartment->bind_param("s", $department_name);
    
    if (!$insertDepartment->execute()) {
        echo "Error inserting department: " . $insertDepartment->error;
        $insertDepartment->close();
        $conn->close();
        exit; // Exit if department insert fails
    }
    $insertDepartment->close(); // Close the department insert statement
}

// Prepare SQL insert query for the student
$sql = "INSERT INTO Student (name, access_no, contact, program, address, email, sex, username, password, age, department_name)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Prepare and bind
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param("sssssssssis", $name, $access_no, $contact, $program, $address, $email, $sex, $username, $password, $age, $department_name);

// Execute and check for success
if ($stmt->execute()) {
    echo "Student added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
