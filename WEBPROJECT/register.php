<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = htmlspecialchars($_POST['role'], ENT_QUOTES, 'UTF-8');
    $photo = '';

    // Create uploads directory if it doesn't exist
    $uploadsDir = 'uploads';
    if (!is_dir($uploadsDir)) mkdir($uploadsDir, 0777, true);

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            exit('File size exceeds 2MB.');
        }
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array(mime_content_type($_FILES['photo']['tmp_name']), $allowedTypes)) {
            exit('Invalid file type.');
        }
        // File Saving
        $photo = $uploadsDir . '/' . uniqid() . '_' . basename($_FILES['photo']['name']);
        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $photo)) {
            exit('Photo upload failed.');
        }
    }

    // Save user data in the database 
    try {
        $stmt = $conn->prepare("INSERT INTO users (username, password, role, photo) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $role, $photo]);
        echo "Account created successfully!";
    } catch (PDOException $e) {
        exit("Error: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form method="POST" action="" enctype="multipart/form-data">
        <h2>Create Account</h2>
        <label>Username</label>
        <input type="text" name="username" required>
        <label>Password</label>
        <input type="password" name="password" required>
        <label>Role</label>
        <select name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
        <label>Photo</label>
        <input type="file" name="photo" accept="image/*">
        <button type="submit">Register</button>
        <button type="button" onclick="window.location.href='login.php'">Go to Login</button>
        <button type="button" onclick="window.location.href='index.php'">Go to Home</button>

    </form>
</body>
</html>