<?php
$conn = new mysqli("localhost", "root", "", "login");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Prepare SQL to select only the PDF files matching the student ID prefix
$sql = "SELECT * FROM report ORDER BY date ASC"; // Order by upload_date in descending order
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
            overflow: hidden;
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
            overflow: hidden; /* Prevent scrolling in main content */
        }

        .left-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 200px;
        }

        .menu-item {
            background: rgba(0, 0, 0, 1);
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
        }

        .menu-item:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: translateY(-3px);
        }

        .menu-item i {
            font-size: 2rem;
            margin-bottom: 0.8rem;
        }

        .dashboard {
            flex: 1;
            margin-left: 2rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 140px);
        }

        .dashboard-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .reports-list {
            display: grid;
            gap: 1rem;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .report-item {
            background: #fff;
            padding: 1.2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eee;
        }

        .report-title {
            font-weight: bold;
            font-size: 1.1rem;
            color: #444;
        }

        .report-date {
            color: #666;
            font-size: 0.9rem;
        }

        .report-content {
            margin: 0.8rem 0;
            line-height: 1.5;
            color: #555;
        }

        .report-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 0.8rem;
            padding-top: 0.5rem;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
            color: #666;
        }

        .report-footer .btn {
            margin-left: auto; /* Align button to the right */
        }

        .report-status {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
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

    </style>
</head>
<body>

    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>
            <a href="exmcoord_home.php">Home</a>
            <a href="index.html">Logout</a>
        </div>
    </header>
    
    <div class="main-content">
        <div class="left-panel">
            <a href="view_seating.php" class="menu-item">
                <i>💺</i>
                Seating
            </a>
            <a href="exm_view_attendance.php" class="menu-item"> <!-- Added Attendance link -->
                <i>📋</i>
                Attendance
            </a>
            <a href="invg_req.php" class="menu-item">
                <i>👨‍🏫</i>
                Invigilator
            </a>
            <a href="exm_coord_view_report.php" class="menu-item">
                <i>📊</i>
                Report
            </a>
        </div>
        
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Reported Issues</h2>
            </div>
            <div class="reports-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $proof = $row['proof']; // Use pdf_path from the database
                        ?>
                        <div class="file-tile">
                            <div class="report-item">
                                <div class="report-header">
                                    <span class="report-title"><?php echo htmlspecialchars($row['issue_type']); ?></span>
                                    <span class="report-date"><?php echo htmlspecialchars($row['date']); ?></span>
                                </div>
                                <div class="report-content">
                                    STUDENT: <strong><?php echo htmlspecialchars($row['student_id']); ?></strong>
                                </div>
                                <div class="report-content">
                                    EXAM: <strong><?php echo htmlspecialchars($row['exam_name']); ?></strong>
                                </div>
                                <div class="report-content">
                                <?php echo htmlspecialchars($row['issue_desc']); ?>
                                </div>
                                <div class="report-footer">
                                    <span>Reported by: <?php echo htmlspecialchars($row['id']); ?></span>
                                    <a href="<?php echo htmlspecialchars($proof); ?>" class="btn" download>Download</a>
                                </div>
                            </div>
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
    </div>
</body>
</html>
