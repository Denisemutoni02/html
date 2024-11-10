<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php'; // Database connection

$message = ''; // To store messages for success or errors
$action = ''; // Initialize action variable

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    // Collect form data
    $action = $_POST['action'];
    
    // Common variables
    $name = $_POST['name'] ?? '';
    $access_no = $_POST['access_no'] ?? '';
    $contact = $_POST['contact'] ?? '';
    $program = $_POST['program'] ?? '';
    $department_name = $_POST['department'] ?? '';
    $address = $_POST['address'] ?? '';
    $email = $_POST['email'] ?? '';
    $sex = $_POST['sex'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = isset($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : '';
    $age = $_POST['age'] ?? 0; // Default age to 0 if not set

    if ($action === 'add') {
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
                $message = "Error inserting department: " . $insertDepartment->error;
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
        $stmt->bind_param("sssssssssis", $name, $access_no, $contact, $program, $address, $email, $sex, $username, $password, $age, $department_name);
        
        // Execute and check for success
        if ($stmt->execute()) {
            $message = "Student added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif ($action === 'update') {
        // Update existing student record
        $student_id = $_POST['student_id'];

        $updateSQL = "UPDATE Student SET name=?, access_no=?, contact=?, program=?, address=?, email=?, sex=?, username=?, password=?, age=?, department_name=? WHERE id=?";
        $updateStmt = $conn->prepare($updateSQL);
        $updateStmt->bind_param("ssssssssssi", $name, $access_no, $contact, $program, $address, $email, $sex, $username, $password, $age, $department_name, $student_id);

        if ($updateStmt->execute()) {
            $message = "Student updated successfully!";
        } else {
            $message = "Error: " . $updateStmt->error;
        }
        $updateStmt->close();
    } elseif ($action === 'delete') {
        // Delete a student record
        $student_id = $_POST['student_id'];

        $deleteSQL = "DELETE FROM Student WHERE id=?";
        $deleteStmt = $conn->prepare($deleteSQL);
        $deleteStmt->bind_param("i", $student_id);
        if ($deleteStmt->execute()) {
            $message = "Student deleted successfully!";
        } else {
            $message = "Error: " . $deleteStmt->error;
        }
        $deleteStmt->close();
    }
}

// Fetch all students to display
$studentsResult = $conn->query("SELECT * FROM Student");

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Student Registration</title>
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
    <h1 class="text-center">Student Registration</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <h3>Add New Student</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="name">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="access_no">Access No</label>
            <input type="text" name="access_no" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="contact">Contact</label>
            <input type="text" name="contact" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="program">Program</label>
            <input type="text" name="program" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" name="department" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" name="address" class="form-control">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" name="email" class="form-control">
        </div>
        <div class="form-group">
            <label for="sex">Sex</label>
            <select name="sex" class="form-control" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" name="age" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Student</button>
    </form>

    <?php if ($studentsResult && $studentsResult->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Name</th>
                    <th>Access No</th>
                    <th>Contact</th>
                    <th>Program</th>
                    <th>Department</th>
                    <th>Email</th>
                    <th>Sex</th>
                    <th>Age</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $studentsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['access_no']); ?></td>
                        <td><?php echo htmlspecialchars($row['contact']); ?></td>
                        <td><?php echo htmlspecialchars($row['program']); ?></td>
                        <td><?php echo htmlspecialchars($row['department_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['sex']); ?></td>
                        <td><?php echo htmlspecialchars($row['age']); ?></td>
                        <td>
                            <!-- Delete Form -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
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
                                            <h5 class="modal-title" id="updateModalLabel">Update Student</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="student_id" value="<?php echo $row['id']; ?>">
                                                <div class="form-group">
                                                    <label for="name">Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="access_no">Access No</label>
                                                    <input type="text" name="access_no" class="form-control" value="<?php echo htmlspecialchars($row['access_no']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="contact">Contact</label>
                                                    <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($row['contact']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="program">Program</label>
                                                    <input type="text" name="program" class="form-control" value="<?php echo htmlspecialchars($row['program']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="department">Department</label>
                                                    <input type="text" name="department" class="form-control" value="<?php echo htmlspecialchars($row['department_name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="address">Address</label>
                                                    <input type="text" name="address" class="form-control" value="<?php echo htmlspecialchars($row['address']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="email">Email</label>
                                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>">
                                                </div>
                                                <div class="form-group">
                                                    <label for="sex">Sex</label>
                                                    <select name="sex" class="form-control" required>
                                                        <option value="Male" <?php echo $row['sex'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                                                        <option value="Female" <?php echo $row['sex'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                                                        <option value="Other" <?php echo $row['sex'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="age">Age</label>
                                                    <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($row['age']); ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Student</button>
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
        <p>No students found.</p>
    <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
