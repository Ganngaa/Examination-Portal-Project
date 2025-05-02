<?php
session_start(); // Ensure session is started

$conn = new mysqli("localhost", "root", "", "login");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get student ID from session
if (isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];
    $student_id_prefix = substr($student_id, 0, 10); // Get the first 10 characters of the student ID

    // Prepare SQL to select only the PDF files matching the student ID prefix
    $sql = "SELECT pdf_name, pdf_path FROM pdf_files WHERE pdf_name LIKE '$student_id_prefix%' ORDER BY upload_date DESC"; // Order by upload_date in descending order
    $result = $conn->query($sql);
} else {
    echo "<div class='file-tile centered'>No student ID found in session.</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hall Ticket</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
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
            overflow-y: auto; /* Allow vertical scrolling */
            max-height: calc(100vh - 200px); /* Adjust height to allow scrolling */
            display: flex; /* Change to flex for horizontal layout */
            flex-direction: column; /* Align items in a column */
            gap: 1rem; /* Space between tiles */
        }
        .file-tile {
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem; /* Increased padding for larger tiles */
            text-align: center;
            transition: transform 0.3s;
            display: flex; /* Ensure file tile is also a flex container */
            flex-direction: column; /* Keep content vertical */
            align-items: center; /* Center content */
            width: 1520px; /* Increased width for tiles */
            height: 85px; /* Increased height for tiles */
        }
        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }
        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }
        .centered {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .logout {
            color: #fff; /* Make logout text white */
            text-decoration: none; /* Remove underline */
            font-size: 1.2rem; /* Increase size */
            margin-left: auto; /* Increase spacing to the right */
            padding-right: 2rem; /* Add padding for more spacing */
        }
        .home {
            color: #fff; /* Make home text white */
            text-decoration: none; /* Remove underline */
            font-size: 1.2rem; /* Increase size */
            margin-left: auto; /* Increase spacing to the right */
            padding-right: 2rem; /* Add padding for more spacing */
        }
        .btn {
            background-color:rgb(0, 0, 0); /* Button color */
            color: white; /* Button text color */
            padding: 0.5rem 1rem; /* Button padding */
            border: none; /* No border */
            border-radius: 4px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            text-decoration: none; /* No underline */
            font-size: 1rem; /* Font size */
            transition: background-color 0.3s; /* Transition for hover effect */
            margin-top: 0.75rem; /* Increased spacing between text and button */
        }
        .btn:hover {
            background-color:rgb(165, 165, 165); /* Darker shade on hover */
        }
        .file-tile h3 {
            font-size: 1.7rem; /* Decreased font size for file names */
            margin: 0; /* Remove margin for better alignment */
        }
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>
            <a href="student_home.php" class="home">Home</a>
            <a href="index.html" class="logout">Logout</a>
        </div>
    </header>
    <div class="main-content">
        <div class="tab-menu">
            <div class="tab-item">
                <a href="download.php">
                    <i>🎫</i>
                    Hall Ticket
                </a>
            </div>
            <div class="tab-item">
                <a href="stud_attendance.php">
                    <i>📋</i>
                    Attendance
                </a>
            </div>
            <div class="tab-item">
                <a href="stud_seat.html">
                    <i>💺</i>
                    Seating
                </a>
            </div>
            <div class="tab-item">
                <a href="stud_report_issues.html">
                    <i>📊</i>
                    Report
                </a>
            </div>
        </div>
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Hall Ticket</h2>
            </div>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $pdf_path = $row['pdf_path']; // Use pdf_path from the database
                    ?>
                    <div class="file-tile">
                        <h3><?php echo htmlspecialchars($row['pdf_name']); ?></h3>
                        <a href="<?php echo htmlspecialchars($pdf_path); ?>" class="btn" download>Download</a>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div class="file-tile centered">No files uploaded yet.</div>
                <?php
            }
            ?>
        </div>
    </div>
</body>
</html>
<?php
$conn->close();
?>
