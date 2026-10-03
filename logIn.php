<?php
require('nav.php');

if (!isset($_SESSION['cnt']) || !is_array($_SESSION['cnt'])) {
    $_SESSION['cnt'] = [];
}

$usernameError = '';
$passwordError = '';
$msg = '';

if (isset($_POST['submit'])) {
    $username = isset($_POST['user']) ? $_POST['user'] : '';
    $password = isset($_POST['pass']) ? $_POST['pass'] : '';

    $result = mysqli_execute_query($conn, "SELECT * FROM users WHERE username = ?", [$username]);
    $row = mysqli_fetch_array($result);

    if (!$row) { // no user found
        $usernameError = "User does not exist.";
    } else if ($row['blocked'] == 1) { // blocked by the admin - no login at all
        $msg = "Account Access Restricted.
            Your account has been blocked by the administrator. For further assistance or clarification,
            please check your email or contact our support team via email at " . SUPPORT_EMAIL;
    } else if ($row['changeP'] == 1 && !password_verify($password, $row['password'])) {
        $msg = "You received a new password by email.";
    } else if (!password_verify($password, $row['password'])) { // wrong pass
        if (!isset($_SESSION['cnt'][$username])) {
            $_SESSION['cnt'][$username] = 0;
        }
        $_SESSION['cnt'][$username]++;
        $remain = 3 - $_SESSION['cnt'][$username];

        if ($_SESSION['cnt'][$username] < 3) {
            $passwordError = "Wrong password. $remain tries remaining.";
        } else {
            $str = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $newpass = '';
            for ($i = 0; $i < 6; $i++) {
                $x = random_int(0, strlen($str) - 1);
                $newpass .= $str[$x];
            }
            $hash = password_hash($newpass, PASSWORD_DEFAULT);
            mysqli_execute_query($conn, "UPDATE users SET changeP = 1, `password` = ?  WHERE username = ?", [$hash, $username]);

            $resetUrl = SITE_URL . "/logIn.php";
            $subject = "Your Revzone Password Has Been Updated";
            $message  = "<b>Hello $username</b>";
            $message .= "<p>We wanted to let you know that your Revzone password was updated due to many failed attempts.</p>";
            $message .= "<p>Your updated password: $newpass.</p>";
            $message .= "<p>update your passowrd here: <a href='$resetUrl'> Click here to login</a> <p>";
            $message .= "<p>If it wasn't you contact us via email at " . SUPPORT_EMAIL . "</p>";
            $message .= "<p>The Revzone Account Team</p>";

            $header = "From: RevZone <" . MAIL_FROM . ">\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html\r\n";
            $retval = mail($row['email'], $subject, $message, $header);
            $msg = "check your email";
        }
    } else { // correct pass
        session_regenerate_id(true);
        if ($row['changeP'] == 1) { //need to change pass
            $_SESSION['cnt'][$username] = 0;
            $_SESSION['username'] = $username;
            header("Location: changePass.php");
        } else {
            $_SESSION['cnt'][$username] = 0;
            $_SESSION['loggedin'] = true;
            $_SESSION['username'] = $username;

            mysqli_execute_query($conn, "UPDATE users SET loginh = NOW() WHERE username = ?", [$username]);
            header("Location: revZone.php");
        }
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="LogInStyle.css" />
    <title>RevZone Login</title>
</head>

<body>
    <?php require('header.php'); ?>
    <form id="login" method="post">
        <h2>Login</h2>

        <div class="msg-box"><?php if (isset($_POST['submit']) && $msg !== '') echo $msg; ?></div>

        <div class="input">
            <label for="user">User</label>
            <input name="user" id="user" type="text" placeholder="Enter user name" required />

            <div id="userError" class="error">
                <?php if (isset($_POST['submit']) && $usernameError !== '') echo $usernameError; ?>
            </div>
        </div>

        <div class="input">
            <label for="password">Password</label>
            <input name="pass" id="password" type="password" placeholder="Enter your Password" required />

            <div id="passError" class="error">
                <?php if (isset($_POST['submit']) && $passwordError !== '') echo $passwordError; ?>
            </div>
        </div>

        <div class="remember-forgot">
            <a href="forgot.php">Forgot Password?</a>
        </div>

        <button name="submit" type="submit" class="login-btn">Login</button>

        <div class="SignUp">
            <p>Don't have an account? <a href="signUp.php">Sign up Now</a></p>
        </div>
    </form>
</body>

</html>