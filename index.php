
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles/main.css">
    <title>Room bookings</title>
</head>
<body>
    <?php include_once 'components/navbar.php'; ?>
    <?php include_once 'components/hero.php'; ?>
    <?php include_once 'components/browserrooms.php'; ?>


    <script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    const searchValue = this.value.toLowerCase();
    const rooms = document.getElementsByClassName('room-card');
    console.log('Found rooms:', rooms.length);

    Array.from(rooms).forEach(room => {
        try {
            const title = room.querySelector('.room-title')?.textContent?.toLowerCase() || '';
            const price = room.querySelector('.room-price')?.textContent?.toLowerCase() || '';
            const seats = room.querySelector('.room-details .seats')?.textContent?.toLowerCase() || '';
            
            console.log('Room data:', { title, price, seats });

            const isMatch = title.includes(searchValue) || 
                          price.includes(searchValue) || 
                          seats.includes(searchValue);

            room.style.display = isMatch ? 'block' : 'none';

        } catch (error) {
            console.error('Error processing room:', error);
        }
    });
});

</script>
</body>
</html>