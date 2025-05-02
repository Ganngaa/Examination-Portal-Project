<?php 
session_start();
require "connection.php";
$errors = array();

if (!isset($_SESSION['email'])) {
    header('Location: index.html');
    exit();
}

if (isset($_POST['change-password'])) {
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    if ($password !== $cpassword) {
        $errors['password'] = "Passwords do not match!";
    } else {
       
        $email = $_SESSION['email'];
        
        $update_pass = "UPDATE credentials SET password = '$password', code = NULL WHERE email = '$email'";
        $run_query = mysqli_query($con, $update_pass);

        if ($run_query) {
            $_SESSION['info'] = "Your password has been changed successfully!";
            header('location: index.html');
            exit();
        } else {
            $errors['db-error'] = "Failed to change your password!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create a New Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-md-4 offset-md-4 form">
                <form action="new-password.php" method="POST" autocomplete="off">
                    <h2 class="text-center">New Password</h2>
                    <?php if(isset($_SESSION['info'])){ ?>
                        <div class="alert alert-success text-center"><?php echo $_SESSION['info']; ?></div>
                    <?php } ?>
                    <?php if(count($errors) > 0){ ?>
                        <div class="alert alert-danger text-center">
                            <?php foreach($errors as $showerror){ echo $showerror; } ?>
                        </div>
                    <?php } ?>
                    <div class="form-group">
                        <input class="form-control" type="password" name="password" placeholder="Create new password" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control" type="password" name="cpassword" placeholder="Confirm your password" required>
                    </div>
                    <div class="form-group">
                        <input class="form-control button" type="submit" name="change-password" value="Change Password">
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
