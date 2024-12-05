<?php

if(session_status() == PHP_SESSION_NONE) {
    session_start();
}


include_once dirname(__DIR__) . '/config/config.php';

$email = $_POST['email'];
$password = $_POST['password'];


try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: " . BASE_URL . "/login");
        exit();
    }

    $hashed_password = hash('sha256', $password);

    if ($hashed_password != $user['password']) {
        $_SESSION['error'] = "Invalid email or password";
        header("Location: " . BASE_URL . "/login");
        exit();
    }

    $_SESSION['user'] = $user;
    header("Location: " . BASE_URL . "/");

} catch (PDOException $e) {

    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: " . BASE_URL . "/login");
    exit();

}


?>