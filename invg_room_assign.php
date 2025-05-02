<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['assign_invigilators'])) {
    $exam_date = $_POST['exam_date'] ?? '';
    if (empty($exam_date)) {
        die("Error: Exam date is missing.");
    }

    // Clear previous assignments
    $conn->query("TRUNCATE TABLE assigned_room_block");

    // Fetch department requirements
    $dept_requirements = [];
    $sql_req = "SELECT dept, count FROM invg_req WHERE date = '$exam_date'";
    $result_req = $conn->query($sql_req);
    if ($result_req->num_rows == 0) {
        die("No department requirements found for this date.");
    }
    while ($row = $result_req->fetch_assoc()) {
        $dept_requirements[$row['dept']] = $row['count'];
    }

    // Fetch available faculty
    $faculty_list = [];
    $sql_faculty = "SELECT id, name, dept, time FROM assigned_fac WHERE block = '0' AND room = '0' AND date = '$exam_date'";
    $result_faculty = $conn->query($sql_faculty);
    if ($result_faculty->num_rows == 0) {
        die("No available faculty for this date.");
    }
    while ($row = $result_faculty->fetch_assoc()) {
        $faculty_list[$row['dept']][] = $row;
    }

    // Fetch room details
    $room_assignments = [];
    $sql_rooms = "SELECT block, room, count, time FROM invg_room WHERE date = '$exam_date'";
    $result_rooms = $conn->query($sql_rooms);
    if ($result_rooms->num_rows == 0) {
        die("No rooms available for this date.");
    }
    while ($room = $result_rooms->fetch_assoc()) {
        $block = $room['block'];
        $room_no = $room['room'];
        $room_capacity = $room['count'];
        $room_time = $room['time'];
        $room_assignments["$block-$room_no"] = 0;

        while ($room_assignments["$block-$room_no"] < $room_capacity) {
            $facultyAssigned = false;
            foreach ($dept_requirements as $dept => $required_count) {
                if ($required_count > 0 && !empty($faculty_list[$dept])) {
                    $faculty = array_shift($faculty_list[$dept]);

                    // Fetch correct faculty ID from credentials table
                    $get_faculty_id = $conn->prepare("SELECT id FROM credentials WHERE name LIKE ?");
                    $name_param = "%{$faculty['name']}%"; // Use LIKE for better matching
                    $get_faculty_id->bind_param("s", $name_param);
                    $get_faculty_id->execute();
                    $get_faculty_id->bind_result($faculty_id);
                    $get_faculty_id->fetch();
                    $get_faculty_id->close();

                    if (!$faculty_id) {
                        die("Error: Faculty ID not found for {$faculty['name']}");
                    }

                    // Insert into assigned_room_block
                    $stmt = $conn->prepare("INSERT INTO assigned_room_block (id, name, date, dept, time, block, room) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssssss", $faculty_id, $faculty['name'], $exam_date, $faculty['dept'], $room_time, $block, $room_no);

                    if (!$stmt->execute()) {
                        die("Error inserting data: " . $stmt->error);
                    } 
                    $stmt->close();

                    $dept_requirements[$dept]--;
                    $room_assignments["$block-$room_no"]++;
                    $facultyAssigned = true;

                    if ($room_assignments["$block-$room_no"] >= $room_capacity) {
                        break;
                    }
                }
            }
            if (!$facultyAssigned) {
                break;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Invigilators</title>
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
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
        }

        .table-container {
            overflow-x: auto;
            width: 100%;
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
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

        h1 {
            text-align: center;
            margin-bottom: 2rem;
            color: #333;
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
            <a href="exm_view_attendance.php" class="menu-item">
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
            <div class="dashboard-header">
                <h2>Assigned Invigilators</h2>
            </div>
            
            <div class="table-container">
                <table>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Block</th>
                        <th>Room</th>
                    </tr>
                    <?php
                    // Fetch and display assigned invigilators
                    $sql = "SELECT date, name, dept, block, room FROM assigned_room_block";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                        echo "<tr><td>" . date("d-m-Y", strtotime($row['date'])) . "</td><td>{$row['name']}</td><td>{$row['dept']}</td><td>{$row['block']}</td><td>{$row['room']}</td></tr>";
                    }

                    $conn->close();
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>