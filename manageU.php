<?php
require('nav.php');

// AUTH (manager only) 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];

$roleQ = mysqli_execute_query($conn, "SELECT role FROM users WHERE username = ?", [$username]);
$roleRow = mysqli_fetch_assoc($roleQ);

if (!$roleRow || (int)$roleRow['role'] !== 1) {
    header("Location: revZone.php");
    exit;
}

// MESSAGE 
$msg = "";

$adduserError = $addfnameError = $addlnameError = $addaddressError = $addemailError = $addpassError = $addphoneError = $adddobError = "";
$check = true;

if (isset($_POST['user_submit'])) {
    $adduser = isset($_POST['adduser']) ? trim($_POST['adduser']) : '';
    $addfname = isset($_POST['addfname']) ? trim($_POST['addfname']) : '';
    $addlname = isset($_POST['addlname']) ? trim($_POST['addlname']) : '';
    $addaddress = isset($_POST['addaddress']) ? strtolower(trim($_POST['addaddress'])) : '';
    $addphone = isset($_POST['addphone']) ? trim($_POST['addphone']) : '';
    $addemail = isset($_POST['addemail']) ? strtolower(trim($_POST['addemail'])) : '';
    $addpass = isset($_POST['addpass']) ? trim(trim($_POST['addpass'])) : '';
    $addrole = isset($_POST['addrole']) ? intval($_POST['addrole']) : 0;
    $adddob = isset($_POST['adddob']) ? trim($_POST['adddob']) : '';

    //validate username
    if (empty($adduser) || !preg_match("/^[a-zA-Z0-9_]+$/", $adduser)) {
        $adduserError = "Please enter a valid username.";
        $check = false;
    } else {
        $result = mysqli_execute_query($conn, "SELECT username FROM users WHERE username = ?", [$adduser]);
        $row = mysqli_fetch_array($result);
        if ($row) {
            $adduserError = "Username already exists.";
            $check = false;
        }
    }

    // Validate first name
    if (empty($addfname) || !preg_match("/^[a-zA-Z ]{2,}+$/", $addfname)) {
        $addfnameError = "Please enter valid first name (letters only, At least 2).";
        $check = false;
    }

    // Validate last name
    if (empty($addlname) || !preg_match("/^[a-zA-Z ]{2,}$/", $addlname)) {
        $addlnameError = "Please Enter a valid last name (letters only, At least 2).";
        $check = false;
    }

    // Validate address
    if (empty($addaddress)) {
        $addaddressError = "Please enter your address.";
        $check = false;
    }

    // Validate phone
    if (!preg_match("/^\+?[0-9]{10,15}$/", $addphone)) {
        $addphoneError = "Please enter a valid phone number.";
        $check = false;
    }

    // Validate email
    if (empty($addemail) || !preg_match("/^[^\s@]+@[^\s@]+\.[^\s@]+$/", $addemail)) {
        $addemailError = "Please enter a valid email address.";
        $check = false;
    } else {
        $result = mysqli_execute_query($conn, "SELECT email FROM users WHERE email = ?", [$addemail]);
        $row = mysqli_fetch_array($result);
        if ($row) {
            $addemailError = "Email already exists.";
            $check = false;
        }
    }

    // Validate password
    if (strlen($addpass) < 6) {
        $addpassError = "Password must be at least 6 characters long.";
        $check = false;
    }

    // Validate date of birth
    if (empty($adddob)) {
        $adddobError = "Please enter date of birth.";
        $check = false;
    } else if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $adddob)) {
        $adddobError = "Date of birth must be YYYY-MM-DD.";
        $check = false;
    }


    // if every thing passed - addToDB
    if ($check) {
        $hash = password_hash($addpass, PASSWORD_DEFAULT);
        mysqli_execute_query($conn, "INSERT INTO users (username, fname, lname, birth_date, role, address, phone, email, password, pass3, pass2, pass1, changeP, blocked)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, '0', '0')", [$adduser, $addfname, $addlname, $adddob, $addrole, $addaddress, $addphone, $addemail, $hash, $hash, $hash, $hash]);
        echo "<script>alert('User added successfully'); window.location.href='manageU.php';</script>";
        exit;
    }
}

// TOGGLE BLOCK 
if (isset($_POST['toggle_block'])) {

    $user = $_POST['u'];
    if ($user === $username) {
        $msg = "You can't block yourself.";
    } else {
        $check = mysqli_execute_query($conn, "SELECT blocked FROM users WHERE username = ?", [$user]);
        $row = mysqli_fetch_assoc($check);

        if ($row != null) {
            if ($row['blocked'] == 1) {
                mysqli_execute_query($conn, "UPDATE users SET blocked=0 WHERE username = ?", [$user]);
                $msg = "User $user has been unblocked.";
            } else {
                mysqli_execute_query($conn, "UPDATE users SET blocked=1 WHERE username = ?", [$user]);
                $msg = "User $user has been blocked.";
            }
        }
    }
}

// DELETE USER 
if (isset($_POST['delete_user'])) {
    $user = $_POST['u'];
    if ($user === $username) {
        $msg = "You can't delete yourself.";
    } else {
        $ordersQ = mysqli_execute_query($conn, "SELECT 1 FROM orders WHERE user_id = ? LIMIT 1", [$user]);
        if (mysqli_fetch_assoc($ordersQ)) {
            $msg = "User $user has orders and can't be deleted. Block the account instead.";
        } else {
            mysqli_execute_query($conn, "DELETE FROM users WHERE username = ?", [$user]);
            $msg = "User $user has been deleted.";
        }
    }
}

// SEARCH + LIST 
if (isset($_GET['q']) && $_GET['q'] != "") {
    $q = $_GET['q'];
    $sql = "SELECT username, fname, lname, address, phone, email, role, blocked FROM users
            WHERE username = ? ORDER BY role DESC, username ASC";
    $params = [$q];
} else {
    $sql = "SELECT username, fname, lname, address, phone, email, role, blocked FROM users
            ORDER BY role DESC, username ASC";
    $params = [];
}
$res = mysqli_execute_query($conn, $sql, $params);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RevZone - Manage Users</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="manageUStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <!-- ADD USER -->
    <div class="panel add-user-panel">
        <h1>Add New User</h1>
        <form method="post" class="form-grid">
            <div class="field">
                <label>Username</label>
                <input type="text" name="adduser" required>
                <div class="error"><?php if (isset($_POST['adduser']) && $adduserError !== '') echo $adduserError; ?> </div>
            </div>

            <div class="field">
                <label>First Name</label>
                <input type="text" name="addfname" required>
                <div class="error"><?php if (isset($_POST['addfname']) && $addfnameError !== '') echo $addfnameError; ?> </div>
            </div>

            <div class="field">
                <label>Last Name</label>
                <input type="text" name="addlname" required>
                <div class="error"><?php if (isset($_POST['addlname']) && $addlnameError !== '') echo $addlnameError; ?> </div>
            </div>

            <div class="field">
                <label>Date of Birth</label>
                <input type="date" name="adddob" required>
                <div class="error"><?php if (isset($_POST['adddob']) && $adddobError !== '') echo $adddobError; ?> </div>
            </div>


            <div class="field">
                <label>Address</label>
                <input type="text" name="addaddress" required>
                <div class="error"><?php if (isset($_POST['addaddress']) && $addaddressError !== '') echo $addaddressError; ?> </div>
            </div>

            <div class="field">
                <label>Phone</label>
                <input type="text" name="addphone" required>
                <div class="error"><?php if (isset($_POST['addphone']) && $addphoneError !== '') echo $addphoneError; ?> </div>
            </div>

            <div class="field">
                <label>Email</label>
                <input type="email" name="addemail" required>
                <div class="error"><?php if (isset($_POST['addemail']) && $addemailError !== '') echo $addemailError; ?> </div>
            </div>

            <div class="field">
                <label>Password</label>
                <input type="password" name="addpass" required>
                <div class="error"><?php if (isset($_POST['addpass']) && $addpassError !== '') echo $addpassError; ?> </div>
            </div>

            <div class="role-row">
                <div class="field">
                    <label>Role</label>
                    <select name="addrole">
                        <option value="0">User</option>
                        <option value="1">Admin</option>
                    </select>
                </div>
                <button name="user_submit" class="btn" type="submit">Add User</button>
            </div>
        </form>
    </div>

    <div class="wrap">
        <div class="panel">
            <div class="top">
                <h1>Manage Users</h1>

                <form class="search" method="GET" action="manageU.php">
                    <input type="text" name="q" placeholder="Search by username">
                    <button class="btn" type="submit">Search</button>
                    <a class="btn" href="manageU.php" style="text-decoration:none;">Clear</a>
                </form>
            </div>

            <?php if ($msg != ""): ?>
                <div class="msg"><?php echo htmlspecialchars($msg); ?></div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>First</th>
                        <th>Last</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($u = mysqli_fetch_assoc($res)): ?>
                        <tr>
                            <td><b><?php echo htmlspecialchars($u['username']); ?></b></td>
                            <td><?php echo htmlspecialchars($u['fname']); ?></td>
                            <td><?php echo htmlspecialchars($u['lname']); ?></td>
                            <td><?php echo htmlspecialchars($u['address']); ?></td>
                            <td><?php echo htmlspecialchars($u['phone']); ?></td>
                            <td><?php echo htmlspecialchars($u['email']); ?></td>
                            <td>
                                <?php if ($u['role'] == 1): ?>
                                    <span class="badge admin">Admin</span>
                                <?php else: ?>
                                    <span class="badge user">User</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($u['blocked'] == 1): ?>
                                    <span class="badge blocked">Blocked</span>
                                <?php else: ?>
                                    <span class="badge active">Active</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="actions">
                                    <form method="POST" action="manageU.php">
                                        <input type="hidden" name="u" value="<?php echo htmlspecialchars($u['username']); ?>">
                                        <button class="smallbtn orange" name="toggle_block" type="submit">
                                            <?php echo ($u['blocked'] == 1 ? "Unblock" : "Block"); ?>
                                        </button>
                                    </form>

                                    <form method="POST" action="manageU.php" onsubmit="return confirm('Delete user?');">
                                        <input type="hidden" name="u" value="<?php echo htmlspecialchars($u['username']); ?>">
                                        <button class="smallbtn red" name="delete_user" type="submit">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>