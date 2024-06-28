

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="../css/changePassword.css">
    <link rel="icon" href="../images/favicon.png">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <h1>Change Password</h1>
    <?php if ($error) { ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php } elseif ($success) { ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php } ?>
    <form action="process_change_password.php" method="post">
        <div>
            <input type="hidden" id="username" name="username" value="">
        </div>
        <div>
            <label for="old_password">Old Password:</label>
            <input type="password" id="old_password" name="oldPassword" required>
        </div>
        <div>
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="newPassword" required>
        </div>
        <div>
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" id="confirm_password" name="confirmNewPassword" required>
        </div>
        <button type="submit">Change Password</button>
    </form>
    <script type="text/javascript">
        // Get username from local storage and set it in the form field
        var username = localStorage.getItem('username');
        document.getElementById('username').value = username;
    </script>
</body>
</html>
