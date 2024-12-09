<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

// Check admin access
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if(!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid room ID";
    header('Location: ' . BASE_URL . '/admindashboard');
    exit();
}

$roomId = (int)$_GET['id'];

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $imageStmt = $conn->prepare("SELECT roomimage FROM rooms WHERE room_id = :id");
    $imageStmt->bindParam(':id', $roomId, PDO::PARAM_INT);
    $imageStmt->execute();
    $imagePath = $imageStmt->fetchColumn();


    $deleteStmt = $conn->prepare("DELETE FROM rooms WHERE room_id = :id");
    $deleteStmt->bindParam(':id', $roomId, PDO::PARAM_INT);

    if($deleteStmt->execute()) {
        if($imagePath && $imagePath !== 'assets/roomimages/default-room.jpg') {
            $fullPath = dirname(__DIR__) . '/' . $imagePath;
            if(file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        $_SESSION['success'] = "Room deleted successfully";
    } else {
        $_SESSION['error'] = "Failed to delete room";
    }

} catch(PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/admindashboard');
exit();