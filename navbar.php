<?php

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


include_once dirname(__DIR__) . '/config/config.php';


?>


<nav class="navbar">
    <div class="container">
        <a class="logo" href="<?php echo BASE_URL; ?>">
            <img src="<?php echo BASE_URL; ?>/assets/Logo.png" alt="Car Company Logo">
        </a>

        <button class="menu-toggle" type="button">
            <span class="menu-icon"></span>
        </button>

        <div class="nav-content">
            <ul class="nav-links">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>">Home</a>
                </li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>/analytics">Analytics</a>
                    </li>
                    <li class="nav-item">
                        <a href="<?php echo BASE_URL; ?>/bookings">My Bookings</a>
                    </li>
                    <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a href="<?php echo BASE_URL; ?>/admindashboard">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
        </div>
        <div class="cta-buttons">

            <?php

            if (isset($_SESSION['user'])) {
                // display user image and name
                echo '<div class="user-info">';
                echo '<span>' . $_SESSION['user']['username'] . '</span>';
                echo '<img src="' . BASE_URL . '/' .
                    (isset($_SESSION['user']['image']) && !empty(trim($_SESSION['user']['image']))
                        ? $_SESSION['user']['image']
                        : 'assets/userimages/default-profile.jpg') .
                    '" alt="User Profile Picture" class="user-image">';
                echo '</div>';
                echo '<a href="' . BASE_URL . '/profile" class="btn btn-solid">Profile</a>';
                echo '<a href="' . BASE_URL . '/logout" class="btn btn-outline">Logout</a>';
            } else {
                echo '<a href="' . BASE_URL . '/login" class="btn btn-outline">Sign In</a>';
                echo '<a href="' . BASE_URL . '/signup" class="btn btn-solid">Sign Up</a>';
            }
            ?>
        </div>
    </div>
</nav>