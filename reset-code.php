<?php
session_start(); // Ensure session is active
require "connection.php";
$errors = array();

if (isset($_POST['check-reset-otp'])) {
    $otp = mysqli_real_escape_string($con, $_POST['otp']);
    
    $check_code = "SELECT * FROM credentials WHERE code = '$otp'";
    $code_res = mysqli_query($con, $check_code);

    if (mysqli_num_rows($code_res) > 0) {
        $fetch_data = mysqli_fetch_assoc($code_res);
        $email = $fetch_data['email'];

        $_SESSION['email'] = $email;
        $_SESSION['info'] = "Please create a new password.";

        // Debugging Step: Check if the script is executing till this point
        echo "Redirecting to new-password.php..."; 
        header('location: new-password.php'); 
        exit(); // Stop further execution
    } else {
        $errors['otp-error'] = "Invalid OTP!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Code Verification</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="reset-code.php" method="POST" autocomplete="off">
                    <h2 class="text-center">Code Verification</h2>
                    <?php if(isset($_SESSION['info'])){ ?>
                        <div class="alert alert-success text-center"><?php echo $_SESSION['info']; ?></div>
                    <?php } ?>
                    <?php if(count($errors) > 0){ ?>
                        <div class="alert alert-danger text-center">
                            <?php foreach($errors as $showerror){ echo $showerror; } ?>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <input class="form-control" type="number" name="otp" placeholder="Enter OTP" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control button" type="submit" name="check-reset-otp" value="Submit">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
