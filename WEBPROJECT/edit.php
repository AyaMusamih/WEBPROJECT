<?php
include 'db.php';

// Check for User ID in the URL
if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = $_GET['id'];

// Fetch user details 
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];
    $photo = $user['photo'];
    $password = $user['password']; 

    // Update password if a new password is provided
    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    }

    // Handle photo upload
    if ($_FILES['photo']['error'] == 0) {
        $photo = 'uploads/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
    }

    // Update user info in the database
    $stmt = $conn->prepare("UPDATE users SET username = ?, role = ?, password = ?, photo = ? WHERE id = ?");
    $stmt->execute([$username, $role, $password, $photo, $id]);

    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form method="POST" action="" enctype="multipart/form-data">
        <h2>Edit User</h2>
        <label>Username</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        <label>Role</label>
        <select name="role" required>
            <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
            <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
        </select>
        <label>New Password (leave blank to keep the current password)</label>
        <input type="password" name="password">
        <label>Photo</label>
        <input type="file" name="photo" accept="image/*">
        <?php if ($user['photo']): ?>
            <p>Current photo:</p>
            <img src="<?= $user['photo'] ?>" alt="Photo" width="100">
        <?php endif; ?>
        <button type="submit">Update</button>
        <a href="dashboard.php">Cancel</a>
    </form>
</body>
</html>