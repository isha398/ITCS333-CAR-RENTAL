<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access";
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = isset($_POST['room_id']) ? (int)$_POST['room_id'] : null;
    $roomTitle = trim($_POST['roomtitle']);
    $roomPrice = (float)$_POST['roomprice'];
    $roomSeats = (int)$_POST['roomseats'];
    $roomProjectors = (int)$_POST['roomprojectors'];

    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $imagePath = null;
        if(isset($_FILES['roomimage']) && $_FILES['roomimage']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__) . '/assets/roomimages/';
            if(!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = uniqid() . '_' . basename($_FILES['roomimage']['name']);
            $targetPath = $uploadDir . $fileName;
            $dbImagePath = 'assets/roomimages/' . $fileName;

            // Delete old image if updating
            if($roomId) {
                $oldImageStmt = $conn->prepare("SELECT roomimage FROM rooms WHERE room_id = :id");
                $oldImageStmt->bindParam(':id', $roomId, PDO::PARAM_INT);
                $oldImageStmt->execute();
                $oldImage = $oldImageStmt->fetchColumn();
                
                if($oldImage && file_exists(dirname(__DIR__) . '/' . $oldImage)) {
                    unlink(dirname(__DIR__) . '/' . $oldImage);
                }
            }

            if(move_uploaded_file($_FILES['roomimage']['tmp_name'], $targetPath)) {
                $imagePath = $dbImagePath;
            }
        }

        if($roomId) {
            $stmt = $conn->prepare("UPDATE rooms SET 
                roomtitle = :title,
                roomprice = :price,
                roomseats = :seats,
                roomprojectors = :projectors" . 
                ($imagePath ? ", roomimage = :image" : "") . 
                " WHERE room_id = :id"
            );

            $stmt->bindParam(':title', $roomTitle, PDO::PARAM_STR);
            $stmt->bindParam(':price', $roomPrice, PDO::PARAM_STR);
            $stmt->bindParam(':seats', $roomSeats, PDO::PARAM_INT);
            $stmt->bindParam(':projectors', $roomProjectors, PDO::PARAM_INT);
            $stmt->bindParam(':id', $roomId, PDO::PARAM_INT);
            
            if($imagePath) {
                $stmt->bindParam(':image', $imagePath, PDO::PARAM_STR);
            }
        } else {
            // Add new room
            $stmt = $conn->prepare("INSERT INTO rooms 
                (roomtitle, roomprice, roomseats, roomprojectors, roomimage) 
                VALUES (:title, :price, :seats, :projectors, :image)"
            );
            
            $stmt->bindParam(':title', $roomTitle, PDO::PARAM_STR);
            $stmt->bindParam(':price', $roomPrice, PDO::PARAM_STR);
            $stmt->bindParam(':seats', $roomSeats, PDO::PARAM_INT);
            $stmt->bindParam(':projectors', $roomProjectors, PDO::PARAM_INT);
            $defaultImage = $imagePath ?? 'assets/roomimages/default-room.jpg';
            $stmt->bindParam(':image', $defaultImage, PDO::PARAM_STR);
        }

        if($stmt->execute()) {
            $_SESSION['success'] = $roomId ? "Room updated successfully" : "Room added successfully";
        } else {
            $_SESSION['error'] = "Failed to " . ($roomId ? "update" : "add") . " room";
        }

    } catch(PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

header('Location: ' . BASE_URL . '/admindashboard');
exit();
