<?php
require('nav.php');

// AUTH CHECK (MANAGER ONLY) 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 0) {
    header("Location: revZone.php");
    exit;
}
$username = $_SESSION['username'];

// REDIRECT IF CART IS EMPTY 
$cartEmptyCheck = mysqli_execute_query($conn, "SELECT 1 FROM cart WHERE username = ? LIMIT 1", [$username]);
if (!$cartEmptyCheck || mysqli_num_rows($cartEmptyCheck) === 0) {
    header("Location: revZone.php");
    exit;
}

if (isset($_POST['submit'])) {
    $cartCheck = mysqli_execute_query($conn, "SELECT 1 FROM cart WHERE username = ? LIMIT 1", [$username]);
    if (mysqli_num_rows($cartCheck) === 0) {
        header("Location: cart.php");
        exit;
    }
}

// generate ORDER ID at the begining 
if (!isset($_SESSION['order_id'])) {
    do {
        $tempOrderId = random_int(100000, 999999);
        $check = mysqli_execute_query($conn, "SELECT order_id FROM orders WHERE order_id = ?", [$tempOrderId]);
    } while ($check && mysqli_num_rows($check) > 0);

    $_SESSION['order_id'] = $tempOrderId;
}

// LOAD CITIES FROM DB 
$cities = [];
$cityRes = mysqli_execute_query($conn, "SELECT `name` FROM city ORDER BY `name` ASC");
if ($cityRes) {
    while ($r = mysqli_fetch_assoc($cityRes)) {
        $cities[] = $r['name'];
    }
}

// DELIVERY UI MODE 
if (!isset($_SESSION['delivery_chosen'])) {
    $_SESSION['delivery_chosen'] = false;
}
if (!isset($_SESSION['delivery_method'])) {
    $_SESSION['delivery_method'] = 'ship';
}
if (!isset($_SESSION['branch_id'])) {
    $_SESSION['branch_id'] = '';
}
if (!isset($_SESSION['pickup_from'])) {
    $_SESSION['pickup_from'] = '';
}
if (!isset($_SESSION['pickup_to'])) {
    $_SESSION['pickup_to'] = '';
}
if (!isset($_SESSION['arrival_date'])) {
    $_SESSION['arrival_date'] = '';
}

// Update delivery method button 
if (isset($_POST['updateDelivery'])) {
    $_SESSION['delivery_chosen'] = true;

    if (isset($_POST['delivery_method']) && ($_POST['delivery_method'] === 'ship' || $_POST['delivery_method'] === 'pickup')) {
        $_SESSION['delivery_method'] = $_POST['delivery_method'];
        // Clear pickup values if switching to ship 
        if ($_POST['delivery_method'] === 'ship') {
            $_SESSION['branch_id'] = '';
            $_SESSION['pickup_from'] = '';
            $_SESSION['pickup_to'] = '';
        }
    }
}

$deliveryMethod = $_SESSION['delivery_method'];

// LOAD BRANCHES FROM DB 
$branches = [];
$branchQuery = mysqli_execute_query(
    $conn,
    "SELECT id, name, city FROM branches ORDER BY name ASC"
);
if ($branchQuery) {
    while ($br = mysqli_fetch_assoc($branchQuery)) {
        $branches[] = $br;
    }
}

// initialize error values 
$fullnameError = $emailError = $addressError = $cityError = $cardnameError = $cardnumberError = '';
$expmonthError = $expyearError = $cvvError = $stockError = $formError = $arrivalDateError = '';

// Pickup errors 
$branchError = $pickupFromError = $pickupToError = '';

// initialize values 
$fullname = $email = $address = $city = $orderId = '';
$cardname = $cardnumber = $expmonth = $expyear = $cvv = '';
$orderId = isset($_SESSION['order_id']) ? $_SESSION['order_id'] : '';

// Pickup values from session 
$branch_id = $_SESSION['branch_id'];
$pickup_from = $_SESSION['pickup_from'];
$pickup_to = $_SESSION['pickup_to'];
$arrival_date = $_SESSION['arrival_date'];

// initialize year for card exp year
$startYear = date("Y");
$endYear = $startYear + 10;

if (isset($_POST['submit'])) {
    $_SESSION['delivery_chosen'] = true;
    $username = $_SESSION['username'];

    $fullname = isset($_POST['fullname']) ? trim($_POST['fullname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';

    $cardname = isset($_POST['cardname']) ? trim($_POST['cardname']) : '';
    $cardnumber = isset($_POST['cardnumber']) ? trim($_POST['cardnumber']) : '';

    $expmonth = isset($_POST['expmonth']) ? trim($_POST['expmonth']) : '';
    $expyear = isset($_POST['expyear']) ? trim($_POST['expyear']) : '';

    $cvv = isset($_POST['cvv']) ? trim($_POST['cvv']) : '';

    // delivery choice from session
    $deliveryMethod = isset($_SESSION['delivery_method']) ? $_SESSION['delivery_method'] : 'ship';

    // read pickup values from POST on submit
    $branch_id = isset($_POST['branch_id']) ? trim($_POST['branch_id']) : $_SESSION['branch_id'];
    $pickup_from = isset($_POST['pickup_from']) ? trim($_POST['pickup_from']) : $_SESSION['pickup_from'];
    $pickup_to = isset($_POST['pickup_to']) ? trim($_POST['pickup_to']) : $_SESSION['pickup_to'];
    $arrival_date = isset($_POST['arrival_date']) ? trim($_POST['arrival_date']) : $_SESSION['arrival_date'];

    // keep them in session
    $_SESSION['branch_id'] = $branch_id;
    $_SESSION['pickup_from'] = $pickup_from;
    $_SESSION['pickup_to'] = $pickup_to;
    $_SESSION['arrival_date'] = $arrival_date;

    //  VALIDATION 
    if ($fullname === '' || !preg_match("/^[A-Za-z ]+$/", $fullname)) {
        $fullnameError = "Full Name must contain letters only.";
    }
    if ($email === '' || !preg_match("/^[^\s@]+@[^\s@]+\.[^\s@]+$/", $email)) {
        $emailError = "Please enter a valid email address.";
    }
    // Ship vs Pickup validation 
    if ($deliveryMethod === 'ship') {
        if ($city === '') {
            $cityError = "Please select a city.";
        } else {
            $cityCheck = mysqli_execute_query($conn, "SELECT `name` FROM city WHERE `name` = ?", [$city]);
            if (mysqli_num_rows($cityCheck) === 0) {
                $cityError = "Invalid city selected.";
            }
        }
        if ($address === '') {
            $addressError = "Please enter your address.";
        }
    } else {
        // Pickup validation
        if ($branch_id === '') {
            $branchError = "Please select a pickup branch.";
        }
        if ($arrival_date  === '') {
            $arrivalDateError = "Please select a pickup date.";
        }
        // Validate date is not in the past
        if ($arrival_date  !== '') {
            $today = date('Y-m-d');
            if ($arrival_date  < $today) {
                $arrivalDateError = "Pickup date cannot be in the past.";
            }
        }
        if ($pickup_from === '') {
            $pickupFromError = "Please select pickup start time.";
        }
        if ($pickup_to === '') {
            $pickupToError = "Please select pickup end time.";
        }
        if ($pickup_from !== '' && $pickup_to !== '') {
            if (strtotime($pickup_from) > strtotime($pickup_to)) {
                $pickupToError = "Pickup end time must be after start time.";
            }
        }
    }

    // Name on card
    if ($cardname === '' || !preg_match("/^[A-Za-z ]+$/", $cardname)) {
        $cardnameError = "Name on Card must contain letters only.";
    }
    // Card number
    $cardDigits = str_replace(array(' ', '-'), '', $cardnumber);
    if ($cardnumber === '' || !preg_match("/^[0-9]{16}$/", $cardDigits)) {
        $cardnumberError = "Card number must be 16 digits.";
    }
    // Exp month
    if ($expmonth === '' || (int)$expmonth < 1 || (int)$expmonth > 12) {
        $expmonthError = "Please select a valid month.";
    }
    // Exp year
    if ($expyear === '') {
        $expyearError = "Please select a year.";
    }
    // CVV
    if ($cvv === '' || !preg_match("/^[0-9]{3}$/", $cvv)) {
        $cvvError = "CVV must be 3 digits.";
    }

    $hasErrors =
        !empty($fullnameError) ||
        !empty($emailError) ||
        !empty($addressError) ||
        !empty($cityError) ||
        !empty($branchError) ||
        !empty($arrivalDateError) ||
        !empty($pickupFromError) ||
        !empty($pickupToError) ||
        !empty($cardnameError) ||
        !empty($cardnumberError) ||
        !empty($expmonthError) ||
        !empty($expyearError) ||
        !empty($cvvError);

    // CONTINUE ONLY IF NO ERRORS 
    if (!$hasErrors) {
        mysqli_begin_transaction($conn);
        // Stock check
        $cartQuery = mysqli_execute_query($conn, "SELECT cart.product_SKU, cart.amount, products.stock, products.name FROM cart
             JOIN products ON cart.product_SKU = products.Product_SKU WHERE cart.username = ? FOR UPDATE", [$username]);

        while ($row = mysqli_fetch_assoc($cartQuery)) {
            if ((int)$row['stock'] < (int)$row['amount']) {
                $stockError = "Not enough in stock for: " . $row['name'];
                break;
            }
        }

        if ($stockError !== '') {
            mysqli_rollback($conn);
            $formError = $stockError;
        } else {
            $orderId = $_SESSION['order_id'];

            $shipping = ($deliveryMethod === 'pickup') ? 0 : 39;
            $insert = mysqli_execute_query($conn, "INSERT INTO orders (order_id, user_id, order_date, shipping) 
                 VALUES (?, ?, NOW(), ?)", [$orderId, $username, $shipping]);

            if (!$insert) {
                $formError = "Could not add order to DataBase. Please try again.";
            }

            $cartQuery = mysqli_execute_query($conn, "SELECT cart.product_SKU, cart.amount, products.price, products.stock FROM cart
                 JOIN products ON cart.product_SKU = products.Product_SKU WHERE cart.username = ?", [$username]);

            while ($row = mysqli_fetch_assoc($cartQuery)) {
                mysqli_execute_query($conn, "INSERT INTO orderh (order_id, product_SKU, amount, price, order_date)
                 VALUES (?, ?, ?, ?, NOW())", [$orderId, $row['product_SKU'], $row['amount'], $row['price']]);

                mysqli_execute_query($conn, "UPDATE products SET stock = stock - ? WHERE Product_SKU = ?", [$row['amount'], $row['product_SKU']]);
            }

            // Delivery table 
            $delivery_type = ($deliveryMethod === 'pickup') ? 'pickup' : 'ship';

            $branch_val = ($deliveryMethod === 'pickup' && is_numeric($branch_id)) ? (int)$branch_id : null;
            $from_val = ($deliveryMethod === 'pickup' && $pickup_from !== '') ? $pickup_from : null;
            $to_val = ($deliveryMethod === 'pickup' && $pickup_to !== '') ? $pickup_to : null;

            $arrival_val = ($deliveryMethod === 'pickup' && $arrival_date  !== '') ? $arrival_date : null;

            $db_city = ($deliveryMethod === 'ship') ? $city : '';
            $db_address = ($deliveryMethod === 'ship')
                ? $address
                : 'PICKUP';

            $city_id_val = null;

            if ($deliveryMethod === 'ship' && $city !== '') {
                $cityRes = mysqli_execute_query($conn, "SELECT id FROM city WHERE name = ? LIMIT 1", [$city]);
                if ($cityRow = mysqli_fetch_assoc($cityRes)) {
                    $city_id_val = $cityRow['id'];
                }
            }

            mysqli_execute_query($conn, "INSERT INTO delivery (order_id, fullname, email, city_id, `address`, `status`, arrival_time, delivery_type, branch_id, pickup_time_from, pickup_time_to)
            VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)", [$orderId, $fullname, $email, $city_id_val, $db_address, $arrival_val, $delivery_type, $branch_val, $from_val, $to_val]);

            // Email 
            $strTo = mysqli_execute_query($conn, "SELECT * FROM users WHERE username = ?", [$username]);
            $res = mysqli_fetch_array($strTo);

            $subject = "Your Order Confirmation & Receipt";

            $message = "<p><strong>Hello $username,</strong></p>";
            $message .= "<p>Thank you for your purchase! Your order has been completed successfully.</p>";
            $message .= "<p><a href='" . SITE_URL . "/receipt.php?id=$orderId'>View Receipt</a></p>";
            $message .= "<p>Thank you for choosing <strong>RevZone</strong>.</p>";

            $header = "From: RevZone <" . MAIL_FROM . ">\r\n";
            $header .= "MIME-Version: 1.0\r\n";
            $header .= "Content-type: text/html; charset=UTF-8\r\n";

            mail($res['email'], $subject, $message, $header);

            mysqli_execute_query($conn, "DELETE FROM cart WHERE username = ?", [$username]);
            mysqli_commit($conn);

            unset($_SESSION['branch_id']);
            unset($_SESSION['pickup_from']);
            unset($_SESSION['pickup_to']);
            unset($_SESSION['arrival_date']);
            unset($_SESSION['delivery_chosen']);
            unset($_SESSION['order_id']);

            header("Location: ordercomp.php");
            exit;
        }
    } else {
        $formError = "Please Fill in the details correctly.";
    }
}

// CART SUMMARY 
$cartItems = [];
$subtotal = 0;
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];

    $cartQ = mysqli_execute_query($conn, "SELECT products.Product_SKU, products.name, products.image, products.price, cart.amount
      FROM cart JOIN products ON cart.product_SKU = products.Product_SKU WHERE cart.username = ?", [$username]);

    while ($row = mysqli_fetch_assoc($cartQ)) {
        $row['line_total'] = $row['price'] * $row['amount'];
        $subtotal += $row['line_total'];
        $cartItems[] = $row;
    }
}

// Shipping depends on delivery method 
$shipping = 0;
if ($subtotal > 0) {
    $shipping = ($deliveryMethod === 'pickup') ? 0 : 39;
}
$total = $subtotal + $shipping;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,600,700&display=swap">
    <link rel="stylesheet" href="checkoutStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <div class="checkout-page">
        <div class="checkout-grid">
            <!-- LEFT -->
            <div class="checkout-left">
                <div class="checkout-container">
                    <div class="status">
                        <?php if ($formError !== ''): ?>
                            <p class="errorMsg"><?php echo $formError; ?></p>
                        <?php endif; ?>
                    </div>
                    <!-- Delivery -->
                    <div class="section-head">
                        <h2 class="section-title">Delivery</h2>
                        <p class="section-sub">Choose how you want to receive your order.</p>
                    </div>

                    <form method="post">
                        <div class="ship-pick">
                            <label class="radio-card">
                                <input type="radio" name="delivery_method" value="ship"
                                    <?php echo ($deliveryMethod === 'ship') ? 'checked' : ''; ?>>
                                <span class="radio-dot"></span>
                                <span class="radio-text">
                                    <strong>Ship</strong>
                                    <small>Delivery to your address</small>
                                </span>
                            </label>

                            <label class="radio-card">
                                <input type="radio" name="delivery_method" value="pickup"
                                    <?php echo ($deliveryMethod === 'pickup') ? 'checked' : ''; ?>>
                                <span class="radio-dot"></span>
                                <span class="radio-text">
                                    <strong>Pick up</strong>
                                    <small>Collect from a branch</small>
                                </span>
                            </label>
                        </div>

                        <button type="submit" name="updateDelivery" class="btn-update">
                            Update delivery method
                        </button>
                    </form>

                    <?php if ($_SESSION['delivery_chosen']): ?>
                        <form method="post">
                            <!-- Pickup box - only show when pickup is selected -->
                            <?php if ($_SESSION['delivery_chosen'] && $deliveryMethod === 'pickup'): ?>
                                <div class="pickup-ui">
                                    <div class="pickup-title">Self pickup details</div>
                                    <div class="pickup-sub">Select a branch and a time range.</div>

                                    <div class="input">
                                        <label><i class="fa fa-map-marker"></i> Pickup Branch</label>
                                        <select name="branch_id">
                                            <option value="">Select Branch</option>
                                            <?php foreach ($branches as $br): ?>
                                                <option value="<?php echo $br['id']; ?>"
                                                    <?php echo ($branch_id == $br['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($br['name'] . " – " . $br['city']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="error"><?php echo $branchError; ?></div>
                                    </div>

                                    <div class="input">
                                        <label><i class="fa fa-calendar"></i> Pickup Date</label>
                                        <input type="date" name="arrival_date"
                                            min="<?php echo date('Y-m-d'); ?>"
                                            value="<?php echo $arrival_date; ?>">
                                        <div class="error"><?php echo $arrivalDateError; ?></div>
                                    </div>

                                    <div class="flex-row">
                                        <div class="input">
                                            <label><i class="fa fa-clock-o"></i> From</label>
                                            <select name="pickup_from">
                                                <option value="">Select</option>
                                                <?php
                                                $times = ["09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00"];
                                                foreach ($times as $t) {
                                                    $sel = ($pickup_from === $t) ? 'selected' : '';
                                                    echo "<option value=\"$t\" $sel>$t</option>";
                                                }
                                                ?>
                                            </select>
                                            <div class="error"><?php echo $pickupFromError; ?></div>
                                        </div>

                                        <div class="input">
                                            <label><i class="fa fa-clock-o"></i> To</label>
                                            <select name="pickup_to">
                                                <option value="">Select</option>
                                                <?php
                                                $times2 = ["12:00", "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", "19:00"];
                                                foreach ($times2 as $t) {
                                                    $sel = ($pickup_to === $t) ? 'selected' : '';
                                                    echo "<option value=\"$t\" $sel>$t</option>";
                                                }
                                                ?>
                                            </select>
                                            <div class="error"><?php echo $pickupToError; ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="divider"></div>

                            <!-- Billing Address -->
                            <h3>Billing Address</h3>

                            <div class="input">
                                <label for="fname"><i class="fa fa-user"></i> Full Name</label>
                                <input type="text" id="fname" name="fullname" placeholder="John M. Doe" value="<?php echo htmlspecialchars($fullname); ?>">
                                <div class="error"><?php echo $fullnameError; ?></div>
                            </div>

                            <div class="input">
                                <label for="email"><i class="fa fa-envelope"></i> Email</label>
                                <input type="text" id="email" name="email" placeholder="john@example.com" value="<?php echo htmlspecialchars($email); ?>">
                                <div class="error"><?php echo $emailError; ?></div>
                            </div>

                            <!-- Ship fields only when ship -->
                            <?php if ($_SESSION['delivery_chosen'] && $deliveryMethod === 'ship'): ?>
                                <div class="input">
                                    <label for="city"><i class="fa fa-institution"></i> City</label>
                                    <select id="city" name="city">
                                        <option value="">Select City</option>
                                        <?php foreach ($cities as $c): ?>
                                            <option value="<?php echo htmlspecialchars($c); ?>"
                                                <?php echo ($city === $c) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($c); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="error"><?php echo $cityError; ?></div>
                                </div>

                                <div class="input">
                                    <label for="adr"><i class="fa fa-address-card-o"></i> Address</label>
                                    <input type="text" id="adr" name="address" placeholder="542 W. 15th Street" value="<?php echo htmlspecialchars($address); ?>">
                                    <div class="error"><?php echo $addressError; ?></div>
                                </div>
                            <?php endif; ?>

                            <h3>Payment</h3>

                            <label>Accepted Cards</label>
                            <div class="icon-container">
                                <i class="fa-brands fa-cc-visa" style="color:navy;"></i>
                                <i class="fa-brands fa-cc-amex" style="color:blue;"></i>
                                <i class="fa-brands fa-cc-mastercard" style="color:red;"></i>
                                <i class="fa-brands fa-cc-discover" style="color:orange;"></i>
                            </div>

                            <div class="input">
                                <label for="cname">Name on Card</label>
                                <input type="text" id="cname" name="cardname" placeholder="John More Doe" value="<?php echo htmlspecialchars($cardname); ?>">
                                <div class="error"><?php echo $cardnameError; ?></div>
                            </div>

                            <div class="input">
                                <label for="ccnum">Credit card number</label>
                                <input type="text" id="ccnum" name="cardnumber" placeholder="1111 2222 3333 4444" maxlength="19"
                                    value="<?php echo htmlspecialchars($cardnumber); ?>" oninput="this.value = this.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim()">
                                <div class="error"><?php echo $cardnumberError; ?></div>
                            </div>

                            <div class="flex-row">
                                <div class="input">
                                    <label>Exp Month</label>
                                    <select name="expmonth">
                                        <option value="">Month</option>
                                        <?php for ($m = 1; $m <= 12; $m++): ?>
                                            <option value="<?php echo $m; ?>" <?php echo (($expmonth == $m) ? 'selected' : ''); ?>>
                                                <?php echo $m; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="error"><?php echo $expmonthError; ?></div>
                                </div>

                                <div class="input">
                                    <label>Exp Year</label>
                                    <select name="expyear">
                                        <option value="">Year</option>
                                        <?php for ($y = $startYear; $y <= $endYear; $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php echo (($expyear == $y) ? 'selected' : ''); ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <div class="error"><?php echo $expyearError; ?></div>
                                </div>
                            </div>

                            <div class="input">
                                <label for="cvv">CVV</label>
                                <input type="text" id="cvv" name="cvv" maxlength="3" placeholder="352" value="<?php echo htmlspecialchars($cvv); ?>">
                                <div class="error"><?php echo $cvvError; ?></div>
                            </div>

                            <?php if (count($cartItems) > 0): ?>
                                <button name="submit" type="submit" class="btn">Continue to checkout</button>
                            <?php else: ?>
                                <button name="submit" type="submit" class="btn" disabled>Continue to checkout</button>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT -->
            <aside class="checkout-right">
                <div class="summary-card">

                    <div class="totals" style="margin-bottom: 15px; border-bottom: 1px solid rgba(242,242,242,0.1); padding-bottom: 10px;">
                        <div class="row" style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px;">
                            <span><?php echo date("d/m/Y"); ?></span>
                            <?php if (!empty($orderId)): ?>
                                <span style="color: #F2F2F2;">Order ID: #<?php echo $orderId; ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="summary-head">
                        <h2>Order Summary</h2>
                        <p>Products, quantities, and total price.</p>
                    </div>

                    <div class="summary-items">
                        <?php if (count($cartItems) > 0): ?>
                            <?php foreach ($cartItems as $item): ?>
                                <div class="sum-item">
                                    <img class="sum-img" src="<?php echo htmlspecialchars($item['image']); ?>">
                                    <div class="sum-mid">
                                        <div class="sum-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="sum-sub">
                                            <?php echo htmlspecialchars($item['Product_SKU']); ?> • Qty
                                            <span class="qty-badge"><?php echo $item['amount']; ?></span>
                                        </div>
                                    </div>
                                    <div class="sum-price">₪<?php echo number_format($item['line_total'], 2); ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-cart">Your cart is empty.</div>
                        <?php endif; ?>
                    </div>

                    <div class="summary-line"></div>

                    <div class="totals">
                        <div class="row"><span>Subtotal</span><span>₪<?php echo number_format($subtotal, 2); ?></span></div>
                        <div class="row"><span>Shipping</span><span>₪<?php echo number_format($shipping, 2); ?></span></div>
                        <div class="row total"><span>Total</span><span>₪<?php echo number_format($total, 2); ?></span></div>
                    </div>

                    <div class="summary-note">
                        Delivery: <b><?php echo ($deliveryMethod === 'pickup') ? 'Pick up' : 'Ship'; ?></b>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</body>

</html>