<?php
$host = "localhost";
$user = "root";
$pass = "";
$db_name = "login";

$con = mysqli_connect($host, $user, $pass, $db_name);
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
