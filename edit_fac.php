<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection
$conn = new mysqli("localhost", "root", "", "login");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if edit_id is set and valid
if (!isset($_GET['edit_id']) || empty(trim($_GET['edit_id']))) {
    die("Error: No faculty ID specified.");
}

$edit_id = trim($_GET['edit_id']);

// Fetch faculty details
$stmt = $conn->prepare("SELECT * FROM faculty_details WHERE id = ?");
$stmt->bind_param("s", $edit_id);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();
$stmt->close();

if (!$faculty) {
    die("Error: Faculty not found.");
}

// Fetch faculty availability
$availability = [];
$stmt = $conn->prepare("SELECT id, day, time FROM faculty_availability WHERE faculty_id = ?");
$stmt->bind_param("s", $edit_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $availability[] = $row;
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phn_no']);
    $days = $_POST['day'] ?? [];
    $times = $_POST['time'] ?? [];

    if (!empty($name) && !empty($phone)) {
        $conn->begin_transaction();
        try {
            // Update faculty_details
            $stmt = $conn->prepare("UPDATE faculty_details SET name=?, phn_no=? WHERE id=?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("sss", $name, $phone, $edit_id);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $stmt->close();

            // Clear previous availability data
            $stmt = $conn->prepare("DELETE FROM faculty_availability WHERE faculty_id = ?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param("s", $edit_id);
            if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
            $stmt->close();

            // Insert new availability records
            $stmt = $conn->prepare("INSERT INTO faculty_availability (faculty_id, day, time) VALUES (?, ?, ?)");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            foreach ($days as $key => $day) {
                $time = $times[$key] ?? '';
                if (!empty($day) && !empty($time)) {
                    $stmt->bind_param("sss", $edit_id, $day, $time);
                    if (!$stmt->execute()) throw new Exception("Execute failed: " . $stmt->error);
                }
            }
            $stmt->close();

            // Commit transaction
            $conn->commit();
            header("Location: search_faculty.php");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    } else {
        echo "Error: Missing values for one or more faculty details.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Faculty</title>
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
        .container {
            flex: 1;
            background: rgba(255, 255, 255, 0.9);
            padding: 2rem;
            border-radius: 8px;
            width: 95%;
            max-width: 600px;
            margin: 2rem auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input {
            width: 100%;
            padding: 0.8rem;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        button {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
        button:hover {
            background: rgba(0, 0, 0, 0.9);
        }
    </style>
    <script>
        function addAvailability(day = '', time = '') {
            let container = document.createElement("div");
            container.innerHTML = `
                <div class="form-group">
                    <label for="day">Day:</label>
                    <input type="text" name="day[]" value="${day}" required>
                </div>
                <div class="form-group">
                    <label for="time">Time:</label>
                    <input type="text" name="time[]" value="${time}" required>
                </div>
            `;
            document.getElementById("availability-container").appendChild(container);
        }

        window.onload = function() {
            let availability = <?php echo json_encode($availability); ?>;
            if (availability.length > 0) {
                document.getElementById("availability-container").innerHTML = "";
                availability.forEach(avail => addAvailability(avail.day, avail.time));
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Edit Faculty Details</h2>
        <form method="POST" action="edit_fac.php?edit_id=<?php echo htmlspecialchars($edit_id); ?>">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($faculty['id']); ?>">

            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" name="name" value="<?php echo isset($faculty['name']) ? htmlspecialchars($faculty['name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="phn_no">Phone Number:</label>
                <input type="text" name="phn_no" value="<?php echo isset($faculty['phn_no']) ? htmlspecialchars($faculty['phn_no']) : ''; ?>" required>
            </div>

            <div id="availability-container">
                <!-- Availability fields will be inserted here via JS -->
            </div>

            <button type="button" onclick="addAvailability()">Add More Availability</button>
            <button type="submit">Update Faculty</button>
        </form>
    </div>
</body>
</html>
