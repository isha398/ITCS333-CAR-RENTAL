<section class="room-browser">
    <div class="search-section">
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search search-icon"></i>
                <input type="text" id="searchInput" placeholder="Search rooms...">
            </div>
        </div>
    </div>

    <div class="rooms-grid" id="roomsGrid">
        <?php 
        include_once dirname(__DIR__) . '/config/config.php';
        include_once dirname(__DIR__) . '/components/roomcard.php';
        
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            $stmt = $conn->prepare("SELECT * FROM rooms");
            $stmt->execute();

            while ($room = $stmt->fetch()) {
                $roomCard = new Roomcard($room['room_id'], $room['roomtitle'], $room['roomimage'], $room['roomprice'], $room['roomseats'], $room['roomprojectors']);
                echo $roomCard->display();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error: " . $e->getMessage();
        }
        ?>
    </div>
</section>