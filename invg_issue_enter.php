<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $upload_dir = "report/";
    
    // Check if file is uploaded
    if (isset($_FILES['file']) && $_FILES['file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['file'];
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        // Validate file type
        if (in_array(strtolower($file_extension), ['jpg', 'jpeg', 'png'])) {
            $file_path = $upload_dir . basename($file['name']);
            
            // Move the uploaded file to the report directory
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $conn = new mysqli("localhost", "root", "", "login");

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Get invigilator ID from session
                if (isset($_SESSION['invigilator_id'])) {
                    $invigilator_id = $_SESSION['invigilator_id'];
                    $register_number = $_POST['register_number'];
                    $exam_name = $_POST['exam_name'];
                    $issue_type = $_POST['issue_type'];
                    $proof = $file_path; // Use the path of the uploaded file
                    $issue_desc = $_POST['issue_desc'];
                    $date = date("Y-m-d H:i:s");

                    // Prepare and execute SQL query for report
                    $sql = "INSERT INTO report (id, student_id, exam_name, issue_type, proof, issue_desc, date) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssss", $invigilator_id, $register_number, $exam_name, $issue_type, $proof, $issue_desc, $date);

                    if ($stmt->execute()) {
                        header("Location: invg_report_issues.html");
                        exit();
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }

                    // Close connection
                    $stmt->close();
                } else {
                    echo "Error: Invigilator ID not found in session";
                }

                $conn->close();
            } else {
                echo "Error: Failed to move uploaded file.";
            }
        } else {
            echo "Error: Invalid file type. Please upload a file in jpg, jpeg, or png format.";
        }
    } else {
        echo "Error: No file uploaded or there was an upload error. Please check the file input name and ensure a file is selected. File should be a jpg, jpeg, or png.";
    }
}