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

        .section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .section-content {
            background: #f9f9f9;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            cursor: pointer;
            flex: 1;
            margin-right: 1rem;
            text-align: center;
        }

        .section-content:last-child {
            margin-right: 0;
        }

        .section-content:hover {
            background: #e9e9e9;
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
                <div class="section">
                    <div class="section-content" onclick="window.location.href='exmcoord_view_stud_report.php'">
                        <h3>Student's Issues</h3>
                    </div>
                    <div class="section-content" onclick="window.location.href='exmcoord_view_ing_issues.php'">
                        <h3>Invigilator's Issues</h3>
                    </div>
                </div>
            </div>
        </div>
</body>
</html>
