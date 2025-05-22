<?php
include 'config.php';
session_start();

if(isset($_POST['login_btn'])) {
    $userType = $_POST['user_type'];
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    
    if($userType === 'admin') {
        if($email === 'admin@admin.com' && $password === 'admin') {
            $_SESSION['email'] = $email;
            $_SESSION['user_type'] = 'admin';
            header('location:admin_dashboard.php');
            exit();
        }
    } else {
        $password = md5($password);
        $select = mysqli_query($conn, "SELECT * FROM account WHERE email = '$email' AND password = '$password'") or die('query failed');
        
        if(mysqli_num_rows($select) > 0) {
            $row = mysqli_fetch_assoc($select);
            $_SESSION['email'] = $row['email'];
            $_SESSION['user_type'] = 'user';
            header('location:home.php');
            exit();
        }
    }
    $_SESSION['login_error'] = 'Incorrect email or password!';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="CSS/style2.css">
    <title>Login</title>
    <style>
        .user-type-selector {
            margin-bottom: 20px;
            text-align: center;
        }
        .user-type-selector label {
            margin: 0 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="box form-box">
            <header>Login</header>
            <form action="" method="post">
                <div class="user-type-selector">
                    <label>
                        <input type="radio" name="user_type" value="user" checked> User
                    </label>
                    <label>
                        <input type="radio" name="user_type" value="admin"> Admin
                    </label>
                </div>
                
                <div class="field input">
                    <label for="email">Email</label>
                    <input type="email" name="email" autocomplete="off" id="email" required>
                </div>

                <div class="field input">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required>
                </div>

                <div class="field">
                    <button type="submit" class="button" name="login_btn">Login</button>
                </div>

                <?php
                if(isset($_SESSION['login_error'])) {
                    echo '<div class="error-message" style="color: red; padding: 20px;">' . $_SESSION['login_error'] . '</div>';
                    unset($_SESSION['login_error']);
                }
                ?>

                <div class="link">
                    Don't have an account? <a href="register.php">Register</a>
                    <br><br>
                    <a href="forgot_password.php">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>