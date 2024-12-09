<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if(!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $conn->prepare("SELECT image FROM users WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user']['id']);
    $stmt->execute();
    $currentImage = $stmt->fetchColumn();

    if($currentImage && strpos($currentImage, 'default-profile.jpg') === false) {
        $imagePath = dirname(__DIR__) . '/' . $currentImage;
        if(file_exists($imagePath) && basename($imagePath) !== 'default-profile.jpg') {
            unlink($imagePath);
        }
        
        $updateStmt = $conn->prepare("UPDATE users SET image = :default_image WHERE id = :id");
        $defaultImage = 'assets/userimages/default-profile.jpg';
        $updateStmt->bindParam(':default_image', $defaultImage);
        $updateStmt->bindParam(':id', $_SESSION['user']['id']);
        
        if($updateStmt->execute()) {
            $_SESSION['user']['image'] = $defaultImage;
            $_SESSION['success'] = "Profile picture deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to update profile picture";
        }
    }

} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/profile');
exit();