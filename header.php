<!-- EXTERNAL LINKS -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="navStyle.css">

<header>
    <div class="navbar">
        <!-- LOGO -->
        <div class="logo">
            <a href="revZone.php" class="logo-text">
                <span class="logo-main">REVZONE</span>
                <span class="logo-sub">AUTO</span>
            </a>
        </div>
        <!-- NAV LINKS -->
        <div class="nav-buttons">
            <?php
            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
                // CLIENT 
                if ($role == 0) {
                    echo '
                            <a href="revZone.php">Home</a>
                            <a href="products.php">Products</a>
                            <a href="myorders.php">My Orders</a>
                            <a href="contact.php">Contact</a>
                            <a href="contactUser.php">support page</a>
                        ';
                }
                // ADMIN 
                else {
                    echo '
                            <a href="revZone.php">Home</a>
                            <a href="manageU.php">Manage Users</a>
                            <a href="manageP.php">Manage Products</a>
                            <a href="manageD.php">manage Delivery</a>
                            <a href="contactManager.php">Chat</a>
                        ';
                }
            } else {
                echo '
                        <a href="revZone.php">Home</a>
                        <a href="products.php">Products</a>
                        <a href="about.php">About</a>
                        <a href="contact.php">Contact</a>
                    ';
            }
            ?>
        </div>
        <!-- USER ACTIONS -->
        <div class="user-actions">
            <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
                <a href="profile.php" class="account-link">
                    <i class="far fa-user-circle"></i>
                    <span>Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </a>

                <?php if ($role == 0): ?>
                    <a href="cart.php" class="cart-btn">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span>CART</span>
                    </a>
                <?php endif; ?>

                <a href="logout.php" class="cart-btn">
                    <i class="fas fa-sign-out-alt"></i> Log Out
                </a>
            <?php else: ?>
                <a href="logIn.php" class="account-link">
                    <i class="far fa-user-circle"></i>
                    <span>Log In</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<?php include __DIR__ . '/chatbot/chatbot_ui.php'; ?>