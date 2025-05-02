<?php
// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "login";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$request_date = isset($_POST['request_date']) ? $_POST['request_date'] : null;
$result = null;

if ($request_date) {
    // Prepare and execute the SQL query to retrieve seating information
    $sql = "SELECT block, room_no, COUNT(room_no) AS room_count 
            FROM seating 
            WHERE DATE(date) = DATE(?) 
            GROUP BY block, room_no";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $request_date);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requirements</title>
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

        .dashboard {
            flex: 1;
            margin-left: 2rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .dashboard-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .dashboard-header h2 {
            color: #333;
            font-size: 1.8rem;
        }

        .form-container {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        input[type="date"] {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            padding: 10px 20px;
            background: #0e0e0e;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background: #333;
        }

        .table-container {
            overflow-x: auto;
            width: 100%;
            margin-top: 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <header>
        <div class="portal-name">University Examination Portal</div>
        <a href="index.html">Logout</a>
    </header>
    
    <div class="main-content">
        <div class="left-panel">
            <a href="view_seating.php" class="menu-item">
                <i>💺</i>
                Seating
            </a>
            <a href="invg_req.html" class="menu-item">
                <i>👥</i>
                Invigilator
            </a>
            <a href="view_report.php" class="menu-item">
                <i>📊</i>
                Report
            </a>
        </div>

        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Requirements</h2>
            </div>

            <!-- Date Selection Form -->
            <form method="POST" action="">
                <div class="form-container">
                    <label for="request_date">Select Date:</label>
                    <input type="date" id="request_date" name="request_date" value="<?php echo htmlspecialchars($request_date); ?>" required>
                    <button type="submit">Submit</button>
                </div>
            </form>

            <!-- Results Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Block</th>
                            <th>Room</th>
                            <th>Invigilator Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($request_date) {
                            if ($result && $result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $invigilator_count = ($row['room_count'] > 30) ? 2 : 1;
                                    echo "<tr>
                                            <td>" . htmlspecialchars($row['block']) . "</td>
                                            <td>" . htmlspecialchars($row['room_no']) . "</td>
                                            <td>" . htmlspecialchars($invigilator_count) . "</td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>No requirements found for the selected date.</td></tr>";
                            }
                        }
                        ?>             
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
