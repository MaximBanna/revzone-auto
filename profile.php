<?php
require('nav.php');

// AUTH CHECK 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];

// FETCH USER DETAILS 
$userQ = mysqli_execute_query($conn, "SELECT * FROM users WHERE username = ? LIMIT 1", [$username]);
$user = mysqli_fetch_assoc($userQ);
if (!$user) {
    header("Location: revZone.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Account | RevZone</title>
    <link rel="stylesheet" href="profileStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <div class="page-wrap">
        <!-- Header -->
        <section class="hero">
            <div class="hero-left">
                <h1 class="title">My Account</h1>
                <p class="subtitle">
                    View your details and manage your actions inside the system.
                </p>
            </div>

            <div class="hero-right">
                <div class="badge">
                    <div class="badge-dot"></div>
                    <span>
                        <?php echo ((int)$user['role'] === 1) ? "Admin Account" : "Customer Account"; ?>
                    </span>
                </div>
            </div>
        </section>

        <!-- Main Grid -->
        <section class="grid">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-head">
                    <h2>Profile Details</h2>
                    <span class="mini">Account information </span>
                </div>

                <div class="info-grid">
                    <div class="info">
                        <div class="label">Full Name</div>
                        <div class="value">
                            <?php echo htmlspecialchars($user['fname'] . " " . $user['lname']); ?>
                        </div>
                    </div>

                    <div class="info">
                        <div class="label">Username</div>
                        <div class="value"><?php echo htmlspecialchars($user['username']); ?></div>
                    </div>

                    <div class="info">
                        <div class="label">Email</div>
                        <div class="value"><?php echo htmlspecialchars($user['email']); ?></div>
                    </div>

                    <div class="info">
                        <div class="label">Phone</div>
                        <div class="value"><?php echo htmlspecialchars($user['phone']); ?></div>
                    </div>

                    <div class="info">
                        <div class="label">Address</div>
                        <div class="value"><?php echo htmlspecialchars($user['address']); ?></div>
                    </div>

                    <div class="info">
                        <div class="label">Birth Date</div>
                        <div class="value"><?php echo $user['birth_date']; ?></div>
                    </div>

                    <div class="info">
                        <div class="label">Account Status</div>
                        <div class="value">
                            <?php echo ((int)$user['blocked'] === 0) ? "Active" : "Suspended"; ?>
                        </div>
                    </div>

                    <div class="info">
                        <div class="label">Last Login</div>
                        <div class="value">
                            <?php echo $user['loginh'] ? date("d M Y, H:i", strtotime($user['loginh'])) : 'Never'; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card">
                <div class="card-head">
                    <h2>Available Actions</h2>
                    <span class="mini">What you can do in the system</span>
                </div>

                <?php if ((int)$user['role'] === 1): ?>
                    <!-- ADMIN ACTIONS (replace user buttons) -->
                    <div class="actions admin-actions">
                        <a class="action-btn admin" href="manageU.php">
                            <div class="action-title">Manage Users</div>
                            <div class="action-sub">View & control accounts</div>
                        </a>

                        <a class="action-btn admin" href="manageP.php">
                            <div class="action-title">Manage Products</div>
                            <div class="action-sub">Stock, add & delete products</div>
                        </a>

                        <a class="action-btn admin" href="manageD.php">
                            <div class="action-title">Manage Delivery</div>
                            <div class="action-sub">Assign & update deliveries</div>
                        </a>

                        <a class="action-btn admin" href="contactManager.php">
                            <div class="action-title">Manage Tickets</div>
                            <div class="action-sub">Reply & close contacts</div>
                        </a>

                        <a class="action-btn danger" href="logout.php">
                            <div class="action-title">Logout</div>
                            <div class="action-sub">Sign out of your account</div>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- CUSTOMER ACTIONS -->
                    <div class="actions">
                        <a class="action-btn" href="products.php">
                            <div class="action-title">Browse Products</div>
                            <div class="action-sub">Explore RevZone catalog</div>
                        </a>

                        <a class="action-btn" href="cart.php">
                            <div class="action-title">My Cart</div>
                            <div class="action-sub">Review & checkout</div>
                        </a>

                        <a class="action-btn" href="myorders.php">
                            <div class="action-title">My Orders</div>
                            <div class="action-sub">Track all your orders</div>
                        </a>

                        <a class="action-btn" href="changePass.php">
                            <div class="action-title">Change Password</div>
                            <div class="action-sub">Update your account security</div>
                        </a>

                        <a class="action-btn" href="contactUser.php">
                            <div class="action-title">Contact Support</div>
                            <div class="action-sub">Open a ticket</div>
                        </a>

                        <a class="action-btn danger" href="logout.php">
                            <div class="action-title">Logout</div>
                            <div class="action-sub">Sign out of your account</div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>

</html>