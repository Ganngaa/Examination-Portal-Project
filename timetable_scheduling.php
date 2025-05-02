<?php
// fetch_courses.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $semester = $_POST['semester'];
    $Branch = $_POST['Branch'];

    // Database connection
    $conn = new mysqli("localhost", "root", "", "login");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to get course codes from course_table
    $sql = "SELECT course_code FROM course_table WHERE semester = ? AND Branch = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $semester, $Branch);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<h3>Course Codes:</h3><ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['course_code']) . " <input type='date' name='date_" . htmlspecialchars($row['course_code']) . "' required data-course='" . htmlspecialchars($row['course_code']) . "' class='date-input'>";
            echo "<select name='session_" . htmlspecialchars($row['course_code']) . "' required class='session-select' style='width: auto; padding: 0.5rem; margin-left: 0.5rem; border: 1px solid #ccc; border-radius: 4px; font-size: 1rem;'>";
            echo "<option value=''>Select Session</option>";
            echo "<option value='FN'>FN</option>";
            echo "<option value='AN'>AN</option>";
            echo "</select></li>";
        }
        echo "</ul>";
        echo "<button id='submitDates' class='submit-button'>Submit Dates</button>"; // Add submit button
    } else {
        echo "No courses found for the selected semester and branch.";
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room and Seating Details</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            display: flex;
            flex-direction: column;
            height: 100vh; /* Changed to height for fixed layout */
            background: url('index_bg.jpg') no-repeat center center/cover;
            position: relative;
            overflow: hidden; /* Prevent scrolling on body */
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
            overflow-y: auto; /* Enable scrolling in dashboard */
            max-height: calc(100vh - 140px); /* Adjust height for fixed layout */
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin: 2rem 0;
        }
        .button {
            background-color: #007BFF;
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 4px;
            font-size: 1.2rem;
            cursor: pointer;
            margin: 0 1rem;
            transition: background-color 0.3s, transform 0.2s;
        }

        .button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        .rooms-list {
            display: none; /* Initially hide the rooms list */
            gap: 1rem;
            overflow-y: auto;
            padding-right: 0.5rem;
        }

        .block-section {
            background: #fff;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .block-header {
            font-size: 1.5rem;
            font-weight: bold;
            color: #444;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #007BFF;
        }

        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .room-item {
            background: #f8f8f8;
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid #eee;
            transition: transform 0.2s;
        }

        .room-item:hover {
            transform: scale(1.02);
        }

        .room-number {
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .room-seats {
            color: #666;
            font-size: 0.9rem;
        }

        /* New styles for the course form */
        #courseForm {
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        #courseForm select {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
        }

        .date-input {
            margin-left: 0.5rem;
            padding: 0.5rem;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .submit-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-button:hover {
            background-color: #218838;
        }

        /* New styles for the Get Course Code button */
        button[type="submit"] {
            background-color: #007BFF; /* Blue background */
            color: white; /* White text */
            border: none; /* No border */
            padding: 0.8rem 1.5rem; /* Padding */
            border-radius: 4px; /* Rounded corners */
            font-size: 1rem; /* Font size */
            cursor: pointer; /* Pointer cursor */
            transition: background-color 0.3s, transform 0.2s; /* Transition effects */
        }

        button[type="submit"]:hover {
            background-color: #0056b3; /* Darker blue on hover */
            transform: scale(1.05); /* Slightly enlarge on hover */
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
            <div class="button-container">
                <button class="button" onclick="showRooms()">View Rooms</button>
                <button class="button" onclick="window.location.href='timetable_scheduling.php'">Schedule Timetable</button>
                <button class="button" onclick="window.location.href='seating.html'">Seating Arrangement</button>
            </div>
            <form id="courseForm">
                <select id="semester" required>
                    <option value="">Select Semester</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                    <option value="7">7</option>
                    <option value="8">8</option>
                </select>
                <select id="Branch" required>
                    <option value="">Select Branch</option>
                    <option value="CS">CS</option>
                    <option value="IT">IT</option>
                    <option value="EC">EC</option>
                    <option value="EE">EE</option>
                    <option value="ME">ME</option>
                </select>
                <button type="submit">Get Course Code</button>
            </form>
            <div class="result" id="result"></div>
        </div>
    </div>

    <script>
        document.getElementById('courseForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const semester = document.getElementById('semester').value;
            const Branch = document.getElementById('Branch').value;

            // AJAX request to fetch course codes from course_table
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'timetable_scheduling.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onload = function() {
                if (this.status === 200) {
                    document.getElementById('result').innerHTML = this.responseText;
                    addSubmitListener(); // Add listener for the submit button
                } else {
                    document.getElementById('result').innerHTML = 'Error fetching data.';
                }
            };
            xhr.send('semester=' + semester + '&Branch=' + Branch);
        });

        function addSubmitListener() {
            const submitButton = document.getElementById('submitDates');
            if (submitButton) {
                submitButton.addEventListener('click', function() {
                    const inputs = document.querySelectorAll('input[type="date"]');
                    const sessionSelects = document.querySelectorAll('select.session-select');
                    let allFilled = true;
                    const semester = document.getElementById('semester').value; // Get current semester value

                    inputs.forEach(input => {
                        if (!input.value) {
                            allFilled = false;
                        }
                    });

                    if (allFilled) {
                        // Create an array to store all promises
                        const promises = [];

                        // Send each date entry along with the selected session
                        inputs.forEach((input, index) => {
                            const courseCode = input.getAttribute('data-course');
                            const sessionValue = sessionSelects[index].value; // Get the selected session value
                            
                            // Only proceed if the session is either 'FN' or 'AN'
                            if (sessionValue === 'FN' || sessionValue === 'AN') {
                                const data = `sub=${encodeURIComponent(courseCode)}&sem=${encodeURIComponent(semester)}&date=${encodeURIComponent(input.value)}&time=${encodeURIComponent(sessionValue)}`;
                                
                                // Create a promise for each request
                                const promise = new Promise((resolve, reject) => {
                                    const xhr = new XMLHttpRequest();
                                    xhr.open('POST', 'enter_tt.php', true);
                                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                    xhr.onload = function() {
                                        if (this.status === 200) {
                                            resolve(this.responseText);
                                        } else {
                                            reject('Error submitting entry');
                                        }
                                    };
                                    xhr.onerror = function() {
                                        reject('Network error');
                                    };
                                    xhr.send(data);
                                });
                                promises.push(promise);
                            }
                        });

                        // Wait for all requests to complete
                        Promise.all(promises)
                            .then(results => {
                                alert('All entries submitted successfully!');
                                // Reset the form and result display
                                document.getElementById('courseForm').reset();
                                document.getElementById('result').innerHTML = '';
                            })
                            .catch(error => {
                                alert('Error submitting entries: ' + error);
                            });
                    } else {
                        alert('Please fill in all dates before submitting.');
                    }
                });
            }
        }
    </script>
</body>
</html>
