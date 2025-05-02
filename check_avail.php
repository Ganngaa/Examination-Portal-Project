<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$dept_code = '';
$faculty_list = [];
$required_time_slots = [];
$required_counts = []; // To store required counts for each time slot
$date = date('Y-m-d'); // Default to today if no date is found

// Extract department code from session
if (isset($_SESSION['dept_co_id'])) {
    $dept_co_id = mysqli_real_escape_string($conn, $_SESSION['dept_co_id']);
    $dept_code = substr($dept_co_id, 5, 2); // Extract the 6th and 7th characters
}

// Get the date from URL (selected date from count_invg_req.php)
if (isset($_GET['selected_date']) && !empty($_GET['selected_date'])) {
    $date = $_GET['selected_date'];
}

// Format date for display
$formatted_date = date('d-m-Y', strtotime($date));

// Get the day corresponding to the date
$day_of_week = date('l', strtotime($date));

// Get required time slots and counts from `invg_req`
$sql_time = "SELECT time, COUNT(*) as required_count FROM invg_req WHERE date = ? AND dept LIKE ? GROUP BY time";
$stmt_time = $conn->prepare($sql_time);
$dept_code_param = "%$dept_code%";
$stmt_time->bind_param("ss", $date, $dept_code_param);
$stmt_time->execute();
$result_time = $stmt_time->get_result();

$total_required = 0;
while ($row_time = $result_time->fetch_assoc()) {
    $required_time_slots[] = $row_time['time']; // FN or AN
    $required_counts[$row_time['time']] = $row_time['required_count'];
    $total_required += $row_time['required_count'];
}

$stmt_time->close();

// Fetch faculty availability for the selected date and department
if (!empty($required_time_slots)) {
    $placeholders = implode(',', array_fill(0, count($required_time_slots), '?')); // Prepare placeholders
    $sql = "SELECT fa.name AS faculty_name, fa.id AS faculty_id, fd.time 
            FROM faculty_availability fd 
            JOIN faculty_details fa ON fd.faculty_id = fa.id 
            WHERE fd.day = ? 
            AND fa.dept LIKE ? 
            AND fd.time IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    $params = array_merge([$day_of_week, $dept_code_param], $required_time_slots);

    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $faculty_list[] = [
            'faculty_name' => $row['faculty_name'],
            'faculty_id' => $row['faculty_id'],
            'time' => $row['time'] // FN or AN
        ];
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Availability</title>
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

        .faculty-heading {
            color: #141514;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .faculty-list {
            list-style-type: none;
            padding: 0;
        }

        .faculty-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            padding: 1rem;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .faculty-checkbox {
            margin-left: auto;
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
            transition: background-color 0.3s;
        }

        .assign-button:hover {
            background: rgba(0, 0, 0, 0.9);
        }
    </style>
     <script>
        function assignFaculty() {
            const checkboxes = document.querySelectorAll('.faculty-checkbox:checked');
            const selectedFaculty = [];
            
            // Group selected faculty by time slot
            const selectedByTime = {
                'FN': 0,
                'AN': 0
            };
            
            checkboxes.forEach(checkbox => {
                const time = checkbox.getAttribute('data-time');
                selectedByTime[time]++;
                selectedFaculty.push({
                    name: checkbox.getAttribute('data-name'),
                    id: checkbox.getAttribute('data-id'),
                    time: time
                });
            });

            // Check if selected counts exactly match required counts for each time slot
            let isValid = true;
            let errorMessage = '';
            
            <?php foreach ($required_counts as $time => $count): ?>
                if (selectedByTime['<?php echo $time; ?>'] !== <?php echo $count; ?>) {
                    isValid = false;
                    errorMessage += `For <?php echo $time; ?> slot: Exactly <?php echo $count; ?> required, but ${selectedByTime['<?php echo $time; ?>']} selected.\n`;
                }
            <?php endforeach; ?>

            if (!isValid) {
                alert('Error: Faculty selection must exactly match requirements:\n' + errorMessage);
                return false;
            }

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "assign_faculty.php", true);
            xhr.setRequestHeader("Content-Type", "application/json;charset=UTF-8");
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        alert('Faculty assigned successfully!');
                        window.location.href = "view_all.php";
                    } else {
                        alert('Error assigning faculty: ' + response.message);
                    }
                } else {
                    alert('Error assigning faculty.');
                }
            };
            xhr.send(JSON.stringify({ 
                faculty: selectedFaculty, 
                date: '<?php echo $date; ?>', 
                dept: '<?php echo $dept_code; ?>' 
            }));
            return false;
        }

        // Add event listener to prevent selecting more than required
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.faculty-checkbox');
            
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const time = this.getAttribute('data-time');
                    const currentSelected = document.querySelectorAll(`.faculty-checkbox[data-time="${time}"]:checked`).length;
                    const requiredCount = <?php echo json_encode($required_counts); ?>[time] || 0;
                    
                    if (currentSelected > requiredCount) {
                        alert(`You can only select exactly ${requiredCount} faculty.`);
                        this.checked = false;
                    }
                });
            });
        });
    </script>
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
            <a href="dept_seat.html" class="menu-item">
                <i>💺</i>
                Seating
            </a>
            <a href="attendance.php" class="menu-item">
                <i>📋</i>
                Attendance
            </a>
            <a href="view_all.php" class="menu-item">
                <i>👨‍🏫</i>
                Invigilators
            </a>
        </div>
        
        <div class="container">
            <h2 class="faculty-heading">Faculty Available on <?php echo htmlspecialchars($formatted_date); ?>:</h2>
            <p>Required Invigilators: 
                <?php 
                foreach ($required_counts as $time => $count) {
                    echo htmlspecialchars(" $count ");
                }
                ?>
                
            </p>
            <ul class="faculty-list">
                <?php foreach ($faculty_list as $faculty): ?>
                    <li class="faculty-item">
                        <?php echo htmlspecialchars($faculty['faculty_name']) . " (" . htmlspecialchars($faculty['time']) . ")"; ?>
                        <input type="checkbox" class="faculty-checkbox" data-name="<?php echo htmlspecialchars($faculty['faculty_name']); ?>" data-id="<?php echo htmlspecialchars($faculty['faculty_id']); ?>" data-time="<?php echo htmlspecialchars($faculty['time']); ?>">
                    </li>
                <?php endforeach; ?>
            </ul>
            <button class="assign-button" onclick="assignFaculty()">Assign Faculty</button>
        </div>
    </div>
</body>
</html>