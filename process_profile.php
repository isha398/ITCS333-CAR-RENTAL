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

    $userId = $_SESSION['user']['id'];
    $fullname = $_POST['full_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];

    if(!preg_match("/@stu\.uob\.edu\.bh$/", $email)) {
        $_SESSION['errorprofile'] = "Email must be a valid @stu.uob.edu.bh address";
        header("Location: " . BASE_URL . "/signup");
        exit();
    }


    if(isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__) . '/assets/userimages/';
        if(!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $file = $_FILES['image'];
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
        $dbImagePath = 'assets/userimages/' . $fileName;

        if(move_uploaded_file($file['tmp_name'], $targetPath)) {
            $stmt = $conn->prepare("UPDATE users SET image = :image WHERE id = :id");
            $stmt->bindParam(':image', $dbImagePath);
            $stmt->bindParam(':id', $userId);
            
            if($stmt->execute()) {
                $_SESSION['user']['image'] = $dbImagePath;
            }
        }
    }

    $stmt = $conn->prepare("UPDATE users SET fullname = :fullname, username = :username, email = :email WHERE id = :id");
    
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $userId);

    if($stmt->execute()) {
        $_SESSION['user']['fullname'] = $fullname;
        $_SESSION['user']['username'] = $username;
        $_SESSION['user']['email'] = $email;
        $_SESSION['successprofile'] = "Profile updated successfully";
    } else {
        $_SESSION['errorprofile'] = "Error updating profile";
    }

} catch(PDOException $e) {
    $_SESSION['errorprofile'] = "Database error: " . $e->getMessage();
}

header('Location: ' . BASE_URL . '/profile');
exit();