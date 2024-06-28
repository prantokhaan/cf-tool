<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <link rel="stylesheet" href="../css/nav.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <?php
    // Display error messages based on GET parameters
    $error = '';
    if (isset($_GET['error']) && $_GET['error'] == 'username_not_found') {
        $error = 'Username not found. Please try again.';
    } else if (isset($_GET['error']) && $_GET['error'] == 'incorrect_password') {
        $error = 'Incorrect password. Please try again.';
    }
    ?>
    <div class="login-container">
        <div class="login-image">
            <img src="../images/loginImage.png" alt="Login Image">
        </div>
        <div class="login-form">
            <h2>Login</h2>
            <?php if (!empty($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="process_login.php" method="post">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
                <div class="additional-buttons">
                    <a href="#">Forgot Password?</a>
                    <a href="register.php">Not Registered? Register</a>
                </div>
            </form>
        </div>
    </div>

    <!-- <script src="../js/navbar.js"></script> -->
</body>
</html>
