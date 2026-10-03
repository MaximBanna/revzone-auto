<?php
require('revZone_DB.php');
$conn = OpenCon();
session_start();

$role = '';
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $username = $_SESSION['username'];
    $query = mysqli_execute_query($conn, "SELECT `role`, blocked FROM users WHERE username = ?", [$username]);
    $row = mysqli_fetch_assoc($query);
    if (!$row || $row['blocked'] == 1) { // user deleted or blocked by the admin -> log out
        session_unset();
        session_destroy();
        header("Location: logIn.php");
        exit;
    }
    $role = (int)$row['role'];
}
