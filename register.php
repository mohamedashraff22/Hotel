<?php
include 'config.php';
session_start();

function checkPasswordStrength($password) {
    if (strlen($password) < 8) return "Password must be at least 8 characters long";
    if (!preg_match("/[A-Z]/", $password)) return "Password must contain at least one uppercase letter";
    if (!preg_match("/[a-z]/", $password)) return "Password must contain at least one lowercase letter";
    if (!preg_match("/[0-9]/", $password)) return "Password must contain at least one number";
    if (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) return "Password must contain at least one special character";
    return "";
}

if(isset($_POST['submit'])){
    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $balance = mysqli_real_escape_string($conn, $_POST['balance']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $security_question = mysqli_real_escape_string($conn, $_POST['security_question']);
    $security_answer = mysqli_real_escape_string($conn, $_POST['security_answer']);

    $passwordError = checkPasswordStrength($password);
    $hasError = false;
    
    if (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $_SESSION['register_error'] = 'Invalid email format!';
        $_SESSION['error_field'] = 'email';
        $hasError = true;
    } 
    elseif (mysqli_num_rows(mysqli_query($conn, "SELECT * FROM account WHERE email = '$email'")) > 0) {
        $_SESSION['register_error'] = 'Email already exists!';
        $_SESSION['error_field'] = 'email';
        $hasError = true;
    }
    elseif ($passwordError !== "") {
        $_SESSION['register_error'] = $passwordError;
        $_SESSION['error_field'] = 'password';
        $hasError = true;
    }
    elseif ($password !== $cpassword) {
        $_SESSION['register_error'] = 'Passwords do not match!';
        $_SESSION['error_field'] = 'cpassword';
        $hasError = true;
    }

    if ($hasError) {
        $_SESSION['form_data'] = $_POST;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    $hashed_password = md5($password);
    mysqli_query($conn, "INSERT INTO account (email, password, security_question, security_answer) VALUES ('$email', '$hashed_password', '$security_question', '$security_answer')") or die('account query failed');
    mysqli_query($conn, "INSERT INTO customer (fname, lname, phone, country, email, balance) 
                        VALUES ('$fname', '$lname', '$phone', '$country', '$email', '$balance')") 
                        or die('customer query failed');

    $_SESSION['register_success'] = 'Registered successfully!';
    header('location: login.php');
    exit();
}

$form_data = isset($_SESSION['form_data']) ? $_SESSION['form_data'] : array();
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta http-equiv="X-UA-Compatible" content="IE=edge">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Register</title>
   <link rel="stylesheet" href="css/style2.css">
   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
   <style>
        .password-requirements {
            margin-top: 10px;
            font-size: 0.9em;
        }
        .password-requirements ul {
            list-style-type: none;
            padding-left: 20px;
            margin-top: 5px;
        }
        .password-requirements li {
            color: red;
            margin: 3px 0;
        }
   </style>
</head>
<body>

<div class="container">
    <div class="box form-box">
        <header>Registration</header>
        <form action="" method="post">
            <div class="field input">
                <label for="fname">First Name</label>
                <input type="text" name="fname" id="fname" value="<?php echo isset($form_data['fname']) ? htmlspecialchars($form_data['fname']) : ''; ?>" required>
            </div>
            <div class="field input">
                <label for="lname">Last Name</label>
                <input type="text" name="lname" id="lname" value="<?php echo isset($form_data['lname']) ? htmlspecialchars($form_data['lname']) : ''; ?>" required>
            </div>
            <div class="field input">
                <label class="phone-label">Phone Number</label>
                <div class="phone-input-container">
                    <input type="tel" name="phone" id="phone" value="<?php echo isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : ''; ?>" required>
                </div>
            </div>
            <div class="field input">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>" required>
            </div>
            <div class="field input">
                <label for="balance">Balance</label>
                <input type="text" name="balance" id="balance" value="<?php echo isset($form_data['balance']) ? htmlspecialchars($form_data['balance']) : ''; ?>" required>
            </div>
            <div class="field input">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
                <div id="password-requirements" class="password-requirements">
                    Password must contain:
                    <ul>
                        <li style="display:inline" id="length">At least 8 characters</li>
                        <li style="display:inline" id="uppercase">One uppercase letter</li>    
                        <li style="display:inline" id="lowercase">One lowercase letter</li>
                        <li style="display:inline" id="number">One number</li>
                        <li style="display:inline" id="special">One special character</li>
                    </ul>
                </div>
            </div>
            <div class="field input">
                <label for="cpassword">Confirm Password</label>
                <input type="password" name="cpassword" id="cpassword" required>
            </div>
            <div class="field input">
                <label for="country">Country</label>
                <select name="country" id="country" required>
                    <option value="" disabled>Select your country</option>
                    <?php
                    $countries = array("United States", "United Kingdom", "Egypt", "India");
                    foreach($countries as $c) {
                        $selected = (isset($form_data['country']) && $form_data['country'] === $c) ? 'selected' : '';
                        echo "<option value=\"$c\" $selected>$c</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="field input">
                <label for="security_question">Security Question</label>
                <select name="security_question" required>
                    <?php
                    $questions = array(
                        "What is your favorite color?",
                        "What is your mother's maiden name?",
                        "What was your first pet's name?",
                        "What city were you born in?"
                    );
                    foreach($questions as $q) {
                        $selected = (isset($form_data['security_question']) && $form_data['security_question'] === $q) ? 'selected' : '';
                        echo "<option value=\"$q\" $selected>$q</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="field input">
                <label for="security_answer">Security Answer</label>
                <input type="text" name="security_answer" value="<?php echo isset($form_data['security_answer']) ? htmlspecialchars($form_data['security_answer']) : ''; ?>" required>
            </div>
            <div class="field">
                <button type="submit" class="button" name="submit">Register</button>
            </div>

            <?php
                if(isset($_SESSION['register_error'])){
                    echo '<div class="message error-message" style="color: red; padding: 20px;">'.$_SESSION['register_error'].'</div>';
                    unset($_SESSION['register_error']);
                }
                if(isset($_SESSION['register_success'])){
                    echo '<div class="message success-message" style="color: green; padding: 20px;">'.$_SESSION['register_success'].'</div>';
                    unset($_SESSION['register_success']);
                }
            ?>

            <div class="link">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
<script>
    const phoneInput = document.querySelector("#phone");
    const iti = window.intlTelInput(phoneInput, {
        initialCountry: "us",
        separateDialCode: true,
        preferredCountries: ["us", "gb", "eg", "in"],
        utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });

    phoneInput.addEventListener("countrychange", function() {
        const countryData = iti.getSelectedCountryData();
        const countrySelect = document.querySelector("#country");
        const countryOptions = Array.from(countrySelect.options);
        
        const matchingOption = countryOptions.find(option => 
            option.text.toLowerCase().includes(countryData.name.toLowerCase())
        );
        
        if (matchingOption) {
            countrySelect.value = matchingOption.value;
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        const fullNumber = iti.getNumber();
        if (!iti.isValidNumber()) {
            e.preventDefault();
            alert('Please enter a valid phone number.');
            return;
        }
        phoneInput.value = fullNumber;
    });

    document.getElementById('password').addEventListener('input', function() {
        const password = this.value;
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*(),.?":{}|<>]/.test(password)
        };

        for (const [requirement, met] of Object.entries(requirements)) {
            const element = document.getElementById(requirement);
            if (element) {
                element.style.color = met ? 'green' : 'red';
            }
        }
    });

    document.getElementById('cpassword').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (confirmPassword !== password) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });

    if (<?php echo isset($_SESSION['error_field']) ? 'true' : 'false'; ?>) {
        const errorField = '<?php echo isset($_SESSION['error_field']) ? $_SESSION['error_field'] : ''; ?>';
        if (errorField) {
            document.getElementById(errorField).value = '';
            document.getElementById(errorField).focus();
        }
        <?php unset($_SESSION['error_field']); ?>
    }
</script>

</body>
</html>