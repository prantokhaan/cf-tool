<?php
include '../database/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $newHandle = $_POST['newHandle'];
    $oldHandle = $_POST['oldHandle'];
    $password = $_POST['password'];
    $error = '';
    $success = '';

    // Check if the username exists in the database
    $stmt = $conn->prepare('SELECT id, password FROM users WHERE username = ?');
    if (!$stmt) {
        exit('Database error');
    }
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) {
        $error = 'Username not found';
    } else {
        // Verify the password
        if (!password_verify($password, $user['password'])) {
            $error = 'Incorrect password';
        } else {
            // Check if the new handle exists on Codeforces
            $cfUrl = 'https://codeforces.com/api/user.info?handles='.urlencode($newHandle);
            $cfResponse = file_get_contents($cfUrl);
            $cfData = json_decode($cfResponse, true);

            if ($cfData['status'] !== 'OK') {
                $error = 'Codeforces handle does not exist';
            } else {
                // Update the handle in the database
                $stmt = $conn->prepare('UPDATE users SET codeforces_handle = ? WHERE id = ?');
                if (!$stmt) {
                    exit('Database error');
                }
                $stmt->bind_param('si', $newHandle, $user['id']);
                if ($stmt->execute()) {
                    $submissions = 'user_submissions_problems_' . $oldHandle;
                    $submissionsTimeStamp = 'user_submissions_problems_' . $oldHandle . '_timestamp';
                    $solvedProblems = 'cfSolvedProblems_' . $oldHandle;
                    $solvedTags = 'cfSolvedTags_' . $oldHandle;
                    $userSubmissions = 'user_submissions_' . $oldHandle;
                    $userSubmissionsTimeStamp = 'user_submissions_' . $oldHandle . '_timestamp';
                    echo "<script>
                        localStorage.setItem('cfUser', '$newHandle');
                        localStorage.removeItem('$submissions');
                        localStorage.removeItem('$submissionsTimeStamp');
                        localStorage.removeItem('$solvedProblems');
                        localStorage.removeItem('$solvedTags');
                        localStorage.removeItem('$userSubmissions');
                        localStorage.removeItem('$userSubmissionsTimeStamp');

                    </script>";
                    $success = 'Handle changed successfully';
                } else {
                    $error = 'Error changing handle';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Handle</title>
    <link rel="stylesheet" href="../css/changePassword.css">
    <link rel="icon" href="../images/favicon.png">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <h1>Change Handle</h1>
    <?php if ($error) { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } elseif ($success) { ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>
    <form action="change_handle.php" method="post">
        <div id="handle"></div>
        <div>
            <input type="hidden" id="oldHandle" name="oldHandle" value="">
        </div>
        <div>
            <input type="hidden" id="username" name="username" value="">
        </div>
        <div>
            <label for="new_handle">New Handle:</label>
            <input type="text" id="new_handle" name="newHandle" required>
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Change Handle</button>
    </form>
    <script type="text/javascript">
        // Get username from local storage and set it in the form field
        var username = localStorage.getItem('username');
        document.getElementById('username').value = username;
        var handle = localStorage.getItem('cfUser');
        document.getElementById('oldHandle').value = handle;
        document.getElementById('handle').innerHTML = 'Current Handle: ' + handle;
    </script>
</body>
</html>
