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

// Get student ID from session
if (isset($_SESSION['student_id'])) {
    $student_id = mysqli_real_escape_string($conn, $_SESSION['student_id']);
    
    // Query to get student data
    $sql = "SELECT name, id, department, semester, phone_no, email FROM credentials WHERE id = '$student_id'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $student_name = $row['name'];
        $student_id = $row['id'];
        $student_department = $row['department'];
        $student_semester = $row['semester'];
        $student_contact = $row['phone_no'];
        $student_email = $row['email'];
    } else {
        // Handle error - student not found
        $student_name = 'Not Found';
        $student_id = 'Not Found';
        $student_department = 'Not Found';
        $student_semester = 'Not Found'; 
        $student_contact = 'Not Found';
        $student_email = 'Not Found';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>
            <a href="student_home.php">Home</a>
            <a href="index.html">Logout</a>
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
                <h2>Student Dashboard</h2>
            </div>
            <div class="dashboard-grid">
                <div class="dashboard-item">
                    <label>Name</label>
                    <span><?php echo $student_name; ?></span>
                </div>
                <div class="dashboard-item">
                    <label>Student ID</label>
                    <span><?php echo $student_id; ?></span>
                </div>
                <div class="dashboard-item">
                    <label>Department</label>
                    <span><?php echo $student_department; ?></span>
                </div>
                <div class="dashboard-item">
                    <label>Semester</label>
                    <span><?php echo $student_semester; ?></span>
                </div>
                <div class="dashboard-item">
                    <label>Email</label>
                    <span><?php echo $student_email; ?></span>
                </div>
                <div class="dashboard-item">
                    <label>Contact</label>
                    <span><?php echo $student_contact; ?></span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
