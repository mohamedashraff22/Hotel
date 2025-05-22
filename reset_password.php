<?php
include 'config.php';
session_start();

// Check if user is verified
if(!isset($_SESSION['verified']) || $_SESSION['verified'] !== true) {
    header('location: forgot_password.php');
    exit();
}

if(isset($_POST['reset_password'])) {
    $new_password = mysqli_real_escape_string($conn, md5($_POST['new_password']));
    $confirm_password = mysqli_real_escape_string($conn, md5($_POST['confirm_password']));
    $email = $_SESSION['reset_email'];

    if($new_password === $confirm_password) {
        $update = mysqli_query($conn, "UPDATE account SET password = '$new_password' WHERE email = '$email'") or die('query failed');
        if($update) {
            $_SESSION['success_message'] = 'Password successfully reset! Please login with your new password.';
            // Clear reset sessions
            unset($_SESSION['verified']);
            unset($_SESSION['reset_email']);
            header('location: login.php');
            exit();
        }
    } else {
        $_SESSION['reset_error'] = 'Passwords do not match!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/style2.css">
    <title>Reset Password</title>
    <style>
        .error-message {
            color: red;
            padding: 10px;
            margin: 10px 0;
            background: #ffebee;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Set New Password</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>

                <div class="field input">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>

                <?php
                if(isset($_SESSION['reset_error'])) {
                    echo '<div class="error-message">' . $_SESSION['reset_error'] . '</div>';
                    unset($_SESSION['reset_error']);
                }
                ?>

                <div class="field">
                    <button type="submit" class="button" name="reset_password">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>