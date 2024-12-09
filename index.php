<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user']['id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile Management</title>
    <link rel="stylesheet" href="../styles/main.css">
</head>

<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars(BASE_URL . '/' .
                (isset($user['image']) && !empty(trim($user['image']))
                    ? $user['image']
                    : 'assets/userimages/default-profile.jpg')); ?>" alt="Profile Picture" class="profile-picture"
                id="preview-image">
            <h1>Profile Management</h1>
        </div>

        <form class="profile-form" action="process_profile.php" method="POST" enctype="multipart/form-data">
            <?php if (isset($_SESSION['errorprofile'])): ?>
                <div class="error-message">
                    <?php echo $_SESSION['errorprofile'];
                    unset($_SESSION['errorprofile']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['successprofile'])): ?>
                <div class="success-message">
                    <?php echo $_SESSION['successprofile'];
                    unset($_SESSION['successprofile']); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="full-name">Full Name</label>
                <input type="text" id="full-name" name="full_name"
                    value="<?php echo htmlspecialchars($user['fullname'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                    value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                    value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
            </div>

            <div class="profile-picture-upload">
                <label for="profile-picture">Update Profile Picture</label>
                <input type="file" id="profile-picture" name="image" accept="image/*" onchange="previewImage(this);">
            </div>

            <?php if (isset($user['image']) && !empty(trim($user['image'])) && $user['image'] != 'assets/userimages/default-profile.jpg'): ?>
                <button type="button" class="delete-image-btn" onclick="confirmDeleteImage()">
                    Delete Profile Picture
                </button>
            <?php endif; ?>


            <button type="submit" class="btn-primary">Save Changes</button>
        </form>
    </div>

    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.getElementById('preview-image').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function confirmDeleteImage() {
            if (confirm('Are you sure you want to delete your profile picture?')) {
                window.location.href = 'process_delete_image.php';
            }
        }
    </script>
</body>

</html>