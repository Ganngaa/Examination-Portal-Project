<?php
// Start the session to access session variables if needed
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the student ID from the session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
} else {
    // Redirect to login page if not logged in
    header("Location: index.html");
    exit();
}

// Get the exam date from the form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $exam_date = $_POST['exam_date'];
} else {
    // If not a POST request, redirect back to the form
    header("Location: stud_seat.html");
    exit();
}

// Format the date for display
$formatted_date = date("F j, Y", strtotime($exam_date));

// Query to get seating arrangement for the student on the given date
$sql = "SELECT s.block, s.room_no, s.bench, s.seat_1, s.seat_2 
        FROM seating s
        WHERE (s.seat_1 = ? OR s.seat_2 = ?) AND s.date = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $student_id, $student_id, $exam_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Arrangement</title>
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
            margin-left: 20px;
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

        .seating-info {
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table, th, td {
            border: 1px solid #ddd;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .message {
            text-align: center;
            color: #d9534f;
            margin-top: 20px;
        }

        .back-button {
            margin-top: 20px;
        }

        .back-button a {
            padding: 0.6rem 1.5rem;
            font-size: 1rem;
            background: #333;
            color: white;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
        }

        .back-button a:hover {
            background: #444;
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
                <h2>Your Seating Arrangement</h2>
                <p>Exam Date: <?php echo $formatted_date; ?></p>
            </div>
            
            <div class="seating-info">
                <?php
                if ($result->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th>Block</th><th>Room</th><th>Bench</th><th>Seat</th></tr>";
                    
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["block"] . "</td>";
                        echo "<td>" . $row["room_no"] . "</td>";
                        echo "<td>" . $row["bench"] . "</td>";
                        
                        // Determine which seat the student is assigned to
                        if ($row["seat_1"] == $student_id) {
                            echo "<td>Left</td>";
                        } else if ($row["seat_2"] == $student_id) {
                            echo "<td>Right</td>";
                        } else {
                            echo "<td>Unknown</td>";
                        }
                        
                        echo "</tr>";
                    }
                    
                    echo "</table>";
                } else {
                    echo "<div class='message'>No seating arrangement found for the selected date.</div>";
                }
                ?>
            </div>
            
            <div class="back-button">
                <a href="stud_seat.html">Back to Search</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Close the database connection
$stmt->close();
$conn->close();
?>
