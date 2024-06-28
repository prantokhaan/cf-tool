<?php

session_start();
include '../database/db.php';

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $oldPassword = $_POST['oldPassword'];
    $newPassword = $_POST['newPassword'];
    $confirmNewPassword = $_POST['confirmNewPassword'];

    // Validate inputs
    if ($newPassword !== $confirmNewPassword) {
        header('Location: change_password.php?error=password_mismatch');
        exit;
    }

    // Check if email exists in the database
    $stmt = $conn->prepare('SELECT id, password FROM users WHERE username = ?');
    if (!$stmt) {
        exit('Error preparing statement: '.$conn->error);
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        header('Location: change_password.php?error=username_not_found');
        exit;
    }

    // Verify old password
    if (!password_verify($oldPassword, $user['password'])) {
        header('Location: change_password.php?error=incorrect_password');
        exit;
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Update the password in the database
    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
    if (!$stmt) {
        exit('Error preparing statement: '.$conn->error);
    }
    $stmt->bind_param('si', $hashedPassword, $user['id']);
    if ($stmt->execute()) {
        // Password changed successfully
        $_SESSION['password_change_success'] = true;
        echo "<script>
            alert('Password changed successfully.');
            window.location.href = 'profile.php';
        </script>";
        exit;
    } else {
        $_SESSION['password_change_error'] = 'Error updating password: '.$stmt->error;
        header('Location: change_password.php?error=unknown_error');
        exit;
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
} else {
    echo 'Invalid request method.';
}
?>          