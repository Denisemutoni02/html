<?php
include 'db_connect.php'; // Database connection

// Collect form data
$student_id = $_POST['student_id'];
$course_code = $_POST['course_code'];
$mark = $_POST['mark'];
$grade = $_POST['grade'];
$comment = $_POST['comment'];

// Check if the student exists
$checkStudentSQL = "SELECT * FROM student WHERE id = ?";
$checkStmt = $conn->prepare($checkStudentSQL);
if ($checkStmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind and execute for student check
$checkStmt->bind_param("i", $student_id);
$checkStmt->execute();
$studentResult = $checkStmt->get_result();

if ($studentResult->num_rows > 0) {
    // Check if the course exists
    $checkCourseSQL = "SELECT * FROM course WHERE code = ?";
    $checkCourseStmt = $conn->prepare($checkCourseSQL);
    if ($checkCourseStmt === false) {
        die("Error preparing course statement: " . $conn->error);
    }

    // Bind and execute for course check
    $checkCourseStmt->bind_param("s", $course_code);
    $checkCourseStmt->execute();
    $courseResult = $checkCourseStmt->get_result();

    if ($courseResult->num_rows > 0) {
        // Course exists, proceed to insert the mark
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
    } else {
        // Provide feedback on existing courses
        echo "Error: Course with code '$course_code' does not exist. Available course codes: ";
        $availableCoursesSQL = "SELECT code FROM course";
        $availableCoursesResult = $conn->query($availableCoursesSQL);

        if ($availableCoursesResult->num_rows > 0) {
            while ($row = $availableCoursesResult->fetch_assoc()) {
                echo $row['code'] . " ";
            }
        } else {
            echo "No courses found in the database.";
        }
    }

    $checkCourseStmt->close();
} else {
    // Output available student IDs for troubleshooting
    $availableStudentsSQL = "SELECT id FROM student";
    $availableStudentsResult = $conn->query($availableStudentsSQL);
    
    if ($availableStudentsResult->num_rows > 0) {
        echo "Error: Student with ID '$student_id' does not exist. Available IDs: ";
        while ($row = $availableStudentsResult->fetch_assoc()) {
            echo $row['id'] . " ";
        }
    } else {
        echo "Error: No students found in the database.";
    }
}

$checkStmt->close();
$conn->close();
?>
