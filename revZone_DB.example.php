<?php
define('SITE_URL', 'http://localhost/phpProject');   // change this one line if the site moves
define('MAIL_FROM', 'no-reply@example.com');         // address the site's emails are sent from
define('SUPPORT_EMAIL', 'support@example.com');     // address shown to users for help

function OpenCon()
{
    $dbhost = "localhost";
    $dbuser = "root";
    $dbpass = ""; // XAMPP default is empty - put your own MySQL password here
    $db = "revzone_db";
    $dbport = 3306; // XAMPP default port
    $conn = new mysqli($dbhost, $dbuser, $dbpass, $db, $dbport);
    return $conn;
}

function CloseCon($conn)
{
    $conn->close();
}
