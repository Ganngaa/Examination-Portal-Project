<?php
session_start();
$conn = new mysqli("localhost", "root", "", "login");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get invigilator numbers and days for each department
$cse_fn = $_POST['cse_inv_fn'];
$cse_an = $_POST['cse_inv_an'];
$cse_day = $_POST['cse_day'];

$mech_fn = $_POST['mech_inv_fn'];
$mech_an = $_POST['mech_inv_an']; 
$mech_day = $_POST['mech_day'];

$ece_fn = $_POST['ece_inv_fn'];
$ece_an = $_POST['ece_inv_an'];
$ece_day = $_POST['ece_day'];

$eee_fn = $_POST['eee_inv_fn'];
$eee_an = $_POST['eee_inv_an'];
$eee_day = $_POST['eee_day'];

$it_fn = $_POST['it_inv_fn'];
$it_an = $_POST['it_inv_an'];
$it_day = $_POST['it_day'];

// Update requirements table for each department
$stmt = $conn->prepare("UPDATE requirements SET FN = ?, AN = ?, day = ? WHERE dept = ?");

// Update CSE
$dept = "CSE";
$stmt->bind_param("iiss", $cse_fn, $cse_an, $cse_day, $dept);
$stmt->execute();

// Update MECH
$dept = "MECH";
$stmt->bind_param("iiss", $mech_fn, $mech_an, $mech_day, $dept);
$stmt->execute();

// Update ECE
$dept = "ECE";
$stmt->bind_param("iiss", $ece_fn, $ece_an, $ece_day, $dept);
$stmt->execute();

// Update EEE
$dept = "EEE";
$stmt->bind_param("iiss", $eee_fn, $eee_an, $eee_day, $dept);
$stmt->execute();

// Update IT
$dept = "IT";
$stmt->bind_param("iiss", $it_fn, $it_an, $it_day, $dept);
$stmt->execute();

$stmt->close();
$conn->close();

header("Location: exm_inv.html");
exit();
?>
