<?php 
include_once dirname(__DIR__) . '/config/config.php'; 
// Start Session

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
    <title>Car Booking | Signup</title>
</head>
 
<body>
    <?php include_once '../components/navbar.php'; ?>
    <section class="signup-container">


        
        <div class="signup-box">
            <h2>Sign Up</h2>
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
            <form class="signup-form" action="process_signup.php" method="POST">
                <div class="form-group">
                    <label for="fullname">Full Name</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div>
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="repassword">Confirm Password</label>
                    <input type="password" id="repassword" name="repassword" required>
                </div>
                <button type="submit" class="signup-btn">Sign Up</button>
                <p class="signup-text">
                    Already have an account? <a href="<?php echo BASE_URL ?>/login">Log In</a>
                </p>
            </form>
        </div>
    </section>
</body>

</html>