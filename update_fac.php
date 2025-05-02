<?php
session_start();
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'][0])) {
    // Retrieve faculty details from the form
    $id = trim($_POST['id'][0]);
    $name = trim($_POST['name'][0]);
    $dept = trim($_POST['dept'][0]);
    $phn_no = trim($_POST['phn_no'][0]);

    // Validate inputs
    if (empty($id) || empty($name) || empty($dept) || empty($phn_no)) {
        echo "All fields are required!";
        exit;
    }

    // Update faculty_details
    $updateSql = "UPDATE faculty_details SET name = ?, dept = ?, phn_no = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ssss", $name, $dept, $phn_no, $id);

    if ($stmt->execute()) {
        // Remove old availability for this faculty
        $deleteSql = "DELETE FROM faculty_availability WHERE faculty_id = ?";
        $deleteStmt = $conn->prepare($deleteSql);
        $deleteStmt->bind_param("s", $id);
        $deleteStmt->execute();
        $deleteStmt->close();

        // Insert new availability
        $available_days = isset($_POST['available_day']) ? $_POST['available_day'] : [];
        $available_times = isset($_POST['available_time']) ? $_POST['available_time'] : [];

        foreach ($available_days as $index => $day) {
            $time = isset($available_times[$index]) ? $available_times[$index] : '';

            // Insert updated availability
            $insertAvailabilitySql = "INSERT INTO faculty_availability (faculty_id, day, time) VALUES (?, ?, ?)";
            $availabilityStmt = $conn->prepare($insertAvailabilitySql);
            $availabilityStmt->bind_param("sss", $id, $day, $time);
            $availabilityStmt->execute();
            $availabilityStmt->close();
        }

        // Acknowledgement and redirection
        $_SESSION['acknowledgement'] = "Faculty details updated successfully.";
        header("Location: view_all.php");
        exit();
    } else {
        echo "Error updating record: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
