<?php
session_start(); // Start session to get logged-in student ID

// Check if the student is logged in
if (!isset($_SESSION['student_id'])) {
    die("Access denied. Please log in.");
}

$student_id = $_SESSION['student_id']; // Get logged-in student ID

$conn = new mysqli("localhost", "root", "", "login");

// Check for connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to fetch attendance details with subjects filtered by matching branches
$sql = "SELECT a.date, a.time, c.course_code, a.status 
        FROM attendance a
        JOIN timetable t ON a.date = t.date AND a.time = t.time
        JOIN course_table c ON t.sub = c.course_code
        JOIN pdf_files p ON c.branch = p.branch
        WHERE a.student_id = ? AND p.reg_no = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $student_id, $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance</title>
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
        .portal-name {
            color: #fff;
            font-size: 1.8rem;
            font-weight: bold;
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
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
        }
        .dashboard-item {
            background: rgba(255, 255, 255, 0.8);
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .dashboard-item label {
            display: block;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: #555;
        }
        .dashboard-item span {
            font-size: 1.1rem;
            color: #333;
        }
        
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .attendance-table th, .attendance-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }
        .attendance-table th {
            background-color: rgba(0, 0, 0, 0.05);
            font-weight: bold;
        }
        .present { color: #008000; font-weight: bold; }
        .absent { color: #ff0000; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>
            <a href="student_home.php" class="nav-button">Home</a>
            <a href="index.html" class="nav-button">Logout</a>
        </div>
    </header>

    <div class="main-content">
        <div class="tab-menu">
            <div class="tab-item">
                <a href="download.php">
                    <i>🎫</i>
                    Hall Ticket
                </a>
            </div>
            <div class="tab-item">
                <a href="stud_attendance.php">
                    <i>📋</i>
                    Attendance
                </a>
            </div>
            <div class="tab-item">
                <a href="stud_seat.html">
                    <i>💺</i>
                    Seating
                </a>
            </div>
            <div class="tab-item">
                <a href="stud_report_issues.html">
                    <i>📊</i>
                    Report
                </a>
            </div>
        </div>

        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Attendance Record</h2>
            </div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Subject</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $status_class = strtolower($row['status']) == 'present' ? 'present' : 'absent';
                            echo "<tr>
                                   <td>" . date("d-m-Y", strtotime($row['date'])) . "</td>
                                    <td>{$row['time']}</td>
                                    <td>{$row['course_code']}</td>
                                    <td class='{$status_class}'>
                                        {$row['status']}
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No attendance records found.</td></tr>";
                    }
                    $stmt->close();
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .nav-button {
            color: #fff;
            text-decoration: none;
            font-size: 1.2rem;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.4);
        }
    </style>
</body>
</html>
