<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Coordinator Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
            overflow: hidden;
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
            height: calc(100vh - 90px);
            overflow: hidden;
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
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 140px);
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

        .date-selector {
            padding: 1rem;
            margin-bottom: 2rem;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .date-selector input[type="date"] {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .date-selector select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 1rem;
        }

        .date-selector button {
            padding: 0.5rem 1rem;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .attendance-container {
            flex: 1;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .attendance-table th,
        .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .attendance-table th {
            background: #f5f5f5;
            font-weight: bold;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .attendance-table tr:hover {
            background: #f9f9f9;
        }

        .present {
            color: #008000;
        }

        .absent {
            color: #ff0000;
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
            <a href="attendance.php" class="menu-item">
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
                <h2>Attendance View</h2>
            </div>

            <div class="date-selector">
                <form method="POST">
                    <input type="date" name="attendance_date" required>
                    <select name="time" required>
                        <option value="FN">FN</option>
                        <option value="AN">AN</option>
                    </select>
                    <button type="submit">View Attendance</button>
                </form>
            </div>

            <div class="attendance-container">
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $date = $_POST['attendance_date'];
                $time = $_POST['time'];
                
                // Database connection
                $conn = new mysqli("localhost", "root", "", "login");
                
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Query to get attendance records for the selected date and time
                $sql = "SELECT student_id, status, date 
                        FROM attendance 
                        WHERE DATE(date) = ? AND time = ?
                        ORDER BY student_id ASC";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ss", $date, $time);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    echo "<h3>Attendance for " . date('d-m-Y', strtotime($date)) . " (" . $time . ")</h3>";
                    echo "<table class='attendance-table' role='table'>
                            <tr>
                                <th scope='col'>Student ID</th>
                                <th scope='col'>Status</th>
                            </tr>";

                    while ($row = $result->fetch_assoc()) {
                        $statusClass = strtolower($row['status']);
                        echo "<tr>
                                <td>".$row['student_id']."</td>
                                <td class='".$statusClass."'>".$row['status']."</td>
                            </tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p>No attendance records found for the selected date and time.</p>";
                }
                
                $stmt->close();
                $conn->close();
            }
            ?>
            </div>
        </div>
    </div>
</body>
</html>
