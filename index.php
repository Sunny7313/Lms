<?php
session_start();
if (isset($_SESSION['email'])) {
    if (isset($_SESSION['previous_page']) && $_SESSION['previous_page'] !== $_SERVER['PHP_SELF']) {
        header("Location: " . $_SESSION['previous_page']);
        exit();
    } else {
        session_destroy();
    }
}
include "calculate_fines.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Library App</title>
    <link rel="stylesheet" href="./src/css/styles.css">
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2>Login</h2>
                <p>Welcome back! Please login to your account.</p>
            </div>
            <form action="authenticate.php" method="POST">
                <div class="form-group">
                    <label for="username">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn-primary">Login</button>
            </form>
        </div>
    </div>

    <!-- <script src="./src/js/script.js"></script> -->
</body>

</html>