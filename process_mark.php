<?php
include 'db_connect.php'; // Database connection

// Collect form data
$student_id = $_POST['student_id'];
$course_code = $_POST['course_code'];
$mark = $_POST['mark'];
$grade = $_POST['grade'];
$comment = $_POST['comment'];

// Prepare SQL insert query
$sql = "INSERT INTO Mark (student_id, course_code, mark, grade, comment) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind and execute
$stmt->bind_param("isiss", $student_id, $course_code, $mark, $grade, $comment);
if ($stmt->execute()) {
    echo "Mark added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
