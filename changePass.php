<?php
require('nav.php');

if (!isset($_SESSION['username'])) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];

$passError = '';
$cpassError = '';

if (isset($_POST['submit'])) {
    $pass = isset($_POST['pass']) ? $_POST['pass'] : '';
    $cpass = isset($_POST['cpass']) ? $_POST['cpass'] : '';

    $strTo = mysqli_execute_query($conn, "SELECT * FROM users WHERE username = ?", [$username]);
    $res = mysqli_fetch_array($strTo);

    $pass3 = $res['pass3'];
    $pass2 = $res['pass2'];
    $pass1 = $res['pass1'];

    if (strlen($pass) < 6) {
        $passError = "Password must be at least 6 characters long.";
    } else if (password_verify($pass, $pass3) || password_verify($pass, $pass2) || password_verify($pass, $pass1)) {
        $passError = "Enter a password that is different from the last 3 passwords you made";
    } else if ($cpass != $pass) {
        $cpassError = "passwords doesn't match !";
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        mysqli_execute_query($conn, "UPDATE users SET changeP = 0, pass1 = ?, pass2 = ?, pass3 = ?, `password` = ? WHERE username = ?", [$pass2, $pass3, $hash, $hash, $username]);
        mysqli_execute_query($conn, "UPDATE users SET loginh = NOW() WHERE username = ?", [$username]);
        $_SESSION['loggedin'] = true;
        header("Location: revZone.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="changePassStyle.css" />

    <title>Change password</title>
</head>

<body>
    <?php require('header.php'); ?>
    <div class="change-passBox">
        <h2>Change Password</h2>

        <form id="change-pass" method="post">
            <div class="input">
                <label for="password">New Password</label>
                <input name="pass" id="password" type="password" placeholder="Enter your Password" required />
                <div id="passError" class="error">
                    <?php if (isset($_POST['submit']) && $passError !== '') echo $passError; ?>
                </div>
            </div>

            <div class="input">
                <label for="cpassword" style="display:none;">Confirm Password</label>
                <input name="cpass" id="cpassword" type="password" placeholder="Confirm your Password" required />
                <div id="cpassError" class="error">
                    <?php if (isset($_POST['submit']) && $cpassError !== '') echo $cpassError; ?>
                </div>
            </div>

            <button name="submit" type="submit" class="change-btn">Change Password</button>

            <div class="login-link">
                <p>Already have an account? &nbsp;<a href="logIn.php">Login</a></p>
            </div>
        </form>
    </div>
</body>

</html>