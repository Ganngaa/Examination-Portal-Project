<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize default values
$total_invigilators = "Not Available";
$Date = "Not Available";
$Time = "Not Available";

// Get coordinator ID from session
if (isset($_SESSION['dept_co_id'])) {
    $dept_co_id = $_SESSION['dept_co_id']; 
    $dept_code = substr($dept_co_id, 5, 2); // Extract the 6th and 7th characters

    // Retrieve available dates and times from invg_req
    $sql_datetimes = "SELECT DISTINCT date, time FROM invg_req ORDER BY date ASC, time ASC";
    $result_datetimes = $conn->query($sql_datetimes);

    // Check if a datetime is selected from the dropdown
    if (isset($_POST['selected_datetime'])) {
        list($selected_date, $selected_time) = explode('|', $_POST['selected_datetime']);

        // Use prepared statement to get count for selected date, time, and department
        $sql = "SELECT SUM(`count`) AS total_invigilators FROM invg_req 
                WHERE dept LIKE ? AND date = ? AND time = ?";
        $stmt = $conn->prepare($sql);
        $searchDept = "%$dept_code%";
        $stmt->bind_param("sss", $searchDept, $selected_date, $selected_time);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            $total_invigilators = $row['total_invigilators'] ?? "Not Available";
            $Date = date("d-m-Y", strtotime($selected_date));
            $Time = $selected_time == 'FN' ? 'FN' : 'AN';
        }

        $stmt->close();
    }
} else {
    echo "<h2>Coordinator ID not found in session.</h2>";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invigilators Required</title>
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
        
        .container {
            flex: 1;
            background: rgba(255, 255, 255, 0.9);
            padding: 20px;
            border-radius: 8px;
            position: relative;
            margin-left: 2rem;
            width: calc(100% - 4rem);
            height: calc(100% - 4rem);
        }

        h2 {
            text-align: left;
            margin-top: 20px;
            font-size: 1.5rem;
            color: rgb(4, 25, 46);
        }

        .assign-button {
            display: block;
            margin: 20px auto;
            padding: 0.8rem 1.5rem;
            font-size: 1.2rem;
            color: #fff;
            background: rgba(0, 0, 0, 0.8);
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .assign-button:hover {
            background: rgba(0, 0, 0, 0.9);
        }
        
        /* Style for the datetime dropdown */
        select {
            padding: 0.5rem;
            font-size: 1rem;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-bottom: 1rem;
            width: 100%;
            max-width: 300px;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
    
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <div>        
            <a href="dept_co.php">Home</a>
            <a href="index.html">Logout</a>
        </div>
    </header>
    <div class="main-content">
        <div class="left-panel">
            <a href="rooms.php" class="menu-item">
                <i>💺</i>
                Rooms
            </a>
            <a href="dept_view_attendance.php" class="menu-item">
                <i>📋</i>
                Attendance
            </a>
            <a href="view_all.php" class="menu-item">
                <i>👨‍🏫</i>
                Invigilators
            </a>
        </div>
        <div class="container form-container">
            <form method="POST">
                <label for="datetime">Select Date and Time:</label>
                <select name="selected_datetime" id="datetime" required onchange="this.form.submit()">
                    <option value="">-- Choose date and time --</option>
                    <?php
                    if (isset($result_datetimes) && $result_datetimes->num_rows > 0) {
                        $grouped_datetimes = [];
                        // First pass to group dates and times
                        while ($row = $result_datetimes->fetch_assoc()) {
                            $dateValue = $row['date'];
                            $timeValue = $row['time'];
                            if (!isset($grouped_datetimes[$dateValue])) {
                                $grouped_datetimes[$dateValue] = [];
                            }
                            $grouped_datetimes[$dateValue][] = $timeValue;
                        }
                        
                        // Second pass to generate options
                        foreach ($grouped_datetimes as $date => $times) {
                            $formattedDate = date("d-m-Y", strtotime($date));
                            foreach ($times as $time) {
                                $value = $date . '|' . $time;
                                $displayTime = $time == 'FN' ? 'FN' : 'AN';
                                $display = $formattedDate . ' - ' . $displayTime;
                                $selected = (isset($_POST['selected_datetime']) && $_POST['selected_datetime'] == $value) ? "selected" : "";
                                echo "<option value='$value' $selected>$display</option>";
                            }
                        }
                    }
                    ?>
                </select>
            </form>

            <h2>Date: <?= htmlspecialchars($Date) ?></h2>
            <h2>Time: <?= htmlspecialchars($Time) ?></h2>
            <h2>Total Invigilators Required: <?= htmlspecialchars($total_invigilators) ?></h2>
            
            <?php if (isset($selected_date) && isset($selected_time)): ?>
                <a href="check_avail.php?selected_date=<?= urlencode($selected_date) ?>&selected_time=<?= urlencode($selected_time) ?>" class="assign-button">Check Availability</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>