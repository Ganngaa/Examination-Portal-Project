<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "login");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if delete_id is set in POST request
if(isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    
    // Prepare delete statement
    $stmt = $conn->prepare("DELETE FROM faculty_details WHERE id = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameter and execute
    $stmt->bind_param("s", $delete_id);
    if (!$stmt->execute()) {
        error_log("Execute failed: " . $stmt->error);
        echo "Error deleting faculty member";
    }

    $stmt->close();
    $conn->close();

    // Redirect back to search page after deletion
    header("Location: search_faculty.php");
    exit();
} else {
    echo "Error: No faculty ID specified for deletion";
}

$conn->close();
?>
