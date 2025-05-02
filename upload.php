<?php
// Prevent any unwanted output
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Increase PHP limits for large file uploads
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
ini_set('max_execution_time', '300'); // 5 minutes
ini_set('max_input_time', '300'); // 5 minutes
ini_set('memory_limit', '256M');

// Ensure we're sending JSON response
header('Content-Type: application/json');

try {
    // Enable error logging
    error_log("Starting file upload process");
    
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Log the number of files received
    error_log("Number of files received: " . (isset($_FILES['splits']) ? count($_FILES['splits']['tmp_name']) : 0));

    // Create uploads directory if it doesn't exist
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception("Failed to create uploads directory");
        }
    }

    // Verify database connection
    $conn = new mysqli("localhost", "root", "", "login");
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Handle split PDFs
    if (!isset($_FILES['splits']) || !is_array($_FILES['splits']['tmp_name'])) {
        throw new Exception("No split PDFs received or invalid format");
    }

    $success_count = 0;
    $error_count = 0;
    $semester = isset($_POST['semester']) ? $_POST['semester'] : '';
    
    // Count how many files were uploaded
    $total_files = count($_FILES['splits']['tmp_name']);
    
    // Loop through each uploaded file
    for ($i = 0; $i < $total_files; $i++) {
        $tmp_name = $_FILES['splits']['tmp_name'][$i];
        $name = basename($_FILES['splits']['name'][$i]);
        $error = $_FILES['splits']['error'][$i];
        
        // Extract reg_no from pdf_name
        $pdf_name = pathinfo($name, PATHINFO_FILENAME);
        if (strtoupper($pdf_name[0]) === 'I') {
            $reg_no = substr($pdf_name, 0, 10);
        } elseif (strtoupper($pdf_name[0]) === 'L') {
            $reg_no = substr($pdf_name, 0, 11);
        } else {
            $reg_no = 'Unknown';
        }
        
        // Extract branch based on reg_no pattern
        if (strtoupper($reg_no[0]) === 'L') {
            $branch = substr($reg_no, 6, 2);
        } elseif (strtoupper($reg_no[0]) === 'I') {
            $branch = substr($reg_no, 5, 2);
        } else {
            $branch = 'Unknown';
        }
        
        // Log progress
        error_log("Processing file $i: $name with reg_no: $reg_no, branch: $branch");
        
        if ($error === UPLOAD_ERR_OK) {
            $target_file = $upload_dir . $name;

            if (move_uploaded_file($tmp_name, $target_file)) {
                // Log successful file move
                error_log("Successfully moved file to: $target_file");
                
                $upload_date = date("Y-m-d H:i:s");

                // First check if this file already exists
                $check_stmt = $conn->prepare("SELECT COUNT(*) FROM pdf_files WHERE pdf_name = ?");
                if (!$check_stmt) {
                    error_log("Database prepare error: " . $conn->error);
                    throw new Exception("Database prepare error: " . $conn->error);
                }

                $check_stmt->bind_param("s", $pdf_name);
                $check_stmt->execute();
                $check_stmt->bind_result($count);
                $check_stmt->fetch();
                $check_stmt->close();

                if ($count == 0) {
                    // Insert into database with branch
                    $stmt = $conn->prepare("INSERT INTO pdf_files (pdf_name, pdf_path, upload_date, semester, reg_no, branch) VALUES (?, ?, ?, ?, ?, ?)");
                    if (!$stmt) {
                        error_log("Database prepare error: " . $conn->error);
                        throw new Exception("Database prepare error: " . $conn->error);
                    }

                    $stmt->bind_param("ssssss", $pdf_name, $target_file, $upload_date, $semester, $reg_no, $branch);
                    
                    if ($stmt->execute()) {
                        error_log("Successfully inserted record for: $pdf_name");
                        $success_count++;
                    } else {
                        error_log("Database insert error for file $name: " . $stmt->error);
                        $error_count++;
                    }
                    $stmt->close();
                } else {
                    error_log("File already exists in database: $pdf_name");
                    $success_count++;
                }
            } else {
                error_log("Failed to move uploaded file $name to $target_file");
                $error_count++;
            }
        } else {
            error_log("Upload error for file $name: " . $error);
            $error_count++;
        }
        
        // Log progress after each file
        error_log("Progress: $success_count successful, $error_count failed");
    }

    $conn->close();
    error_log("Upload process completed. Success: $success_count, Failed: $error_count");

    echo json_encode([
        'success' => $success_count > 0,
        'message' => "Processed $success_count files successfully. Failed: $error_count"
    ]);

} catch (Exception $e) {
    error_log("Error in upload.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
