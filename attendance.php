<?php
session_start();
if (isset($_SESSION['attendance_marked']) && $_SESSION['attendance_marked'] === true) {
    echo "<script>alert('Attendance Marked');</script>";
    unset($_SESSION['attendance_marked']); // Clear session variable after showing alert
}

// Ensure session is started only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$invigilator_id = $_SESSION['invigilator_id'] ?? null;
if (!$invigilator_id) {
    die("Invigilator not logged in");
}

// Database connection
$conn = new mysqli("localhost", "root", "", "login");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get invigilator's assigned room and block
$assigned_sql = "SELECT room, block, date, time FROM assigned_room_block WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($assigned_sql);
$stmt->bind_param("s", $invigilator_id);
$stmt->execute();
$assigned_result = $stmt->get_result();

if ($assigned_result->num_rows > 0) {
    $assigned = $assigned_result->fetch_assoc();
    $room_no = $assigned['room'];
    $block = $assigned['block'];
    $date = $assigned['date'];
    $time = $assigned['time'];

    // Convert date to display format (DD-MM-YYYY)
    $display_date = date("d-m-Y", strtotime($date));
} else {
    echo "<script>alert('No room assigned for this invigilator');</script>";
    echo "<script>window.location.href='invg_dashboard.php';</script>"; // Redirect to dashboard
    exit; // Stop further execution
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance</title>
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
            overflow: hidden;
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
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 140px);
        }

        .dashboard-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        
        .room-info {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
            color: rgb(26, 26, 27);
        }
        
        .exam-details {
            font-size: 1.1rem;
            color: rgb(26, 26, 27);
        }
        
        .attendance-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .attendance-table th, .attendance-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        
        .attendance-table th {
            background-color: #0e0e0e;
            color: white;
            position: sticky;
            top: 0;
        }
        
        .attendance-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        form {
            display: flex;
            flex-direction: column;
            flex: 1;
            overflow: hidden;
        }
        
        .table-container {
            flex: 1;
            overflow-y: auto;
            padding: 0 1.5rem;
        }
        
        .btn {
            background-color: rgb(15, 15, 15);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 1rem 1.5rem;
            align-self: flex-start;
        }
        
        .btn:hover {
            background-color: rgb(12, 12, 12);
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
                <h2>Mark Attendance</h2>
                <div class="room-info">
                    Room: <?php echo htmlspecialchars($room_no); ?> | Block: <?php echo htmlspecialchars($block); ?>
                </div>
                <div class="exam-details">
                    Date: <?php echo htmlspecialchars($display_date); ?> | Time: <?php echo htmlspecialchars($time); ?>
                </div>
            </div>
            <form id="attendanceForm" action="submit_attendance.php" method="POST">
                <div class="table-container">
                    <table class="attendance-table">
                        <thead>
                            <tr>
                                <th>Bench No</th>
                                <th>Student ID (Seat 1)</th>
                                <th>Status</th>
                                <th>Student ID (Seat 2)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch seating for the invigilator's assigned room
                            $seating_sql = "SELECT bench, seat_1, seat_2 FROM seating 
                                           WHERE room_no = ? AND block = ? AND date = ? AND time = ?
                                           ORDER BY bench";
                            $stmt = $conn->prepare($seating_sql);
                            $stmt->bind_param("ssss", $room_no, $block, $date, $time);
                            $stmt->execute();
                            $seating_result = $stmt->get_result();

                            if ($seating_result->num_rows > 0) {
                                while ($row = $seating_result->fetch_assoc()) {
                                    $bench = htmlspecialchars($row['bench']);
                                    $student_id_1 = htmlspecialchars($row['seat_1']);
                                    $student_id_2 = htmlspecialchars($row['seat_2']);

                                    if (empty($student_id_1) && empty($student_id_2)) {
                                        continue;
                                    }
                            ?>
                            <tr>
                                <td><?php echo $bench; ?></td>
                                <td><?php echo $student_id_1 ?: "-"; ?></td>
                                <td>
                                    <?php if ($student_id_1) { ?>
                                        <input type="hidden" name="attendance_status[<?php echo $student_id_1; ?>]" value="absent">
                                        <input type="checkbox" name="attendance_status[<?php echo $student_id_1; ?>]" value="present" checked>
                                    <?php } else { echo "-"; } ?>
                                </td>
                                <td><?php echo $student_id_2 ?: "-"; ?></td>
                                <td>
                                    <?php if ($student_id_2) { ?>
                                        <input type="hidden" name="attendance_status[<?php echo $student_id_2; ?>]" value="absent">
                                        <input type="checkbox" name="attendance_status[<?php echo $student_id_2; ?>]" value="present" checked>
                                    <?php } else { echo "-"; } ?>
                                </td>
                            </tr>
                            <?php 
                                }
                            } else {
                                echo '<tr><td colspan="5">No seating arrangement found for this room</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                <input type="hidden" name="time" value="<?php echo htmlspecialchars($time); ?>">
                <input type="hidden" name="room_no" value="<?php echo htmlspecialchars($room_no); ?>">
                <input type="hidden" name="block" value="<?php echo htmlspecialchars($block); ?>">
                <button type="submit" class="btn">Submit</button>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("attendanceForm").addEventListener("submit", function (e) {
                e.preventDefault(); // Prevent default form submission

                let formData = new FormData(this); // Get form data

                fetch("submit_attendance.php", {
                    method: "POST",
                    body: formData,
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text();
                })
                .then(data => {
                    alert("Attendance Marked Successfully!");
                    window.location.reload(); // Reload the page to show updated data
                })
                .catch(error => {
                    console.error("Error:", error);
                    alert("Error marking attendance. Please try again.");
                });
            });
        });
    </script>
</body>
</html>