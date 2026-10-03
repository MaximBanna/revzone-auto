<?php
require('nav.php');

$newpass = '';
$str = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

$usernameError = '';
$sent = false;
if (isset($_POST['submit'])) {
    $username = isset($_POST['user']) ? $_POST['user'] : '';

    $result = mysqli_execute_query($conn, "SELECT email, username FROM users WHERE username = ?", [$username]);
    $row = mysqli_fetch_array($result);

    if (!$row) {
        $usernameError = "username does not exist.";
    } else {
        $newpass = '';
        for ($i = 0; $i < 6; $i++) {
            $x = random_int(0, strlen($str) - 1);
            $newpass .= $str[$x];
        }
        $hash = password_hash($newpass, PASSWORD_DEFAULT);
        mysqli_execute_query($conn, "UPDATE users SET changeP=1, password = ? WHERE username = ?", [$hash, $username]);

        $resetUrl = SITE_URL . "/logIn.php";
        $subject = "Your Revzone Password Has Been Updated";
        $message  = "<b>Hello $username</b>";
        $message .= "<p>We wanted to let you know that your Revzone password was successfully updated.</p>";
        $message .= "<p>Your updated password: $newpass.</p>";
        $message .= "<p>update your passowrd here: <a href='$resetUrl'> Click here to login</a> <p>";
        $message .= "<p>Thank you,</p>";
        $message .= "<p>The Revzone Account Team</p>";

        $header = "From: RevZone <" . MAIL_FROM . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html\r\n";
        $retval = mail($row['email'], $subject, $message, $header);
        $sent = true;
    }
}
CloseCon($conn);
?>

<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="forgotStyle.css" />

    <title>forgot password</title>
</head>

<body>
    <?php require('header.php'); ?>
    <form id="forgot-pass" method="post" class="<?php if ($newpass !== '') echo 'form-locked'; ?>">
        <h2>Get New Password</h2>

        <div class="input">
            <label for="user">User</label>
            <input name="user" id="user" type="text" placeholder="Enter your username" required />

            <div class="error">
                <?php if (isset($_POST['submit']) && $usernameError !== '') echo $usernameError; ?>
            </div>
        </div>

        <div class="input">
            <div class="getRandom">
                <?php if (isset($_POST['submit']) && $newpass !== '') echo "Check your email "; ?>
            </div>
        </div>

        <?php if (!$sent): ?>
            <button name="submit" type="submit" class="change-btn">Get Password</button>
        <?php else: ?>
            <button name="submit" type="submit" class="change-btn" disabled>Get Password</button>
        <?php endif; ?>

        <div class="login-link">
            <p>Already have an account? <a href="logIn.php">Login</a></p>
        </div>
    </form>
</body>

</html>