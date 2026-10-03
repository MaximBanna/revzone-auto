<?php
require('nav.php');

// Must be logged in 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: logIn.php");
    exit;
}

$username = $_SESSION['username'];
$success = false;
$error = '';

if (isset($_POST['submit'])) {
    $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    if ($subject !== '' && $message !== '') {
        // Check if user already has an open ticket
        $check = mysqli_execute_query($conn, "SELECT id FROM contact WHERE username = ?", [$username]);
        $row = mysqli_fetch_array($check);

        if ($row) {
            $error = "You already have an open support ticket. Please wait for a response.";
        } else {
            $time = date("d.m.y H:i");
            $chat = "[$time] " . $username . ": " . $message . "\n";
            mysqli_execute_query($conn, "INSERT INTO contact (username, `subject`, chat, `date`) VALUES (?, ?, ?, NOW())", [$username, $subject, $chat]);
            $success = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Contact Us</title>
    <link rel="stylesheet" href="contactStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <form id="contact-form" method="post">
        <h2>Contact Us</h2>
        <?php if ($success): ?>
            <div class="message success">
                ✓ Thank you for contacting us. We will get back to you soon.<br>
                <a href="contactUser.php">View your support ticket →</a>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                ✗ <?php echo $error; ?><br>
                <a href="contactUser.php">Go to your open ticket →</a>
            </div>
        <?php endif; ?>

        <div class="input">
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" placeholder="What is this about?" required>
        </div>

        <div class="input">
            <label for="message">Message</label>
            <textarea name="message" id="message" placeholder="Describe your issue or question..." rows="5" required></textarea>
        </div>

        <button name="submit" type="submit" class="submit-btn">Send Message</button>

    </form>
</body>

</html>