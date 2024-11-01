<?php
include 'db_connect.php'; // Database connection

// Collect form data
$code = $_POST['code'];
$name = $_POST['name'];
$year = $_POST['year'];
$semester = $_POST['semester'];
$department_name = $_POST['department_name'];

// Prepare SQL insert query for the course
$sql = "INSERT INTO Course (code, name, year, semester, department_name) VALUES (?, ?, ?, ?, ?)";

// Check if the department exists
$checkDepartmentSQL = "SELECT * FROM department WHERE name = ?";
$checkStmt = $conn->prepare($checkDepartmentSQL);
if ($checkStmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind and execute
$checkStmt->bind_param("s", $department_name);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Department exists, proceed to insert the course
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
} else {
    // Optionally, add the department if it doesn't exist
    $addDepartmentSQL = "INSERT INTO department (name) VALUES (?)";
    $addStmt = $conn->prepare($addDepartmentSQL);
    if ($addStmt === false) {
        die("Error preparing statement: " . $conn->error);
    }

    // Bind and execute to add the new department
    $addStmt->bind_param("s", $department_name);
    if ($addStmt->execute()) {
        echo "Department '$department_name' added successfully. Now you can add the course.";
        
        // Now re-attempt to add the course
        $stmt = $conn->prepare($sql);  // Use the previously defined $sql variable
        if ($stmt === false) {
            die("Error preparing statement: " . $conn->error);
        }
        
        $stmt->bind_param("ssiis", $code, $name, $year, $semester, $department_name);
        if ($stmt->execute()) {
            echo "Course added successfully!";
        } else {
            echo "Error: " . $stmt->error;
        }
        
        $stmt->close();
    } else {
        echo "Error: Could not add department '$department_name'.";
    }

    $addStmt->close();
}

$checkStmt->close();
$conn->close();
?>
