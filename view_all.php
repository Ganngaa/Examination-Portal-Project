<?php
session_start();
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get department from session
$dept = '';
if (isset($_SESSION['dept_co_id'])) {
    // Extract department code from dept_co_id (6th and 7th characters)
    $dept_code = substr($_SESSION['dept_co_id'], 5, 2);
    
    // Query to get the full department name based on the code
    $dept_query = "SELECT DISTINCT dept FROM faculty_details WHERE dept LIKE '%$dept_code%'";
    $dept_result = $conn->query($dept_query);
    
    if ($dept_result && $dept_result->num_rows > 0) {
        $dept_row = $dept_result->fetch_assoc();
        $dept = $dept_row['dept'];
    }
}

// Query to get updated faculty details for the specific department
$sql = "SELECT 
            f.id, f.name, f.dept, f.phn_no, 
            fa.day, fa.time
        FROM faculty_details f
        LEFT JOIN faculty_availability fa ON f.id = fa.faculty_id
        WHERE f.dept = '$dept'
        ORDER BY f.id ASC, FIELD(fa.time, 'FN', 'AN')";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Faculty</title>
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
            height: calc(100vh - 90px);
            overflow: hidden;
            gap: 2rem;
        }

        .left-panel {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            width: 200px;
            height: 100%;
        }

        .menu-item {
            background: rgba(0, 0, 0, 0.9);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: transform 0.3s, background-color 0.3s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .menu-item:hover {
            background: rgba(0, 0, 0, 1);
            transform: translateY(-3px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        .menu-item i {
            font-size: 2.2rem;
            margin-bottom: 1rem;
        }

        .dashboard {
            flex: 1;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            top: 0;
            z-index: 1;
        }

        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
            margin-bottom: 0.5rem;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .edit-btn {
            background: rgba(0, 0, 0, 0.85);
            color: white;
            padding: 1rem 1.8rem;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .edit-btn:hover {
            background: rgba(0, 0, 0, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 1.2rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        th {
            background: rgba(0, 0, 0, 0.9);
            color: white;
            font-weight: 600;
            font-size: 1.1rem;
            position: sticky;
            top: 0;
            z-index: 1;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        tr:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        td {
            font-size: 1.05rem;
            color: #333;
        }

        .invigilators-btn {
            display: inline-block;
            margin: 1.5rem 0;
            padding: 1rem 2rem;
            background: rgba(0, 0, 0, 0.85);
            color: white;
            text-align: center;
            border-radius: 10px;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .invigilators-btn:hover {
            background: rgba(0, 0, 0, 1);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
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
        
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Faculty Details</h2>
            </div>
            <div class="button-group">
                <a href="add_fac.html" class="edit-btn">Add New Faculty</a>
                <a href="edit_faculty.html" class="edit-btn">Edit Faculty Details</a>
                <a href="count_invg_req.php" class="edit-btn">Invigilators Required</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Phone</th>
                        <th>Day Available</th>
                        <th>Time Available</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $prev_id = null;
                    while ($row = $result->fetch_assoc()) {
                        if ($row['id'] !== $prev_id) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['dept']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phn_no']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['day']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                            echo "</tr>";
                        } else {
                            echo "<tr>";
                            echo "<td></td><td></td><td></td><td></td>"; // Empty cells for repeated faculty
                            echo "<td>" . htmlspecialchars($row['day']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['time']) . "</td>";
                            echo "</tr>";
                        }
                        $prev_id = $row['id'];
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>