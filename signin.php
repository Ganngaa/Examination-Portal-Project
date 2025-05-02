<?php
    if (isset($_POST['id']) && isset($_POST['password'])) {
        $id = $_POST['id'];
        $password = $_POST['password'];
    } else {
        $id = "";
        $password = "";
    }

    
    $conn = new mysqli("localhost", "root", "", "login");




    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Login validation
    if(isset($_POST['login'])) {
        // Sanitize inputs
        $id = mysqli_real_escape_string($conn, $id);
        $password = mysqli_real_escape_string($conn, $password);


        // Query database
        $sql = "SELECT * FROM credentials WHERE id = '$id' && password = '$password'";
        $result = mysqli_query($conn, $sql);


        if(mysqli_num_rows($result) == 1) {
            // Successful login
            session_start();
            if($id === 'EXM01CRD01') {
                $_SESSION['coordinator_id'] = $id;
                header("Location: exmcoord_home.php");
            } elseif(substr($id, 0, 3) === 'INV') {
                $_SESSION['invigilator_id'] = $id;
                header("Location: invg_dashboard.php");
            } elseif(substr($id, 0, 3) === 'DEP') {
                $_SESSION['dept_co_id'] = $id;
                header("Location: dept_co.php");
            }
            elseif($id === 'KTU01CRD02') {
                $_SESSION['university_id'] = $id;
                header("Location: un_co.php");
            } else {
                $_SESSION['student_id'] = $id;
                header("Location: student_home.php");
            }
            exit();
        } else {
            // Failed login
            header("Location: index.html?error=invalid");
            exit();
        }
    }

    // Close connection
    $conn->close();
?>