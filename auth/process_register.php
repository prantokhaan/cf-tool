<?php
// Database configuration
include '../database/db.php';


$errors = [];

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $codeforces_handle = htmlspecialchars(trim($_POST['codeforces_handle']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check for empty fields
    if (empty($username)) {
        $errors[] = "Username is required.";
    }
    if (empty($email)) {
        $errors[] = "Email is required.";
    }
    if (empty($codeforces_handle)) {
        $errors[] = "Codeforces handle is required.";
    }
    if (empty($password)) {
        $errors[] = "Password is required.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $errors[] = "Username or Email already exists.";
    }

    // Check if Codeforces handle exists and matches the input (basic check)
    $cfApiUrl = "https://codeforces.com/api/user.info?handles=$codeforces_handle";
    $cfResponse = file_get_contents($cfApiUrl);
    $cfData = json_decode($cfResponse, true);

    if ($cfData['status'] !== 'OK') {
        $errors[] = "Invalid Codeforces handle.";
    } else {
        $cfHandleFromAPI = $cfData['result'][0]['handle']; // Assuming the API returns an array of results
        if ($cfHandleFromAPI !== $codeforces_handle) {
            $errors[] = "Codeforces handle does not match the provided handle.";
        }
    }

    // If no errors, insert user into database
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into database
        $insertStmt = $conn->prepare("INSERT INTO users (username, email, codeforces_handle, password) VALUES (?, ?, ?, ?)");
        $insertStmt->bind_param("ssss", $username, $email, $codeforces_handle, $hashedPassword);

        if ($insertStmt->execute()) {
            // Redirect to login page or show success message
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Failed to register user. Please try again later.";
        }
    }
}

// Close connection
$conn->close();
?>