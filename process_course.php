<?php
include 'db_connect.php'; // Database connection

$message = ''; // To store messages for success or errors

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle adding a new course
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $code = $_POST['code'] ?? null;
        $name = $_POST['name'] ?? null;
        $year = $_POST['year'] ?? null;
        $semester = $_POST['semester'] ?? null;
        $department_name = $_POST['department_name'] ?? null;

        // Validate that none of the required fields are null
        if ($code && $name && $year && $semester && $department_name) {
            // Check if the course already exists
            $checkCourseSQL = "SELECT * FROM Course WHERE code = ?";
            $checkStmt = $conn->prepare($checkCourseSQL);
            $checkStmt->bind_param("s", $code);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $message = "Error: Course with code '$code' already exists.";
            } else {
                // Prepare SQL insert query
                $sql = "INSERT INTO Course (code, name, year, semester, department_name) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssiss", $code, $name, $year, $semester, $department_name);

                // Execute and check for success
                if ($stmt->execute()) {
                    $message = "Course added successfully!";
                } else {
                    $message = "Error: " . $stmt->error;
                }
                $stmt->close();
            }
            $checkStmt->close();
        } else {
            $message = "Error: All fields are required.";
        }
    }

    // Handle updating an existing course
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $course_code = $_POST['course_code'] ?? null;
        $code = $_POST['code'] ?? null;
        $name = $_POST['name'] ?? null;
        $year = $_POST['year'] ?? null;
        $semester = $_POST['semester'] ?? null;
        $department_name = $_POST['department_name'] ?? null;

        // Validate that all fields are provided
        if ($course_code && $code && $name && $year && $semester && $department_name) {
            $sql = "UPDATE Course SET code = ?, name = ?, year = ?, semester = ?, department_name = ? WHERE code = ?";
            $stmt = $conn->prepare($sql);
            if ($stmt === false) {
                die("Error preparing statement: " . $conn->error);
            }
            $stmt->bind_param("ssissi", $code, $name, $year, $semester, $department_name, $course_code);
            if ($stmt->execute()) {
                $message = "Course updated successfully!";
            } else {
                $message = "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $message = "Error: All fields are required.";
        }
    }

    // Handle deleting a course
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $course_code = $_POST['course_id']; // Updated to use course_code
        $sql = "DELETE FROM Course WHERE code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $course_code); // Changed 'i' to 's' for string
        if ($stmt->execute()) {
            $message = "Course deleted successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch courses from the database to display
$sql = "SELECT * FROM Course";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Course List</title>
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
    <h1 class="text-center">Course List</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form to add new course -->
    <h3>Add New Course</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="code">Course Code</label>
            <input type="text" name="code" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="name">Course Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="year">Year</label>
            <input type="number" name="year" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="semester">Semester</label>
            <input type="text" name="semester" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="department_name">Department Name</label>
            <input type="text" name="department_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Course</button>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Year</th>
                    <th>Semester</th>
                    <th>Department Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['code']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['year']); ?></td>
                        <td><?php echo htmlspecialchars($row['semester']); ?></td>
                        <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                        <td>
                            <!-- Delete Form -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="course_id" value="<?php echo $row['code']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>

                            <!-- Update Button -->
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#updateModal<?php echo $row['code']; ?>">
                                Edit
                            </button>

                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal<?php echo $row['code']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateModalLabel">Edit Course</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="course_code" value="<?php echo $row['code']; ?>">
                                                <div class="form-group">
                                                    <label for="code">Course Code</label>
                                                    <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($row['code']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="name">Course Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="year">Year</label>
                                                    <input type="number" name="year" class="form-control" value="<?php echo htmlspecialchars($row['year']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="semester">Semester</label>
                                                    <input type="text" name="semester" class="form-control" value="<?php echo htmlspecialchars($row['semester']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="department_name">Department Name</label>
                                                    <input type="text" name="department_name" class="form-control" value="<?php echo htmlspecialchars($row['department_name']); ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Course</button>
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
            No courses found.
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
