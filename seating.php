<?php
// Database connection
$conn = new mysqli("localhost", "root", "", "login");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch rooms and capacities
$room_sql = "SELECT room_no, seats, block FROM rooms ORDER BY block, room_no";
$room_result = $conn->query($room_sql);
$seating_arr = [];
$block_rooms = [];

while ($room = $room_result->fetch_assoc()) {
    $room_no = $room['room_no'] ?? 'Unknown Room';
    $block = $room['block'] ?? 'Unknown Block';
    $seats = (int)($room['seats'] ?? 0);

    if ($seats > 0) {
        $seating_arr[$block][$room_no] = [
            'seats' => $seats,
            'students' => array_fill(0, floor($seats / 2), [null, null])
        ];
        $block_rooms[$block][] = $room_no;
    }
}

$exam_date = "";
$exam_time = "";
$students_by_subject = [];
$assigned_students_global = []; // Track all assigned students globally

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_date = date('d-m-Y', strtotime(str_replace('/', '-', $_POST['exam_date'])));
    $exam_time = $_POST['exam_time'];
    $formatted_date = date('Y-m-d', strtotime($exam_date));
    
    // First, clear any existing seating data
    $clear_sql = "DELETE FROM seating";
    $conn->query($clear_sql);
    
    // Fetch subjects and students from timetable for the selected date and time
    $timetable_sql = "SELECT DISTINCT t.sub, t.sem
                      FROM timetable t 
                      WHERE DATE_FORMAT(t.date, '%d-%m-%Y') = ? AND t.time = ?";
    $stmt = $conn->prepare($timetable_sql);
    $stmt->bind_param("ss", $exam_date, $exam_time);
    $stmt->execute();
    $timetable_result = $stmt->get_result();

    while ($row = $timetable_result->fetch_assoc()) {
        $course_code = $row['sub'];
        $semester = $row['sem'];
        
        // Fetch students for each subject
        $student_sql = "SELECT p.reg_no, p.Branch 
                       FROM pdf_files p 
                       JOIN course_table c ON p.Branch = c.Branch 
                       WHERE c.semester = ? AND c.course_code = ? 
                       AND (p.reg_no LIKE 'IDK%' OR p.reg_no LIKE 'LIDK%')";
        $stmt = $conn->prepare($student_sql);
        $stmt->bind_param("ss", $semester, $course_code);
        $stmt->execute();
        $student_result = $stmt->get_result();

        $students_by_subject[$course_code] = [];
        while ($student = $student_result->fetch_assoc()) {
            $students_by_subject[$course_code][] = $student;
        }
    }

    if (empty($students_by_subject)) {
        die("No subjects found for the selected date and time.");
    }

    // Create a flat array of all students with their subject codes and branches
    $all_students = [];
    foreach ($students_by_subject as $subject => $students) {
        foreach ($students as $student) {
            $all_students[] = [
                'reg_no' => $student['reg_no'],
                'subject' => $subject,
                'branch' => $student['Branch']
            ];
        }
    }

    // Sort students by subject first, then by reg_no
    usort($all_students, function($a, $b) {
        $subjectCompare = strcmp($a['subject'], $b['subject']);
        if ($subjectCompare === 0) {
            return strcmp($a['reg_no'], $b['reg_no']);
        }
        return $subjectCompare;
    });

    // Calculate total students
    $total_students = count($all_students);
    
    // Calculate total available seats
    $total_seats = 0;
    foreach ($seating_arr as $block => $rooms) {
        foreach ($rooms as $room_info) {
            $total_seats += $room_info['seats'];
        }
    }

    if ($total_students > $total_seats) {
        die("Not enough seats available for all students. Total students: $total_students, Total seats: $total_seats");
    }

    // Group students by subject
    $subject_groups = [];
    foreach ($all_students as $student) {
        $subject_groups[$student['subject']][] = $student;
    }
    
    // Interleave students from different subjects
    $interleaved_students = [];
    $max_group_size = max(array_map('count', $subject_groups));
    
    for ($i = 0; $i < $max_group_size; $i++) {
        foreach ($subject_groups as $subject => $students) {
            if (isset($students[$i])) {
                $interleaved_students[] = $students[$i];
            }
        }
    }
    
    // Assign students to seats
    $student_index = 0;
    $student_count = count($interleaved_students);
    
    // Create a queue of available benches across all rooms
    $all_benches = [];
    foreach ($block_rooms as $block => $rooms) {
        foreach ($rooms as $room_no) {
            $bench_count = floor($seating_arr[$block][$room_no]['seats'] / 2);
            for ($bench = 0; $bench < $bench_count; $bench++) {
                $all_benches[] = ['block' => $block, 'room_no' => $room_no, 'bench' => $bench];
            }
        }
    }
    
    // Assign students to benches one by one
    foreach ($all_benches as $bench_info) {
        if ($student_index >= $student_count) {
            break;
        }
        
        $block = $bench_info['block'];
        $room_no = $bench_info['room_no'];
        $bench = $bench_info['bench'];
        
        // Assign left seat (Seat 1)
        if (!isset($seating_arr[$block][$room_no]['students'][$bench][0])) {
            // Find next unassigned student
            while ($student_index < $student_count && 
                   in_array($interleaved_students[$student_index]['reg_no'], $assigned_students_global)) {
                $student_index++;
            }
            
            if ($student_index >= $student_count) {
                break;
            }
            
            $student = $interleaved_students[$student_index];
            $seating_arr[$block][$room_no]['students'][$bench][0] = $student['reg_no'] . 
                ' (' . $student['subject'] . ')';
            $assigned_students_global[] = $student['reg_no'];
            $student_index++;
        }
        
        // Assign right seat (Seat 2) if available and students remain
        if ($student_index < $student_count && 
            !isset($seating_arr[$block][$room_no]['students'][$bench][1])) {
            // Find next student with different subject than left seat
            $left_subject = $seating_arr[$block][$room_no]['students'][$bench][0] ? 
                substr($seating_arr[$block][$room_no]['students'][$bench][0], 
                strpos($seating_arr[$block][$room_no]['students'][$bench][0], '(') + 1, -1) : 
                null;
            
            $original_index = $student_index;
            $found = false;
            
            while ($student_index < $student_count) {
                $student = $interleaved_students[$student_index];
                if ($student['subject'] !== $left_subject && 
                    !in_array($student['reg_no'], $assigned_students_global)) {
                    $seating_arr[$block][$room_no]['students'][$bench][1] = $student['reg_no'] . 
                        ' (' . $student['subject'] . ')';
                    $assigned_students_global[] = $student['reg_no'];
                    $student_index++;
                    $found = true;
                    break;
                }
                $student_index++;
            }
            
            if (!$found) {
                $student_index = $original_index;
            }
        }
    }
    
    // Prepare statement for inserting seating data
    $insert_seating_sql = "INSERT INTO seating (room_no, date, bench, seat_1, seat_2, block, time) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_seating_sql);
    
    // Save the seating arrangement to the database
    foreach ($seating_arr as $block => $rooms) {
        foreach ($rooms as $room_no => $room_info) {
            foreach ($room_info['students'] as $bench => $seats) {
                $seat_1 = $seats[0] ?? null;
                $seat_2 = $seats[1] ?? null;
                $bench_num = $bench + 1;
                
                $reg_no_1 = null;
                $reg_no_2 = null;
                
                if ($seat_1) {
                    $reg_no_1 = substr($seat_1, 0, strpos($seat_1, ' ('));
                }
                
                if ($seat_2) {
                    $reg_no_2 = substr($seat_2, 0, strpos($seat_2, ' ('));
                }
                
                if ($reg_no_1 !== null || $reg_no_2 !== null) {
                    $insert_stmt->bind_param("ssissss", $room_no, $formatted_date, $bench_num, $reg_no_1, $reg_no_2, $block, $exam_time);
                    $insert_stmt->execute();
                }
            }
        }
    }
    
    // Assignment statistics
    $assigned_count = count($assigned_students_global);
    $unassigned_count = $total_students - $assigned_count;
    
    if ($unassigned_count > 0) {
        error_log("Warning: $unassigned_count students could not be assigned seats.");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seating Arrangement</title>
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
            overflow-y: auto;
            max-height: calc(100vh - 100px);
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }

        th {
            background-color:rgb(14, 14, 14);
            color: white;
        }

        .download-btn {
            background-color:rgb(15, 16, 15);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .download-btn:hover {
            background-color:rgb(31, 31, 31);
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
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
            <i style="font-size: 2rem;">💺</i>
                Seating
            </a>
            <a href="exm_view_attendance.php" class="menu-item">
            <i style="font-size: 2rem;">📋</i>
                Attendance
            </a>
            <a href="invg_req.php" class="menu-item">
            <i style="font-size: 2rem;">👨‍🏫</i>
                Invigilator
            </a>
            <a href="exm_coord_view_report.php" class="menu-item">
            <i style="font-size: 2rem;">📊</i>
                Report
            </a>
        </div>
        <div class="dashboard">
            <div class="dashboard-header">
                <h2>Seating Arrangement</h2>
                <p>Date of Examination: <?= htmlspecialchars($exam_date) ?></p>
                <p>Time: <?= htmlspecialchars($exam_time) ?></p>
                
                <button class="download-btn" onclick="downloadPDF()">Download PDF</button>
            </div>

            <div id="seatingTable">
            <?php foreach ($seating_arr as $block => $rooms): ?>
    <?php 
        $block_has_students = false;
        foreach ($rooms as $room_no => $room_info) {
            foreach ($room_info['students'] as $seats) {
                if (!empty($seats[0]) || !empty($seats[1])) {
                    $block_has_students = true;
                    break 2; // Exit both loops
                }
            }
        }
    ?>

    <?php if ($block_has_students): ?>
        <h3>Block: <?= htmlspecialchars($block) ?></h3>
        <?php foreach ($rooms as $room_no => $room_info): ?>
            <?php 
                $room_has_students = false;
                foreach ($room_info['students'] as $seats) {
                    if (!empty($seats[0]) || !empty($seats[1])) {
                        $room_has_students = true;
                        break;
                    }
                }
            ?>

            <?php if ($room_has_students): ?>
                <h4>Room: <?= htmlspecialchars($room_no) ?> (Capacity: <?= $room_info['seats'] ?>)</h4>
                <table>
                    <tr><th>Bench</th><th>Seat 1 (Left)</th><th>Seat 2 (Right)</th></tr>
                    <?php foreach ($room_info['students'] as $bench => $seats): ?>
                        <?php if (!empty($seats[0]) || !empty($seats[1])): ?>
                            <tr>
                                <td><?= $bench + 1 ?></td>
                                <td><?= htmlspecialchars($seats[0] ?? '-') ?></td>
                                <td><?= htmlspecialchars($seats[1] ?? '-') ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>

            </div>
        </div>
    </div>

    <script>
       function downloadPDF() {
    const element = document.getElementById('seatingTable');
    const date = new Date().toLocaleDateString().replace(/\//g, "-");

    // Create a wrapper div to include the exam details
    const pdfContent = document.createElement('div');

    // Fetch exam date and time from PHP-generated content
    const examDate = "<?= htmlspecialchars($exam_date) ?>";
    const examTime = "<?= htmlspecialchars($exam_time) ?>";

    // Create header for PDF
    const header = document.createElement('div');
    header.innerHTML = `
        <h2>Seating Arrangement</h2>
        <p><strong>Date of Examination:</strong> ${examDate}</p>
        <p><strong>Time:</strong> ${examTime}</p>
        <hr>
    `;
    pdfContent.appendChild(header);

    // Clone the seating table and append it to pdfContent
    const clonedTable = element.cloneNode(true);
    pdfContent.appendChild(clonedTable);

    // Convert the updated content to PDF
    const opt = {
        margin: 1,
        filename: `seating_arrangement_${examDate}_${examTime}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(pdfContent).save();
}

    </script>
</body>
</html>

