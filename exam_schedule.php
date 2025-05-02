<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get invigilator ID from session
if (isset($_SESSION['invigilator_id'])) {
    $invg_id = mysqli_real_escape_string($conn, $_SESSION['invigilator_id']);

    // Fetch the invigilator's assigned schedule using their ID
    $sql = "SELECT date, time, block, room FROM assigned_room_block WHERE id = '$invg_id'";
    $result = mysqli_query($conn, $sql);

} else {
    $result = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Schedule</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
        }
        .tab-menu {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 200px;
        }
        .tab-item {
            background: rgba(0, 0, 0, 1);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .tab-item:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-3px);
        }
        .tab-item a {
            color: #fff;
            text-decoration: none;
            font-size: 0.9rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .tab-item i {
            display: block;
            font-size: 2rem;
            margin-bottom: 0.8rem;
            text-align: center;
        }
        .dashboard {
            flex: 1;
            margin-left: 2rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
        }
        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }
        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #0e0e0e;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>
            <a href="invg_dashboard.php">Home</a>
            <a href="index.html">Logout</a>
        </div>
    </header>
    <div class="main-content">
        <div class="tab-menu">
            <div class="tab-item">
                <a href="exam_schedule.php">
                    <i>📅</i>
                    Exam Schedule
                </a>
            </div>
            <div class="tab-item">
                <a href="attendance.php">
                    <i>📋</i>
                    Attendance
                </a>
            </div>
            <div class="tab-item">
                <a href="invg_report_issues.html">
                    <i>📊</i>
                    Report Issues
                </a>
            </div>
        </div>
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Exam Schedule</h2>
            </div>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Block</th>
                    <th>Room</th>
                </tr>
                <?php
                if ($result && mysqli_num_rows($result) > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . date("d-m-Y", strtotime($row['date'])) . "</td>";

                        echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['block']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['room']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No schedule found</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
