<?php
include 'config.php';
session_start();

if(isset($_POST['verify_details'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $security_question = mysqli_real_escape_string($conn, $_POST['security_question']);
    $security_answer = mysqli_real_escape_string($conn, $_POST['security_answer']);
    
    $select = mysqli_query($conn, "SELECT * FROM account WHERE email = '$email' AND security_question = '$security_question' AND security_answer = '$security_answer'") or die('query failed');
    
    if(mysqli_num_rows($select) > 0) {
        $_SESSION['reset_email'] = $email;
        $_SESSION['verified'] = true;
        header('location: reset_password.php');
    } else {
        $_SESSION['recovery_error'] = 'Incorrect details!';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/style2.css">
    <title>Forgot Password</title>
    <style>
        .success-message {
            color: green;
            padding: 10px;
            margin: 10px 0;
            background: #e8f5e9;
            border-radius: 5px;
        }
        .error-message {
            color: red;
            padding: 10px;
            margin: 10px 0;
            background: #ffebee;
            border-radius: 5px;
        }
        select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="box form-box">
            <header>Reset Password</header>
            <form action="" method="post">
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" required>
                </div>

                <div class="field input">
                    <label for="security_question">Security Question</label>
                    <select name="security_question" required>
                        <option value="What is your favorite color?">What is your favorite color?</option>
                        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                        <option value="What was your first pet's name?">What was your first pet's name?</option>
                        <option value="What city were you born in?">What city were you born in?</option>
                    </select>
                </div>

                <div class="field input">
                    <label for="security_answer">Answer</label>
                    <input type="text" name="security_answer" id="security_answer" required>
                </div>

                <?php
                if(isset($_SESSION['recovery_error'])) {
                    echo '<div class="error-message">' . $_SESSION['recovery_error'] . '</div>';
                    unset($_SESSION['recovery_error']);
                }
                ?>

                <div class="field">
                    <button type="submit" class="button" name="verify_details">Verify Details</button>
                </div>
                <div class="link">
                    Remember your password? <a href="login.php">Login</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>