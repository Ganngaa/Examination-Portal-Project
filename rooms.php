<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete request
if(isset($_POST['delete_room'])) {
    $room_no = $_POST['room_no'];
    $delete_sql = "DELETE FROM rooms WHERE room_no = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("s", $room_no);
    $stmt->execute();
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// Handle update seats request
if(isset($_POST['update_seats'])) {
    $room_no = $_POST['room_no'];
    $new_seats = $_POST['new_seats'];
    $update_sql = "UPDATE rooms SET seats = ? WHERE room_no = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("is", $new_seats, $room_no);
    $stmt->execute();
    $stmt->close();
    header("Location: rooms.php");
    exit();
}

// Query to get rooms grouped by block
$sql = "SELECT * FROM rooms ORDER BY block, room_no";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room and Seating Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.05);
            z-index: -1;
        }

        header {
            padding: 1.5rem 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .portal-name {
            color: #fff;
            font-size: 1.8rem;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        header a {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        header a:hover {
            background-color: rgba(0, 0, 0, 0.4);
        }

        .main-content {
            display: flex;
            flex: 1;
            padding: 2rem;
            overflow: hidden;
        }

        .left-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 200px;
            height: 100%;
        }

        .menu-item {
            background: rgba(0, 0, 0, 1);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
        }

        .menu-item:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-3px);
        }

        .menu-item i {
            font-size: 2rem;
            margin-bottom: 0.8rem;
        }

        .dashboard {
            flex: 1;
            margin-left: 2rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            top: 0;
            z-index: 1;
        }

        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }

        .button-container {
            margin-bottom: 2rem;
            position: sticky;
            top: 80px;
            background: rgba(255, 255, 255, 0.9);
            z-index: 1;
            padding: 1rem 0;
        }

        .button {
            padding: 0.8rem 1.5rem;
            font-size: 1rem;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 1rem;
            transition: background 0.3s, transform 0.2s;
        }

        .button:hover {
            background: #444;
            transform: scale(1.05);
        }

        .rooms-list {
            display: none;
            margin-top: 1rem;
            gap: 1rem;
            display: flex;
            flex-direction: column;
        }

        .room-item {
            padding: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            background: #f9f9f9;
            transition: transform 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        .room-item:hover {
            transform: scale(1.02);
        }

        .room-number {
            font-weight: bold;
            color: #333;
            font-size: 1.2rem;
        }

        .room-seats {
            color: #666;
            font-size: 1rem;
        }

        .block-section {
            margin-bottom: 1.5rem;
            padding: 1.5rem;
            border: 1px;
            border-radius: 8px;
            background:rgb(255, 255, 255);
        }

        .block-header {
            font-size: 1.5rem;
            font-weight: bold;
            color:rgb(0, 0, 0);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px;
        }

        .room-grid {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }
    
    </style>
</head>
<body>

    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>        
            <a href="dept_co.php">Home</a>
            <a href="index.html">Logout</a>
        </div>
    </header>
    
    <div class="main-content">
        <div class="left-panel">
            <a href="rooms.php" class="menu-item">
                <i>💺</i>
                Rooms
            </a>
            <a href="dept_view_attendance.php" class="menu-item">
                <i>📋</i>
                Attendance
            </a>
            <a href="view_all.php" class="menu-item">
                <i>👨‍🏫</i>
                Invigilators
            </a>
        </div>
        
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Rooms</h2>
            </div>
                <button class="button" onclick="window.location.href='dept_seat.html'">Add Rooms</button>
            <div class="rooms-list" id="rooms-list">
                <?php
                if ($result->num_rows > 0) {
                    $current_block = '';
                    while($row = $result->fetch_assoc()) {
                        if (!empty($row['room_no']) && !empty($row['seats'])) {
                            if ($current_block != $row['block']) {
                                if ($current_block != '') {
                                    echo '</div></div>';
                                }
                                $current_block = $row['block'];
                                echo '<div class="block-section"><div class="block-header">' . htmlspecialchars($row['block']) . ' Block</div><div class="room-grid">';
                            }
                            echo '<div class="room-item">
                                    <div>
                                        <div class="room-number">Room: ' . htmlspecialchars($row['room_no']) . '</div>
                                        <div class="room-seats">Seats: ' . htmlspecialchars($row['seats']) . '</div>
                                    </div>
                                    <div>
                                        <button class="button" onclick="updateSeats(' . htmlspecialchars($row['room_no']) . ', ' . htmlspecialchars($row['seats']) . ')">Edit</button>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="room_no" value="' . htmlspecialchars($row['room_no']) . '">
                                            <button type="submit" name="delete_room" class="button" onclick="return confirm(\'Are you sure you want to delete room ' . htmlspecialchars($row['room_no']) . '?\')">Delete</button>
                                        </form>
                                    </div>
                                </div>';
                        }
                    }
                    if ($current_block != '') {
                        echo '</div></div>';
                    }
                } else {
                    echo "<p>No rooms available.</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function showRooms() {
            document.getElementById('rooms-list').style.display = 'flex';
        }

        function updateSeats(roomNo, currentSeats) {
            const newSeats = prompt("Enter new number of seats:", currentSeats);
            if (newSeats !== null && !isNaN(newSeats)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="room_no" value="${roomNo}">
                    <input type="hidden" name="new_seats" value="${newSeats}">
                    <input type="hidden" name="update_seats" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>