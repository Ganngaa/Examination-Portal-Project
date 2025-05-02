<?php
session_start();

$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    $issue_type = $_POST['issue_type'];
    $issue_desc = $_POST['issue_desc'];
    $date = date('Y-m-d H:i:s'); // Current timestamp

    // Prepare and execute SQL query
    $sql = "INSERT INTO issues (id, issue_type, issue_desc, date) 
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $student_id, $issue_type, $issue_desc, $date);

    if ($stmt->execute()) {
        header("Location: stud_report_issues.html");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    // Close connection
    $stmt->close();
} else {
    echo "Error: Student ID not found in session";
}

$conn->close();
?>
