<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../css/register.css">
    <link rel="stylesheet" href="../css/nav.css">
</head>
<body>
    <?php include '../shared/nav.php'; ?>
    <div class="register-container">
        <div class="register-image">
            <img src="../images/registerImage.png" alt="Register Image">
        </div>
        <div class="register-form">
            <h1>Register</h1>
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="process_register.php">
                <div class="form-group">
                    <label>Username: </label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email: </label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Codeforces Handle: </label>
                    <input type="text" name="codeforces_handle" required>
                </div>
                <div class="form-group">
                    <label>Password: </label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password: </label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
            <p>Already registered? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
