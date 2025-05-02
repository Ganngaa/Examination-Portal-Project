<?php 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
//session_start();
require "connection.php";
$email = "";
$name = "";
$errors = array();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Load PHPMailer

// Function to send email using PHPMailer
function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // SMTP configuration
        $mail->SMTPDebug = 2;  // Set to 0 for production
$mail->Debugoutput = 'html';

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'gnganilkumar@gmail.com'; // Your Gmail
        $mail->Password = 'fkpb owfh ctep xrea'; // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('gnganilkumar@gmail.com', 'University Examination Portal');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}

// User signup
if (isset($_POST['signup'])) {
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    if ($password !== $cpassword) {
        $errors['password'] = "Confirm password does not match!";
    }

    $email_check = "SELECT * FROM credentials WHERE email = '$email'";
    $res = mysqli_query($con, $email_check);

    if (mysqli_num_rows($res) > 0) {
        $errors['email'] = "Email already exists!";
    }

    if (count($errors) === 0) {
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $code = rand(999999, 111111);
        $status = "notverified";

        $insert_data = "INSERT INTO credentials (name, email, password, code, status)
                        VALUES ('$name', '$email', '$encpass', '$code', '$status')";
        $data_check = mysqli_query($con, $insert_data);

        if ($data_check) {
            $subject = "Email Verification Code";
            $message = "<p>Your verification code is <strong>$code</strong></p>";

            if (sendMail($email, $subject, $message)) {
                $_SESSION['info'] = "We've sent a verification code to your email - $email";
                $_SESSION['email'] = $email;
                $_SESSION['password'] = $password;
                header('location: user-otp.php');
                exit();
            } else {
                $errors['otp-error'] = "Failed to send verification email!";
            }
        } else {
            $errors['db-error'] = "Failed to insert data into database!";
        }
    }
}

// If user clicks "Forgot Password"
if (isset($_POST['check-email'])) {
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $check_email = "SELECT * FROM credentials WHERE email='$email'";
    $run_sql = mysqli_query($con, $check_email);

    if (mysqli_num_rows($run_sql) > 0) {
        $code = rand(999999, 111111);
        $insert_code = "UPDATE credentials SET code = $code WHERE email = '$email'";
        $run_query = mysqli_query($con, $insert_code);

        if ($run_query) {
            $subject = "Password Reset Code";
            $message = "<p>Your password reset code is <strong>$code</strong></p>";

            if (sendMail($email, $subject, $message)) {
                $_SESSION['info'] = "We've sent a password reset OTP to your email - $email";
                $_SESSION['email'] = $email;
                header('location: reset-code.php');
                exit();
            } else {
                $errors['otp-error'] = "Failed to send reset code!";
            }
        } else {
            $errors['db-error'] = "Something went wrong!";
        }
    } else {
        $errors['email'] = "This email address does not exist!";
    }
}
?>
