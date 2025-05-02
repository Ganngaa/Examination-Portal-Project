<?php
session_start();
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get search term if submitted
$search = isset($_POST['search']) ? $_POST['search'] : '';

// Split the search term by comma and trim whitespace
$searchTerms = array_map('trim', explode(',', $search));

// Prepare the SQL query with placeholders for each search term
$sql = "SELECT 
            f.id, f.name, f.dept, f.phn_no,
            fa.day AS available_day,
            fa.time AS available_time
        FROM faculty_details f
        LEFT JOIN faculty_availability fa ON f.id = fa.faculty_id
        WHERE f.id IN (" . implode(',', array_fill(0, count($searchTerms), '?')) . ") 
        OR f.name LIKE ?";

// Prepare the statement
$stmt = $conn->prepare($sql);

// Create an array for binding parameters
$params = [];
foreach ($searchTerms as $term) {
    $params[] = $term;
}
$searchTerm = "%" . implode('%', $searchTerms) . "%"; // Create a search term for LIKE
$params[] = $searchTerm;

// Bind parameters dynamically
$stmt->bind_param(str_repeat('s', count($params)), ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Faculty</title>
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
            position: relative;
        }

        .dashboard-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .search-form {
            margin-bottom: 2rem;
        }

        .search-input {
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px;
        }

        .search-button {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 20px;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        th {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            font-weight: bold;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.9);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .action-btn {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
        }

        .action-btn:hover {
            background: rgba(0, 0, 0, 0.9);
        }

        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
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
              Seating
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
                <h2>Search Faculty</h2>
            </div>
            
            <form method="POST" class="search-form">
                <input type="text" name="search" placeholder="Search by faculty ID or name..." class="search-input" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="search-button">Search</button>
            </form>

            <form method="POST" action="update_fac.php">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Department</th>
                            <th>Phone</th>
                            <th>Available Days</th>
                            <th>Available Times</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            $facultyData = [];
                            while($row = $result->fetch_assoc()) {
                                $facultyData[$row['id']][] = $row;
                            }

                            foreach ($facultyData as $faculty) {
                                $firstRow = true;
                                foreach ($faculty as $row) {
                                    if ($firstRow) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                                        echo "<td><input type='text' name='name[]' value='" . htmlspecialchars($row['name']) . "' /></td>";
                                        echo "<td><input type='text' name='dept[]' value='" . htmlspecialchars($row['dept']) . "' /></td>";
                                        echo "<td><input type='text' name='phn_no[]' value='" . htmlspecialchars($row['phn_no']) . "' /></td>";
                                        echo "<input type='hidden' name='id[]' value='" . htmlspecialchars($row['id']) . "' />";
                                        $firstRow = false;
                                    } else {
                                        echo "<tr>";
                                        echo "<td></td><td></td><td></td><td></td>";
                                    }
                                    
                                    // Day dropdown
                                    echo "<td><select name='available_day[]'>";
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                    foreach ($days as $day) {
                                        $selected = ($day == $row['available_day']) ? 'selected' : '';
                                        echo "<option value='$day' $selected>$day</option>";
                                    }
                                    echo "</select></td>";
                                    
                                    // Time dropdown
                                    echo "<td><select name='available_time[]'>";
                                    $times = ['FN', 'AN'];
                                    foreach ($times as $time) {
                                        $selected = ($time == $row['available_time']) ? 'selected' : '';
                                        echo "<option value='$time' $selected>$time</option>";
                                    }
                                    echo "</select></td>";
                                    
                                    echo "</tr>";
                                }
                            }
                        } else {
                            echo "<tr><td colspan='6'>No faculty found matching your search</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
                
                <div class="form-actions">
                    <a href="view_all.php" class="action-btn">Back</a>
                    <button type="submit" class="action-btn">Update Faculty</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>