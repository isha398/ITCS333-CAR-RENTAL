<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

// Admin access check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ' . BASE_URL . '/login');
    exit();
}

// Fetch rooms
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $roomStmt = $conn->query("SELECT * FROM rooms ORDER BY room_id DESC");
    $rooms = $roomStmt->fetchAll(PDO::FETCH_ASSOC);

    $commentStmt = $conn->prepare("
        SELECT c.*, u.username, r.roomtitle 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        JOIN rooms r ON c.room_id = r.room_id 
        ORDER BY c.created_at DESC 
        LIMIT 5
    ");
    $commentStmt->execute();
    $recentComments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../styles/main.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="admin-container">
        <div class="admin-header">
            <h1>Admin Dashboard</h1>
            <button onclick="showAddRoomForm()" class="add-room-btn">Add New Room</button>
        </div>

        <div id="roomForm" class="modal hidden">
            <div class="modal-content">
                <form action="process_room.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="room_id" id="roomId">
                    <div class="form-group">
                        <label>Room Title</label>
                        <input type="text" name="roomtitle" required>
                    </div>
                    <div class="form-group">
                        <label>Price per Hour</label>
                        <input type="number" name="roomprice" required>
                    </div>
                    <div class="form-group">
                        <label>Seats</label>
                        <input type="number" name="roomseats" required>
                    </div>
                    <div class="form-group">
                        <label>Projectors</label>
                        <input type="number" name="roomprojectors" required>
                    </div>
                    <div class="form-group">
                        <label>Room Image</label>
                        <input type="file" name="roomimage" accept="image/*">
                    </div>
                    <div class="form-actions">
                        <button type="submit">Save Room</button>
                        <button type="button" onclick="hideRoomForm()">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="admin-grid">
            <div class="rooms-section">
                <h2>Room Management</h2>
                <div class="rooms-list">
                    <?php foreach($rooms as $room): ?>
                        <div class="room-card">
                            <img src="<?php echo BASE_URL . '/' . $room['roomimage']; ?>" alt="Room image">
                            <div class="room-info">
                                <h3><?php echo htmlspecialchars($room['roomtitle']); ?></h3>
                                <p>Price: $<?php echo htmlspecialchars($room['roomprice']); ?>/hour</p>
                                <p>Seats: <?php echo htmlspecialchars($room['roomseats']); ?></p>
                                <p>Projectors: <?php echo htmlspecialchars($room['roomprojectors']); ?></p>
                            </div>
                            <div class="room-actions">
                                <button onclick="editRoom(<?php echo htmlspecialchars(json_encode($room)); ?>)" 
                                        class="edit-btn">Edit</button>
                                <button onclick="deleteRoom(<?php echo $room['room_id']; ?>)" 
                                        class="delete-btn">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="comments-section">
                <h2>Recent Comments</h2>
                <div class="comments-list">
                    <?php foreach($recentComments as $comment): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                                <span>on <?php echo htmlspecialchars($comment['roomtitle']); ?></span>
                            </div>
                            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <small><?php echo date('F j, Y g:i a', strtotime($comment['created_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        function showAddRoomForm() {
            document.getElementById('roomId').value = '';
            document.getElementById('roomForm').classList.remove('hidden');
        }

        function hideRoomForm() {
            document.getElementById('roomForm').classList.add('hidden');
        }

        function editRoom(room) {
            const form = document.getElementById('roomForm');
            form.classList.remove('hidden');
            
            // Populate form
            document.getElementById('roomId').value = room.room_id;
            form.querySelector('[name="roomtitle"]').value = room.roomtitle;
            form.querySelector('[name="roomprice"]').value = room.roomprice;
            form.querySelector('[name="roomseats"]').value = room.roomseats;
            form.querySelector('[name="roomprojectors"]').value = room.roomprojectors;
        }

        function deleteRoom(roomId) {
            if(confirm('Are you sure you want to delete this room?')) {
                window.location.href = `process_delete_room.php?id=${roomId}`;
            }
        }
    </script>
</body>
</html>