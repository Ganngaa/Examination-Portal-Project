<?php
session_start();

header('Content-Type: application/json');

// Database connection
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Connection failed: " . $conn->connect_error]));
}

// Get JSON input data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['faculty']) || !is_array($data['faculty']) || empty($data['faculty'])) {
    echo json_encode(["success" => false, "message" => "Invalid faculty data."]);
    exit;
}

$date = isset($data['date']) ? $conn->real_escape_string($data['date']) : date('Y-m-d');
$dept = isset($data['dept']) ? $conn->real_escape_string($data['dept']) : '';

// Begin transaction
$conn->begin_transaction();

try {
    foreach ($data['faculty'] as $faculty) {
        $faculty_id = $conn->real_escape_string($faculty['id']);
        $faculty_name = $conn->real_escape_string($faculty['name']);

        // Check if faculty is already assigned for this date
        $check_sql = "SELECT id FROM assigned_fac WHERE id = ? AND date = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $faculty_id, $date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            throw new Exception("Faculty member " . $faculty_name . " is already assigned for this date.");
        }
        $check_stmt->close();

        // Fetch faculty time from `faculty_availability`
        $sql_time = "SELECT time FROM faculty_availability WHERE faculty_id = ? AND day = ?";
        $stmt_time = $conn->prepare($sql_time);
        $day_of_week = date('l', strtotime($date)); // Get full day name
        $stmt_time->bind_param("ss", $faculty_id, $day_of_week);
        $stmt_time->execute();
        $result_time = $stmt_time->get_result();
        $faculty_time = '';

        if ($row_time = $result_time->fetch_assoc()) {
            $faculty_time = $row_time['time'];
        }
        $stmt_time->close();

        // Insert assigned faculty with time
        $sql = "INSERT INTO assigned_fac (id, name, date, dept, time) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $faculty_id, $faculty_name, $date, $dept, $faculty_time);
        
        if (!$stmt->execute()) {
            throw new Exception("Error inserting faculty: " . $stmt->error);
        }
        $stmt->close();
    }
    
    // Commit transaction
    $conn->commit();
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}

$conn->close();
?>
