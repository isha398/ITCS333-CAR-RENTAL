<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d', strtotime('+30 days'));
$userBookings = [];
$roomStats = [];
$timeSlots = [];

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $roomStatsStmt = $conn->prepare("
        SELECT 
            r.roomtitle, 
            r.room_id,
            COUNT(b.id) as total_bookings,
            COUNT(DISTINCT b.user_id) as unique_users,
            COALESCE(SUM(b.duration), 0) as total_hours
        FROM rooms r
        LEFT JOIN bookings b ON r.room_id = b.room_id 
        WHERE (:start_date IS NULL OR DATE(b.check_in) >= :start_date)
        AND (:end_date IS NULL OR DATE(b.check_in) <= :end_date)
        GROUP BY r.room_id, r.roomtitle
        ORDER BY total_bookings DESC, r.roomtitle ASC
    ");
    
    $roomStatsStmt->bindParam(':start_date', $startDate);
    $roomStatsStmt->bindParam(':end_date', $endDate);
    $roomStatsStmt->execute();
    $roomStats = $roomStatsStmt->fetchAll(PDO::FETCH_ASSOC);

    $availableSlots = [
        '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00'
    ];

    $slotStmt = $conn->prepare("
        SELECT 
            time_slot,
            COUNT(*) as slot_count
        FROM bookings
        GROUP BY time_slot
        ORDER BY CAST(time_slot AS TIME)
    ");
    $slotStmt->execute();
    $dbSlots = $slotStmt->fetchAll(PDO::FETCH_ASSOC);

    // Create time slots array with counts
    $slotCounts = array_column($dbSlots, 'slot_count', 'time_slot');
    $timeSlots = array_map(function($slot) use ($slotCounts) {
        return [
            'time_slot' => $slot,
            'slot_count' => $slotCounts[$slot] ?? 0
        ];
    }, $availableSlots);


        $bookingStmt = $conn->prepare("
            SELECT 
                b.*,
                r.roomtitle,
                DATE_FORMAT(b.check_in, '%Y-%m-%d %H:%i') as start_time,
                DATE_FORMAT(b.check_out, '%Y-%m-%d %H:%i') as end_time,
                b.duration
            FROM bookings b
            JOIN rooms r ON b.room_id = r.room_id
            WHERE b.user_id = :user_id
            ORDER BY b.check_in DESC
            LIMIT 10
        ");
        
        $userId = $_SESSION['user']['id'];
        $bookingStmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $bookingStmt->execute();
        $userBookings = $bookingStmt->fetchAll(PDO::FETCH_ASSOC);
    

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard</title>
    <link rel="stylesheet" href="../styles/main.css">
</head>

<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="analytics-container">
        <div class="filters">
            <form method="GET" class="date-filter">
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $startDate; ?>">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $endDate; ?>">
                </div>
                <button type="submit" class="filter-btn">Apply Filter</button>
            </form>
        </div>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
        <div class="stats-card">
            <h2>Room Usage Statistics</h2>
            <?php if (!empty($roomStats)): ?>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Room Name</th>
                            <th>Total Bookings</th>
                            <th>Unique Users</th>
                            <th>Total Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roomStats as $stat): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($stat['roomtitle']); ?></td>
                                <td><?php echo $stat['total_bookings']; ?></td>
                                <td><?php echo $stat['unique_users']; ?></td>
                                <td><?php echo $stat['total_hours'] ?? 0; ?> hours</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No room statistics available for the selected period.</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="stats-card">
            <h2>Popular Time Slots</h2>
            <?php if (!empty($timeSlots)):
                $maxBookings = max(array_column($timeSlots, 'slot_count')); ?>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Time Slot</th>
                            <th>Number of Bookings</th>
                            <th>Popularity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($timeSlots as $slot): ?>
                            <tr>
                                <td><?php echo $slot['time_slot']; ?></td>
                                <td><?php echo $slot['slot_count']; ?></td>
                                <td>
                                    <div class="progress-bar">
                                        <div class="progress" style="width: <?php
                                        echo ($slot['slot_count'] / $maxBookings) * 100;
                                        ?>%"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-data">No time slot data available for the selected period.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($userBookings)): ?>
            <div class="stats-card">
                <h2>Your Recent Bookings</h2>
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>Room</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Duration</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userBookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['roomtitle']); ?></td>
                                <td><?php echo $booking['start_time']; ?></td>
                                <td><?php echo $booking['end_time']; ?></td>
                                <td><?php echo $booking['duration']; ?> hours</td>
                                <td><?php echo strtotime($booking['check_out']) < time() ? 'Completed' : 'Upcoming'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>