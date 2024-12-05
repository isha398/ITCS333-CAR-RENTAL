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

    $stmt = $conn->prepare("
        SELECT b.*, r.roomtitle, r.roomimage
        FROM bookings b
        JOIN rooms r ON b.room_id = r.room_id
        WHERE b.user_id = :user_id
        ORDER BY b.check_in DESC
    ");
    
    $stmt->bindParam(':user_id', $_SESSION['user']['id']);
    $stmt->execute();
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

