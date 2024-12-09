<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    $_SESSION['errorbookingp'] = "Please login to book a room";
    header('Location: ' . BASE_URL . '/login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $roomId = $_POST['room_id'];
    $userId = $_SESSION['user']['id'];
    $bookingDate = $_POST['booking_date'];
    $timeSlot = $_POST['time_slot'];

    $currentDate = date('Y-m-d H:i:s', strtotime('now'));
    $checkIn = date('Y-m-d H:i:s', strtotime("$bookingDate $timeSlot"));
    $checkOut = date('Y-m-d H:i:s', strtotime("$checkIn $timeSlot"));


    if ($checkIn < $currentDate) {
        $_SESSION['errorbookingp'] = "Cannot book for past dates";
        header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
        exit();
    }


    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $checkUserBookings = $conn->prepare("
            SELECT COUNT(*) as booking_count 
            FROM bookings 
            WHERE user_id = :user_id 
            AND check_out >= NOW()
        ");

        $checkUserBookings->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $checkUserBookings->execute();

        $userBookingCount = $checkUserBookings->fetch(PDO::FETCH_ASSOC)['booking_count'];

        if ($userBookingCount >= 2) {
            $_SESSION['errorbookingp'] = "You can only have 2 active bookings at a time";
            header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
            exit();
        }


        $checkSlot = $conn->prepare("
            SELECT id FROM bookings 
            WHERE room_id = :room_id 
            AND time_slot = :time_slot
            AND DATE(check_in) = :booking_date
        ");

        $checkSlot->bindParam(':room_id', $roomId, PDO::PARAM_INT);
        $checkSlot->bindParam(':time_slot', $timeSlot);
        $checkSlot->bindParam(':booking_date', $bookingDate);
        $checkSlot->execute();

        if ($checkSlot->rowCount() > 0) {
            $_SESSION['errorbookingp'] = "This time slot is already booked";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO bookings (room_id, user_id, check_in, check_out, time_slot, duration) 
                VALUES (:room_id, :user_id, :check_in, :check_out, :time_slot, :duration)
            ");

            $stmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':check_in', $checkIn);
            $stmt->bindParam(':check_out', $checkOut);
            $stmt->bindParam(':time_slot', $timeSlot);
            $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['successbooking'] = "Booking successful! From " .
                    date('F j, Y g:i A', strtotime($checkIn)) .
                    " to " . date('g:i A', strtotime($checkOut));
            } else {
                $_SESSION['errorbookingp'] = "Booking failed";
            }
        }
    } catch (PDOException $e) {
        $_SESSION['errorbookingp'] = "Database error: " . $e->getMessage();
    }
}

header('Location: ' . BASE_URL . '/roomview?id=' . $roomId);
exit();