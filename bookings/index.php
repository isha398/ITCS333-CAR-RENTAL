<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
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

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="stylesheet" href="../styles/navbarstyle.css">
    <link rel="stylesheet" href="../styles/main.css">
    <link rel="stylesheet" href="../styles/bookingsstyle.css">
</head>

<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="bookings-container">
        <h1>My Bookings</h1>

        <?php if (!empty($bookings)): ?>
            <div class="booking-grid">
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-card">
                        <img src="<?php echo BASE_URL . '/' . $booking['roomimage']; ?>"
                            alt="<?php echo htmlspecialchars($booking['roomtitle']); ?>" class="booking-image">
                        <div class="booking-details">
                            <h2 class="booking-title"><?php echo htmlspecialchars($booking['roomtitle']); ?></h2>
                            <div class="booking-info">
                                <p>Check In: <?php echo date('F j, Y g:i A', strtotime($booking['check_in'])); ?></p>
                                <p>Check Out: <?php echo date('F j, Y g:i A', strtotime($booking['check_out'])); ?></p>
                                <p>Duration: <?php echo $booking['duration']; ?> hour(s)</p>
                                <p>
                                    <span class="status-badge <?php
                                    $now = time();
                                    $checkIn = strtotime($booking['check_in']);
                                    $checkOut = strtotime($booking['check_out']);

                                    if ($now < $checkIn) {
                                        echo 'status-upcoming';
                                        $status = 'Upcoming';
                                    } elseif ($now > $checkOut) {
                                        echo 'status-completed';
                                        $status = 'Completed';
                                    } else {
                                        echo 'status-active';
                                        $status = 'Active';
                                    }
                                    ?>">
                                        <?php echo $status; ?>
                                    </span>
                                </p>
                            </div>
                        </div>

                        <?php if ($status === 'Upcoming'): ?>
                            <form action="process_delete_booking.php" method="POST" class="mt-3 mb-3"
                                onsubmit="return confirm('you sure you want to cancel this booking?');">
                                <input type="hidden" name="booking_id"
                                    value="<?php echo htmlspecialchars($booking['id'], ENT_QUOTES, 'UTF-8'); ?>">
                                <button type="submit"
                                    class="btn btn-solid">
                                    Cancel Booking
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="no-bookings">You don't have any bookings yet.</p>
        <?php endif; ?>
    </div>
</body>

</html>