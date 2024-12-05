?php 

include_once dirname(__DIR__) . '/config/config.php'; 

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../styles/main.css">
    <title> Car Booking | Login in</title>
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    <section class="signin-container">
        <div class="signin-box">
            <h2>Login</h2>
            <?php 
                if(isset($_SESSION['error'])) {
                    echo "<div class='error-message'>" . $_SESSION['error'] . "</div>";
                    unset($_SESSION['error']);
                }
                
                if(isset($_SESSION['success'])) {
                    echo "<div class='success-message'>" . $_SESSION['success'] . "</div>";
                    unset($_SESSION['success']);
                }
            ?>
            <form class="signin-form" action="process_signin.php" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="signin-btn">Sign In</button>
                <p class="signup-text">
                    Don't have an account? <a href="<?php echo BASE_URL ?>/signup">Sign Up</a>
                </p>
            </form>
        </div>
    </section>
</body>
</html>
