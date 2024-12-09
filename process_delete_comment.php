<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    $_SESSION['error'] = "Please login first";
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['room_id'])) {
    $_SESSION['error'] = "Invalid request";
    header('Location: ' . BASE_URL);
    exit();
}

$commentId = (int)$_GET['id'];
$roomId = (int)$_GET['room_id'];

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        $_SESSION['errorcommnet'] = "Comment not found";
        header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
        exit();
    }

    if ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] === $comment['user_id']) {
        $stmt = $conn->prepare("DELETE FROM comments WHERE parent_id = ?");
        $stmt->execute([$commentId]);

        $stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);

        $_SESSION['successcomment'] = "Comment deleted successfully";
    } else {
        $_SESSION['errorcommnet'] = "You don't have permission to delete this comment";
    }

} catch(PDOException $e) {
    $_SESSION['errorcommnet'] = "Database error: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
exit();