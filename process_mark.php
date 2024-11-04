<?php
include 'db_connect.php'; // Database connection

$message = ''; // To store messages for success or errors

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle adding a new mark
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];
        $mark = $_POST['mark'];
        $grade = $_POST['grade'];
        $comment = $_POST['comment'];

        // Check if the student exists
        $checkStudentSQL = "SELECT * FROM student WHERE id = ?";
        $checkStmt = $conn->prepare($checkStudentSQL);
        $checkStmt->bind_param("i", $student_id);
        $checkStmt->execute();
        $studentResult = $checkStmt->get_result();

        if ($studentResult->num_rows > 0) {
            // Check if the course exists
            $checkCourseSQL = "SELECT * FROM course WHERE code = ?";
            $checkCourseStmt = $conn->prepare($checkCourseSQL);
            $checkCourseStmt->bind_param("s", $course_code);
            $checkCourseStmt->execute();
            $courseResult = $checkCourseStmt->get_result();

            if ($courseResult->num_rows > 0) {
                // Insert the mark
                $sql = "INSERT INTO Mark (student_id, course_code, mark, grade, comment) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isiss", $student_id, $course_code, $mark, $grade, $comment);
                if ($stmt->execute()) {
                    $message = "Mark added successfully!";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $message = "Error: Course with code '$course_code' does not exist.";
            }

            $checkCourseStmt->close();
        } else {
            $message = "Error: Student with ID '$student_id' does not exist.";
        }

        $checkStmt->close();
    }

    // Handle updating an existing mark
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $mark_id = $_POST['mark_id'];
        $student_id = $_POST['student_id'];
        $course_code = $_POST['course_code'];
        $mark = $_POST['mark'];
        $grade = $_POST['grade'];
        $comment = $_POST['comment'];

        $sql = "UPDATE Mark SET student_id = ?, course_code = ?, mark = ?, grade = ?, comment = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssi", $student_id, $course_code, $mark, $grade, $comment, $mark_id);
        if ($stmt->execute()) {
            $message = "Mark updated successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Handle deleting a mark
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $mark_id = $_POST['mark_id'];

        $sql = "DELETE FROM Mark WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mark_id);
        if ($stmt->execute()) {
            $message = "Mark deleted successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all marks to display
$marksResult = $conn->query("SELECT * FROM Mark");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Marks List</title>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 20px;
        }
        table {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Marks List</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form to add new marks -->
    <h3>Add New Mark</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="student_id">Student ID</label>
            <input type="number" name="student_id" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="course_code">Course Code</label>
            <input type="text" name="course_code" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="mark">Mark</label>
            <input type="number" name="mark" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="grade">Grade</label>
            <input type="text" name="grade" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="comment">Comment</label>
            <input type="text" name="comment" class="form-control">
        </div>
        <button type="submit" class="btn btn-primary">Add Mark</button>
    </form>

    <?php if ($marksResult && $marksResult->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Course Code</th>
                    <th>Mark</th>
                    <th>Grade</th>
                    <th>Comment</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $marksResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['course_code']); ?></td>
                        <td><?php echo htmlspecialchars($row['mark']); ?></td>
                        <td><?php echo htmlspecialchars($row['grade']); ?></td>
                        <td><?php echo htmlspecialchars($row['comment']); ?></td>
                        <td>
                            <!-- Delete Form -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="mark_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>

                            <!-- Update Button -->
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#updateModal<?php echo $row['id']; ?>">
                                Edit
                            </button>

                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateModalLabel">Edit Mark</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="mark_id" value="<?php echo $row['id']; ?>">
                                                <div class="form-group">
                                                    <label for="student_id">Student ID</label>
                                                    <input type="number" name="student_id" class="form-control" value="<?php echo htmlspecialchars($row['student_id']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="course_code">Course Code</label>
                                                    <input type="text" name="course_code" class="form-control" value="<?php echo htmlspecialchars($row['course_code']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="mark">Mark</label>
                                                    <input type="number" name="mark" class="form-control" value="<?php echo htmlspecialchars($row['mark']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="grade">Grade</label>
                                                    <input type="text" name="grade" class="form-control" value="<?php echo htmlspecialchars($row['grade']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="comment">Comment</label>
                                                    <input type="text" name="comment" class="form-control" value="<?php echo htmlspecialchars($row['comment']); ?>">
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Mark</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            No marks found.
        </div>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
