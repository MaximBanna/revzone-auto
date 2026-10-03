<?php
require('nav.php');

$fnameError = $lnameError = $usernameError = $emailError = $phoneError =  '';
$addressError = $passError = $confirmPassError = $successMessage = $dobError = '';

if (isset($_POST['submit'])) {
    $fname = isset($_POST['fname']) ? $_POST['fname'] : '';
    $lname = isset($_POST['lname']) ? $_POST['lname'] : '';
    $username = isset($_POST['user']) ? $_POST['user'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $address = isset($_POST['address']) ? $_POST['address'] : '';
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';
    $cpass = isset($_POST['cpass']) ? $_POST['cpass'] : '';
    $dateOfBirth = isset($_POST['dob']) ? $_POST['dob'] : '';

    // Validate first name
    if (!preg_match("/^[a-zA-Z]{3,}+$/", $fname)) {
        $fnameError = "Please enter a valid first name (at least 3 letters).";
    }

    // Validate last name
    if (!preg_match("/^[a-zA-Z]{3,}+$/", $lname)) {
        $lnameError = "Please enter a valid last name (at least 3 letters).";
    }

    //validate date of birth
    if (empty($dateOfBirth)) {
        $dobError = "Please enter your date of birth.";
    } else {
        // must be born at least 18 years ago (checks day and month too)
        if (strtotime($dateOfBirth) > strtotime('-18 years')) {
            $dobError = "You must be at least 18 years old.";
        }
    }

    // Validate username
    if (!preg_match("/^[a-zA-Z0-9_]{3,}+$/", $username)) {
        $usernameError = "Please enter a valid username (at least 3 chars).";
    } else {
        $result = mysqli_execute_query($conn, "SELECT username FROM users WHERE username = ?", [$username]);
        $row = mysqli_fetch_array($result);
        if ($row) {
            $usernameError = "Username already exists.";
        }
    }

    // Validate email
    if (empty($email) || !preg_match("/^[^\s@]+@[^\s@]+\.[^\s@]+$/", $email)) {
        $emailError = "Please enter a valid email address like example@example.com.";
    } else {
        $result = mysqli_execute_query($conn, "SELECT email FROM users WHERE email = ?", [$email]);
        $row = mysqli_fetch_array($result);
        if ($row) {
            $emailError = "Email already exists.";
        }
    }

    // Validate phone
    if (!preg_match("/^\+?[0-9]{10,15}$/", $phone)) {
        $phoneError = "Please enter a valid phone number.";
    }

    // Validate address
    if (empty($address)) {
        $addressError = "Please enter your address.";
    }

    // Validate password
    if (strlen($pass) < 6) {
        $passError = "Password must be at least 6 characters long.";
    }

    // Confirm password
    if ($pass !== $cpass) {
        $confirmPassError = "Passwords do not match.";
    }

    // If all fields are valid we can add the user the site
    if (
        empty($fnameError) && empty($lnameError) && empty($usernameError) && empty($emailError) && empty($phoneError) &&
        empty($addressError) && empty($passError) && empty($confirmPassError) && empty($dobError)
    ) {

        $hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_execute_query($conn, "INSERT INTO `users`
             (`username`, `fname`, `lname`, birth_date,`role`, `address`, `phone`, `email`, `password`, `pass3`, `pass2`, `pass1` ,`changeP`, `blocked`)
              VALUES (?, ?, ?, ?, '0', ?, ?, ?, ?, ?, ?, ?, '0', '0')", [$username, $fname, $lname, $dateOfBirth, $address, $phone, $email, $hash, $hash, $hash, $hash]);

        $mainUrl = SITE_URL . "/logIn.php";
        $subject = "Welcome to Revzone 🎉";
        $message  = "<b>Hello $username,</b>";
        $message .= "<p>Welcome to <strong>Revzone</strong>! We're excited to have you on board.</p>";
        $message .= "<p>Your account has been successfully created.</p>";
        $message .= "
            <p>
                You can log in to your account here:
                <br>
                <a href='$mainUrl' 
                style='display:inline-block;
                        background:#F2F2F2;
                        color:#0D0D0D;
                        padding:10px 15px;
                        text-decoration:none;
                        border-radius:5px;'>
                    Login to Revzone
                </a>
            </p>";
        $message .= "<p>If you did not create this account, please contact our support team immediately.</p>";
        $message .= "<p>Thank you,<br>The Revzone Account Team</p>";

        $header  = "From: Revzone <" . MAIL_FROM . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=UTF-8\r\n";
        $retval = mail($email, $subject, $message, $header);

        $successMessage = "User registered successfully! you can proceed to login";
    }
    CloseCon($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RevZone Sign Up</title>
    <link rel="stylesheet" href="signUpStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <form id="signup" method="post">
        <h2>Sign Up</h2>

        <div class="sucess"><?php if ($successMessage !== '') echo "<p>$successMessage</p>"; ?></div>

        <div class="input">
            <label for="fname">First Name</label>
            <input name='fname' id="fname" type="text" placeholder="First Name" required>
            <div class="error"><?php if (isset($_POST['submit']) && $fnameError !== '') echo $fnameError; ?> </div>
        </div>

        <div class="input">
            <label for="lname">Last Name</label>
            <input name='lname' id="lname" type="text" placeholder="Last Name" required>
            <div class="error"><?php if (isset($_POST['submit']) && $lnameError !== '') echo $lnameError; ?> </div>
        </div>

        <div class="input">
            <label for="dob">Date of Birth</label>
            <input name="dob" id="dob" type="date" min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" required>
            <div class="error"><?php if (isset($_POST['submit']) && $dobError !== '') echo $dobError; ?></div>
        </div>

        <div class="input">
            <label for="user">Username</label>
            <input name='user' id="user" type="text" placeholder="Username" required>
            <div class="error"><?php if (isset($_POST['submit']) && $usernameError !== '') echo $usernameError; ?> </div>
        </div>

        <div class="input">
            <label for="email">Email</label>
            <input name='email' id="email" type="email" placeholder="Email" required>
            <div class="error"><?php if (isset($_POST['submit']) && $emailError !== '') echo $emailError; ?> </div>
        </div>

        <div class="input">
            <label for="phone">Phone Number</label>
            <input name='phone' id="phone" type="tel" placeholder="Phone Number" required>
            <div class="error"><?php if (isset($_POST['submit']) && $phoneError !== '') echo $phoneError; ?> </div>
        </div>

        <div class="input">
            <label for="address">Address</label>
            <input name='address' id="address" type="text" placeholder="Address" required>
            <div class="error"><?php if (isset($_POST['submit']) && $addressError !== '') echo $addressError; ?> </div>
        </div>

        <div class="input">
            <label for="password">Password</label>
            <input name='pass' id="password" type="password" placeholder="Password" required>
            <div class="error"><?php if (isset($_POST['submit']) && $passError !== '') echo $passError; ?> </div>
        </div>

        <div class="input">
            <label for="cpassword">Confirm Password</label>
            <input name='cpass' id="cpassword" type="password" placeholder="Confirm Password" required>
            <div class="error"><?php if (isset($_POST['submit']) && $confirmPassError !== '') echo $confirmPassError; ?> </div>
        </div>

        <button name="submit" type="submit" class="signup-btn">Sign Up</button>

        <div class="SignUp">
            Already have an account? <a href="logIn.php">Login</a>
        </div>
    </form>
</body>

</html>