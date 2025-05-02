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

// Check if multiple faculty details are provided
if (!isset($_POST['id']) || !is_array($_POST['id']) || empty($_POST['id'])) {
    die("Error: No faculty data provided.");
}

// Start transaction
$conn->begin_transaction();
try {
    foreach ($_POST['id'] as $index => $faculty_id) {
        // Ensure required fields are set and valid for each faculty
        if (
            isset($_POST['name'][$index], $_POST['dept'][$index], $_POST['phn_no'][$index]) &&
            !empty($_POST['name'][$index]) && !empty($_POST['dept'][$index]) && !empty($_POST['phn_no'][$index])
        ) {
            // Sanitize input
            $id    = trim($_POST['id'][$index]);
            $name  = trim($_POST['name'][$index]);
            $dept  = trim($_POST['dept'][$index]);
            $phone = trim($_POST['phn_no'][$index]);

            // Insert faculty details
            $stmt_faculty = $conn->prepare("INSERT INTO faculty_details (id, name, dept, phn_no) VALUES (?, ?, ?, ?)");
            if (!$stmt_faculty) {
                throw new Exception("Faculty Insert Prepare Failed: " . $conn->error);
            }
            $stmt_faculty->bind_param("ssss", $id, $name, $dept, $phone);
            if (!$stmt_faculty->execute()) {
                throw new Exception("Faculty Insert Execution Failed: " . $stmt_faculty->error);
            }
            $stmt_faculty->close();

            // Insert faculty availability
            if (isset($_POST['day'][$index], $_POST['time'][$index])) {
                $stmt_availability = $conn->prepare("INSERT INTO faculty_availability (faculty_id, day, time) VALUES (?, ?, ?)");
                if (!$stmt_availability) {
                    throw new Exception("Availability Insert Prepare Failed: " . $conn->error);
                }

                foreach ($_POST['day'][$index] as $dayIndex => $day) {
                    $time = $_POST['time'][$index][$dayIndex] ?? '';
                    if (!empty($day) && !empty($time)) {
                        $stmt_availability->bind_param("sss", $id, $day, $time);
                        if (!$stmt_availability->execute()) {
                            throw new Exception("Availability Insert Execution Failed: " . $stmt_availability->error);
                        }
                    }
                }
                $stmt_availability->close();
            }
        }
    }

    // Commit transaction
    $conn->commit();
    $conn->close();

    // Redirect to view_all.php after successful insertion
    header("Location: view_all.php");
    exit();
} catch (Exception $e) {
    $conn->rollback();
    die("Transaction failed: " . $e->getMessage());
}
?>
