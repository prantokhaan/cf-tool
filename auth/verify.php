<?php
include '../database/db.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    // Check if the verification code exists
    $stmt = $mysqli->prepare("SELECT * FROM pending_users WHERE verification_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Move user to the main users table
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, codeforces_handle, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $user['username'], $user['email'], $user['codeforces_handle'], $user['password']);
        $stmt->execute();

        // Delete from pending users table
        $stmt = $mysqli->prepare("DELETE FROM pending_users WHERE verification_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();

        echo "Your email has been verified. You can now <a href='login.php'>login</a>.";
    } else {
        echo "Invalid verification code.";
    }
} else {
    echo "No verification code provided.";
}
?>
