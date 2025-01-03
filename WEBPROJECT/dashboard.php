<?php
include 'db.php';
session_start();

// Ensure only admins can access the dashboard
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

// Fetch all users excluding other admins
$users = $conn->query("SELECT * FROM users WHERE role != 'admin'")->fetchAll(PDO::FETCH_ASSOC);

// Handle user deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    
    // Ensure the admin is not trying to delete themselves or another admin
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $user['role'] != 'admin' && $id != $_SESSION['id']) {
        $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$id]);
    }
    // Redirect to Dashboard
    header('Location: dashboard.php');
    exit;
}

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    // Prevent adding another admin
    if ($role == 'admin') {
        header('Location: dashboard.php');
        exit;
    }

    $photo = '';
    if ($_FILES['photo']['error'] == 0) {
        $photo = 'uploads/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    $stmt = $conn->prepare("INSERT INTO users (username, password, role, photo) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $password, $role, $photo]);

    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dash.css">
</head>
<body>
    <h2>Admin Dashboard</h2>
    <button onclick="document.getElementById('addUserModal').style.display='block'">Add User</button>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Photo</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= $user['username'] ?></td>
                    <td><?= $user['role'] ?></td>
                    <td>
                        <?php if ($user['photo']): ?>
                            <img src="<?= $user['photo'] ?>" alt="Photo" width="50">
                        <?php else: ?>
                            No photo
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $user['id'] ?>">Edit</a>
                        <a href="dashboard.php?delete=<?= $user['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button type="button" onclick="window.location.href='index.php'">Go to Home</button>

    <!-- Add User Modal -->
    <div id="addUserModal" style="display: none;">
        <form method="POST" action="" enctype="multipart/form-data">
            <h2>Add User</h2>
            <label>Username</label>
            <input type="text" name="username" required>
            <label>Password</label>
            <input type="password" name="password" required>
            <label>Role</label>
            <select name="role" required>
                <option value="user">User</option>
            </select>
            <label>Photo</label>
            <input type="file" name="photo" accept="image/*">
            <button type="submit" name="add_user">Add User</button>
            <button type="button" onclick="document.getElementById('addUserModal').style.display='none'">Cancel</button>
        </form>
    </div>
</body>
</html>
