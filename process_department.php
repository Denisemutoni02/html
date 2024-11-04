<?php
include 'db_connect.php'; // Database connection

$message = ''; // To store messages for success or errors

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle adding a new department
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = $_POST['name'] ?? null;
        $head = $_POST['head'] ?? null;

        // Prepare SQL insert query
        $sql = "INSERT INTO Department (name, head) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $name, $head);

        // Execute and check for success
        if ($stmt->execute()) {
            $message = "Department added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Handle updating an existing department
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        $department_id = $_POST['department_id'];
        $name = $_POST['name'] ?? null;
        $head = $_POST['head'] ?? null;

        $sql = "UPDATE Department SET name = ?, head = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $name, $head, $department_id);
        if ($stmt->execute()) {
            $message = "Department updated successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    // Handle deleting a department
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $department_id = $_POST['department_id'];

        $sql = "DELETE FROM Department WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $department_id);
        if ($stmt->execute()) {
            $message = "Department deleted successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Fetch all departments to display
$result = $conn->query("SELECT * FROM Department");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Department List</title>
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
    <h1 class="text-center">Department List</h1>

    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?>" role="alert">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Form to add new department -->
    <h3>Add New Department</h3>
    <form method="POST" action="">
        <input type="hidden" name="action" value="add">
        <div class="form-group">
            <label for="name">Department Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="head">Department Head</label>
            <input type="text" name="head" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Department</button>
    </form>

    <?php if ($result && $result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead class="thead-dark">
                <tr>
                    
                    <th>Name</th>
                    <th>Head</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['head']); ?></td>
                        <td>
                            <!-- Delete Form -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>

                            <!-- Update Button -->
                            <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#updateModal<?php echo $row['name']; ?>">
                                Edit
                            </button>

                            <!-- Update Modal -->
                            <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="updateModalLabel">Edit Department</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form method="POST" action="">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="department_id" value="<?php echo $row['id']; ?>">
                                                <div class="form-group">
                                                    <label for="name">Department Name</label>
                                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($row['name']); ?>" required>
                                                </div>
                                                <div class="form-group">
                                                    <label for="head">Department Head</label>
                                                    <input type="text" name="head" class="form-control" value="<?php echo htmlspecialchars($row['head']); ?>" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Update Department</button>
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
            No departments found.
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
