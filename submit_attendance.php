<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "login");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure form data is set
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['attendance_status'])) {
    $attendance_status = $_POST['attendance_status'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $room_no = $_POST['room_no'];
    $block = $_POST['block'];

    foreach ($attendance_status as $student_id => $status) {
        // Check if attendance exists
        $check_sql = "SELECT * FROM attendance WHERE student_id = ? AND date = ? AND time = ? AND room_no = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ssss", $student_id, $date, $time, $room_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update existing record
            $update_sql = "UPDATE attendance SET status = ? WHERE student_id = ? AND date = ? AND time = ? AND room_no = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssss", $status, $student_id, $date, $time, $room_no);
        } else {
            // Insert new attendance record
            $insert_sql = "INSERT INTO attendance (student_id, date, time, room_no, block, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ssssss", $student_id, $date, $time, $room_no, $block, $status);
        }
        $stmt->execute();
    }

    $_SESSION['attendance_marked'] = true;
    header("Location: attendance.php"); // Redirect to prevent resubmission
    exit();
} else {
    echo "No attendance data received.";
}

$conn->close();
?>
