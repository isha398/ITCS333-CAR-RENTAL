<?php 
if(session_status() == PHP_SESSION_NONE) {
    session_start();
}
include_once dirname(__DIR__) . '/config/config.php';
$fullname = $_POST['fullname'];
$email = $_POST['email'];
$username = $_POST['username'];
$password = $_POST['password'];
$repassword = $_POST['repassword'];

if ($password != $repassword) {
    $_SESSION['error'] = "Passwords do not match";
    header("Location: " . BASE_URL . "/signup");
    exit();
}

if(!preg_match("/@stu\.uob\.edu\.bh$/", $email)) {
    $_SESSION['error'] = "Email must be a valid @stu.uob.edu.bh address";
    header("Location: " . BASE_URL . "/signup");
    exit();
}

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email OR username = :username");
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $count = $stmt->fetchColumn();


    if ($count > 0) {
        $_SESSION['error'] = "Email or Username already exists";
        header("Location: " . BASE_URL . "/signup");
        exit();
    }

    $hashed_password = hash('sha256', $password);

    $stmt = $conn->prepare("INSERT INTO users (fullname, email, username, password) VALUES (:fullname, :email, :username, :password)");
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $hashed_password);

    $_SESSION['success'] = "Account created successfully. Please log in.";
    $stmt->execute();

    header("Location: " . BASE_URL . "/signup");
    exit();

} catch (PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: " . BASE_URL . "/signup");
    exit();

}
?>