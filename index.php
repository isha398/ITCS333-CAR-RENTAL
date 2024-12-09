<?php
include_once dirname(__DIR__) . '/config/config.php';
session_start();

$roomId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get room details
    $stmt = $conn->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    // Get comments
    $commentStmt = $conn->prepare("
        SELECT c.*, u.username, u.image as user_image 
        FROM comments c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.room_id = ? 
        ORDER BY c.created_at DESC
    ");
    $commentStmt->execute([$roomId]);
    $comments = $commentStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room View - <?php echo htmlspecialchars($room['roomtitle']); ?></title>
    <link rel="stylesheet" href="../styles/main.css">
</head>

<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="room-container">
        <div class="room-details">
            <div class="room-info">
                <img src="<?php echo BASE_URL . '/' . htmlspecialchars($room['roomimage']); ?>"
                    alt="<?php echo htmlspecialchars($room['roomtitle']); ?>" class="room-image">

                <div class="room-specs">
                    <h1><?php echo htmlspecialchars($room['roomtitle']); ?></h1>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Price:</span>
                            <span class="spec-value">$<?php echo htmlspecialchars($room['roomprice']); ?>/hour</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Seats:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($room['roomseats']); ?></span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Projectors:</span>
                            <span class="spec-value"><?php echo htmlspecialchars($room['roomprojectors']); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="booking-section">
                <h2>Book This Room</h2>
                <?php if (isset($_SESSION['user'])): ?>
                    <form action="process_booking.php" method="POST">
                        <?php
                            if (isset($_SESSION['errorbookingp'])) {
                                echo "<div class='error-message'>" . $_SESSION['errorbookingp'] . "</div>";
                                unset($_SESSION['errorbookingp']);
                            }

                            if (isset($_SESSION['successbooking'])) {
                                echo "<div class='success-message'>" . $_SESSION['successbooking'] . "</div>";
                                unset($_SESSION['successbooking']);
                            }
                        ?>
                        <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" name="booking_date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label>Time Slot</label>
                            <select name="time_slot" required>
                                <option value="">Select time</option>
                                <option value="09:00">09:00 - 10:00</option>
                                <option value="10:00">10:00 - 11:00</option>
                                <option value="11:00">11:00 - 12:00</option>
                                <option value="12:00">12:00 - 13:00</option>
                                <option value="13:00">13:00 - 14:00</option>
                                <option value="14:00">14:00 - 15:00</option>
                                <option value="15:00">15:00 - 16:00</option>
                                <option value="16:00">16:00 - 17:00</option>
                            </select>
                        </div>
                        <button type="submit" class="book-button">Book Now</button>
                    </form>
                <?php else: ?>
                    <p class="login-prompt">Please <a href="<?php echo BASE_URL; ?>/login">login</a> to book this room.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="comments-section">
            <h2>Comments</h2>
            <?php if (isset($_SESSION['user'])): ?>
                <form action="process_comment.php" method="POST" class="comment-form">
                    <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                    <textarea name="comment" required placeholder="Write your comment..."></textarea>
                    <button type="submit" class="comment-button">Post Comment</button>
                </form>
            <?php endif; ?>

            <div class="comments-list">
                <?php
                $parentComments = array_filter($comments, function ($comment) {
                    return $comment['parent_id'] === null;
                });

                foreach ($parentComments as $comment):
                    $replies = array_filter($comments, function ($reply) use ($comment) {
                        return $reply['parent_id'] === $comment['id'];
                    });
                    ?>
                    <div class="comment">
                        <img src="<?php echo BASE_URL . '/' .
                            (isset($comment['user_image']) && !empty(trim($comment['user_image']))
                                ? $comment['user_image']
                                : 'assets/userimages/default-profile.jpg'); ?>" alt="User profile">
                        <div class="comment-content">

                            <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] === $comment['user_id'])): ?>
                                <button class="delete-btn"
                                    onclick="confirmDelete(<?php echo $comment['id']; ?>)">Delete</button>
                            <?php endif; ?>


                            <strong><?php echo htmlspecialchars($comment['username']); ?></strong>
                            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <small><?php echo date('F j, Y', strtotime($comment['created_at'])); ?></small>

                            <?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin'): ?>
                                <button class="reply-toggle"
                                    onclick="toggleReplyForm(<?php echo $comment['id']; ?>)">Reply</button>
                                <form id="replyForm-<?php echo $comment['id']; ?>" action="process_comment.php" method="POST"
                                    class="reply-form hidden">
                                    <input type="hidden" name="parent_id" value="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="room_id" value="<?php echo $roomId; ?>">
                                    <textarea name="comment" required placeholder="Write a reply..."></textarea>
                                    <button type="submit">Post Reply</button>
                                </form>
                            <?php endif; ?>

                            <?php foreach ($replies as $reply): ?>
                                <div class="reply">

                                    <?php if (isset($_SESSION['user']) && ($_SESSION['user']['role'] === 'admin' || $_SESSION['user']['id'] === $reply['user_id'])): ?>
                                        <button class="delete-btn"
                                            onclick="confirmDelete(<?php echo $reply['id']; ?>)">Delete</button>
                                    <?php endif; ?>


                                    <div class="reply-context">
                                        Replying to <span><?php echo htmlspecialchars($comment['username']); ?></span>:
                                        "<?php echo htmlspecialchars(substr($comment['comment'], 0, 60)) . (strlen($comment['comment']) > 60 ? '...' : ''); ?>"
                                    </div>
                                    <img src="<?php echo BASE_URL . '/' .
                                        (isset($reply['user_image']) && !empty(trim($reply['user_image']))
                                            ? $reply['user_image']
                                            : 'assets/userimages/default-profile.jpg'); ?>" alt="Admin profile">
                                    <div class="reply-content">
                                        <strong><?php echo htmlspecialchars($reply['username']); ?> (Admin)</strong>
                                        <p><?php echo htmlspecialchars($reply['comment']); ?></p>
                                        <small><?php echo date('F j, Y', strtotime($reply['created_at'])); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleReplyForm(commentId) {
            const form = document.getElementById(`replyForm-${commentId}`);
            form.classList.toggle('hidden');
        }

        function confirmDelete(commentId) {
            if (confirm('Are you sure you want to delete this comment?')) {
                window.location.href = `process_delete_comment.php?id=${commentId}&room_id=<?php echo $roomId; ?>`;
            }
        }
    </script>
</body>

</html>