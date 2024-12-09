<?php

class Roomcard
{
    private int $roomId;
    private string $roomTitle;
    private string $roomImage;
    private float $roomPrice;
    private int $roomSeats;
    private int $roomProjectors;



    public function __construct($roomId, $roomTitle, $roomImage, $roomPrice, $roomSeats, $roomProjectors)
    {
        $this->roomId = $roomId;
        $this->roomTitle = $roomTitle;
        $this->roomImage = $roomImage;
        $this->roomPrice = $roomPrice;
        $this->roomSeats = $roomSeats;
        $this->roomProjectors = $roomProjectors;
    }




    public function display()
    {
        return ("
        <div class=\"room-card\">
            <div class=\"room-image\">
                <img src=\"" . $this->roomImage . "\" alt=\"Room Image\">
            </div>
            <div class=\"room-info\">
                <h3 class=\"room-title\">" . $this->roomTitle . "</h3>
                <div class=\"room-details\">
                    <span class=\"seats\"><i class=\"fas fa-users\"></i>" . $this->roomSeats . " Seats</span>
                    <span class=\"projectors\"><i class=\"fas fa-chalkboard-teacher\"></i>" . $this->roomProjectors . " Projector</span>
                </div>
                <div class=\"room-price\">
                    <span>$" . $this->roomPrice . "</span>/hour
                </div>
                <a href=\"roomview?id=" . $this->roomId . "\" class=\"book-btn\">Book Now</a>
            </div>
        </div>
        ");
    }


}

?>