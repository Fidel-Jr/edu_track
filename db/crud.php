<?php
$dsn = "mysql:host=localhost;dbname=myapp;charset=utf8mb4";
$username = "root";
$password = "";


try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<p style='font-size:50px;'>Connected successfully</p>";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// INSERT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert'])) {
    $stmt = $pdo->prepare("INSERT INTO student (first_name, last_name, age, email) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['age'],
        $_POST['email']
    ]);
}

// DELETE
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM student WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $stmt = $pdo->prepare("UPDATE student SET first_name = ?, last_name = ?, age = ?, email = ? WHERE id = ?");
    $stmt->execute([
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['age'],
        $_POST['email'],
        $_POST['id']
    ]);
}

// Get all students
$students = $pdo->query("SELECT * FROM student")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student CRUD - PDO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <h2 class="mb-4 text-center">Student Management (PDO Safe)</h2>

    <!-- Add Student -->
    <div class="card p-4 mb-4">
        <h4>Add Student</h4>
        <form method="post" action="">
            <div class="mb-3">
                <input type="text" name="first_name" class="form-control" placeholder="First Name" required>
            </div>
            <div class="mb-3">
                <input type="text" name="last_name" class="form-control" placeholder="Last Name" required>
            </div>
            <div class="mb-3">
                <input type="number" name="age" class="form-control" placeholder="Age" required>
            </div>
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <button type="submit" name="insert" class="btn btn-success">Add Student</button>
        </form>
    </div>

    <!-- Student Records -->
    <div class="card p-4">
        <h4>Student Records</h4>
        <table class="table table-bordered table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th><th>Name</th><th>Age</th><th>Email</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($students as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                    <td><?= htmlspecialchars($row['age']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Are you sure you want to delete this student?');">Delete</a>

                        <button class="btn btn-primary btn-sm" onclick="toggleForm(<?= $row['id'] ?>)">Edit</button>
                    </td>
                </tr>

                <tr id="form-<?= $row['id'] ?>" style="display:none;">
                    <td colspan="5">
                        <form method="post" action="">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($row['id']) ?>">
                            <div class="row">
                                <div class="col"><input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($row['first_name']) ?>" required></div>
                                <div class="col"><input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($row['last_name']) ?>" required></div>
                                <div class="col"><input type="number" name="age" class="form-control" value="<?= htmlspecialchars($row['age']) ?>" required></div>
                                <div class="col"><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>" required></div>
                                <div class="col"><button type="submit" name="update" class="btn btn-warning">Update</button></div>
                            </div>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleForm(id) {
    let row = document.getElementById("form-" + id);
    row.style.display = (row.style.display === "none") ? "table-row" : "none";
}
</script>

</body>
</html>
