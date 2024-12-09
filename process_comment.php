<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if(!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login to comment";
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = $_POST['room_id'];
    $userId = $_SESSION['user']['id'];
    $comment = trim($_POST['comment']);
    $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    // Validate input
    if(empty($comment)) {
        $_SESSION['error'] = "Comment cannot be empty";
        header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
        exit();
    }

    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        if($parentId !== null && $_SESSION['user']['role'] !== 'admin') {
            $_SESSION['error'] = "Only administrators can reply to comments";
            header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
            exit();
        }

        $stmt = $conn->prepare("
            INSERT INTO comments (room_id, user_id, comment, parent_id) 
            VALUES (:room_id, :user_id, :comment, :parent_id)
        ");

        $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
        $stmt->bindParam(':parent_id', $parentId, PDO::PARAM_INT);

        if($stmt->execute()) {
            $_SESSION['success'] = $parentId ? "Reply posted successfully!" : "Comment posted successfully!";
        } else {
            $_SESSION['error'] = "Failed to post comment";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
exit();