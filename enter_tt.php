<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'] ?? '';
    $sem = $_POST['sem'] ?? '';
    $sub = $_POST['sub'] ?? '';
    $time = $_POST['time'] ?? '';

    // Check if any values are empty
    if (empty($date) || empty($sem) || empty($sub) || empty($time)) {
        die("Error: Missing required fields.");
    }

    // Database connection
    $conn = new mysqli("localhost", "root", "", "login");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Insert into timetable table
    $sql = "INSERT INTO timetable (date, sem, sub, time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        die("Error in SQL preparation: " . $conn->error);
    }

    $stmt->bind_param("siss", $date, $sem, $sub, $time);

    if ($stmt->execute()) {
        echo "Entry added successfully!";
    } else {
        echo "Error executing query: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
