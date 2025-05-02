
<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
            height: 100vh; /* Changed to height for fixed layout */
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
            overflow: hidden; /* Prevent scrolling on body */
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
            overflow: hidden; /* Prevent scrolling in main content */
        }

        .left-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 200px;
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
            overflow-y: auto; /* Enable scrolling in dashboard */
            max-height: calc(100vh - 140px); /* Adjust height for fixed layout */
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }
        .button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            font-size: 1.2rem;
            cursor: pointer;
            margin: 0 1rem;
            transition: background-color 0.3s, transform 0.2s;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .rooms-list {
            display: none; /* Initially hide the rooms list */
            gap: 1rem;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .block-section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .block-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #444;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007BFF;
        }

        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .room-item {
            background: #f8f8f8;
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid #eee;
            transition: transform 0.2s;
        }

        .room-item:hover {
            transform: scale(1.02);
        }

        .room-number {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

            .room-seats {
            color: #666;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>        
            <a href="exmcoord_home.php">Home</a>
            <a href="index.html">Logout</a>
        </div>
    </header>
    
    <div class="main-content">
        <div class="left-panel">
            <a href="view_seating.php" class="menu-item">
                <i>💺</i>
                Seating
            </a>
            <a href="exm_view_attendance.php" class="menu-item"> <!-- Added Attendance link -->
                <i>📋</i>
                Attendance
            </a>
            <a href="invg_req.php" class="menu-item">
                <i>👨‍🏫</i>
                Invigilator
            </a>
            <a href="exm_coord_view_report.php" class="menu-item">
                <i>📊</i>
                Report
            </a>
        </div>
        
        
        <div class="dashboard">
            <div class="button-container">
                <button class="button" onclick="showRooms()">View Rooms</button>
                <button class="button" onclick="window.location.href='timetable_scheduling.php'">Schedule Timetable</button>
                <button class="button" onclick="window.location.href='seating.html'">Seating Arrangement</button>
            </div>
            <div class="rooms-list" id="rooms-list">
                <?php
                if ($result->num_rows > 0) {
                    $current_block = '';
                    while($row = $result->fetch_assoc()) {
                        if ($current_block != $row['block']) {
                            if ($current_block != '') {
                                echo '</div></div>';
                            }
                            $current_block = $row['block'];
                            echo '<div class="block-section">
                                    <div class="block-header">' . htmlspecialchars($row['block']) . ' Block</div>

                                    <div class="room-grid">';
                        }
                        echo '<div class="room-item">
                                <div class="room-number">Room: ' . htmlspecialchars($row['room_no']) . '</div>
                                <div class="room-seats">Seats: ' . htmlspecialchars($row['seats']) . '</div>
                            </div>';
                    }
                    echo '</div></div>';
                } else {
                    echo "<p>No rooms available.</p>";
                }
                $conn->close();
                ?>
            </div>
        </div>
    </div>

    <script>
        function showRooms() {
            document.getElementById('rooms-list').style.display = 'block';
            // Add logic to hide seating details if implemented
        }
    </script>
</body>
</html>