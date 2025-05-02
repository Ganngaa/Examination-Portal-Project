<?php
session_start();
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if required fields are set
if (isset($_POST['block'], $_POST['room_no'], $_POST['seats'])) {
    $block = $_POST['block'];
    $room_numbers = $_POST['room_no']; // Array of room numbers
    $seats_array = $_POST['seats'];    // Array of seat counts

    // Ensure arrays are valid
    if (!empty($room_numbers) && is_array($room_numbers) && !empty($seats_array) && is_array($seats_array)) {
        $stmt = $conn->prepare("INSERT INTO rooms (room_no, seats, block) VALUES (?, ?, ?)");

        for ($i = 0; $i < count($room_numbers); $i++) {
            $room_no = $room_numbers[$i];
            $seats = $seats_array[$i];

            // Ensure values are valid before inserting
            if (!empty($room_no) && !empty($seats) && !empty($block)) {
                $stmt->bind_param("sis", $room_no, $seats, $block);
                $stmt->execute();
            } else {
                echo "Error: Missing values for room_no, seats, or block.";
            }
        }

        $stmt->close();
        header("Location: dept_seat.html");
        exit();
    } else {
        echo "Error: No valid room numbers or seats provided.";
    }
} else {
    echo "Error: Required fields are missing.";
}

$conn->close();
?>
