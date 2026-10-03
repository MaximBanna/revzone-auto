<?php
require('nav.php');

// AUTH CHECK (MANAGER ONLY)
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 1) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];

// HANDLE ACTIONS 
if (isset($_POST['export_selected']) || isset($_POST['assign']) || isset($_POST['cancel']) || isset($_POST['delivered']) || isset($_POST['picked_up'])) {

    // EXPORT SELECTED DELIVERIES
    if (isset($_POST['export_selected'])) {
        // Get selected order IDs
        $selectedOrders = "";
        $orderCount = 0;

        // Count how many orders selected
        if (isset($_POST['selected_orders'])) {
            $i = 0;
            while (isset($_POST['selected_orders'][$i])) {
                $orderCount++;
                $i++;
            }
        }

        if ($orderCount === 0) {
            die("No deliveries selected.");
        }

        // Build the IN clause for SQL
        $i = 0;
        while (isset($_POST['selected_orders'][$i])) {
            $orderId = (int)$_POST['selected_orders'][$i];
            if ($orderId > 0) {
                if ($selectedOrders != "") {
                    $selectedOrders .= ",";
                }
                $selectedOrders .= $orderId;
            }
            $i++;
        }

        if ($selectedOrders == "") {
            die("No valid deliveries selected.");
        }

        // Query selected deliveries
        $q = mysqli_execute_query($conn, "
            SELECT
              d.order_id,
              d.fullname,
              d.email,
              city_ship.name AS ship_city,
              d.address,
              d.status,
              d.delivery_type,
              d.branch_id,
              d.pickup_time_from,
              d.pickup_time_to,
              d.arrival_time,
              d.completed_at,

              b.city    AS branch_city,
              b.address AS branch_address,

              city_ship.region AS ship_region,
              city_pick.region AS pickup_region,

              (SELECT SUM(h.amount * h.price)
                 FROM orderh h
                 WHERE h.order_id = d.order_id
              ) + o.shipping AS order_total,

              c.fname AS courier_fname,
              c.lname AS courier_lname,
              c.phone AS courier_phone

            FROM delivery d
            JOIN orders o ON o.order_id = d.order_id
            LEFT JOIN city city_ship ON d.city_id = city_ship.id
            LEFT JOIN branches b        ON b.id = d.branch_id
            LEFT JOIN city     city_pick ON city_pick.name = b.city
            LEFT JOIN courier  c        ON c.id = d.courier_id
            WHERE d.order_id IN ($selectedOrders)
            ORDER BY d.order_id
        ");

        if (!$q) {
            die("Export query failed.");
        }

        $now = date("d/m/Y H:i");

        // Write HTML header
        echo
        "<!DOCTYPE html>
        <html lang='en'>
            <head>
                <meta charset='UTF-8'>
                <meta name='viewport' content='width=device-width, initial-scale=1.0'>
                <title>RevZone • Deliveries Receipt</title>
                <link rel='stylesheet' href='manageDStyle.css'>
            </head>
            <body class='rz-receipt-body'>
                <div class='rz-receipt-paper'>

                    <div class='rzr-head'>
                        <div>
                            <div class='rzr-brandLine'>
                            <div class='rzr-brand'>REVZONE</div>
                            <div class='rzr-brand2'>AUTO</div>
                            </div>
                            <div class='rzr-small'>Deliveries Receipt • Selected Orders</div>
                        </div>

                        <div class='rzr-meta'>
                            <div class='rzr-metaTitle'>RECEIPT</div>
                            <div class='rzr-small'>Generated: <b>$now</b></div>
                        </div>
                    </div>

                    <div class='rzr-divider'></div>
                    
                    <div class='rzr-items'>";
        $count = 0;
        $grandTotal = 0;
        // Loop through each delivery
        while ($r = mysqli_fetch_assoc($q)) {
            $count++;

            $orderId = (int)$r['order_id'];
            $total = (float)($r['order_total']);
            if ($total == 0) {
                $total = 0;
            }
            $grandTotal = $grandTotal + $total;

            $type = $r['delivery_type'];
            $status = $r['status'];

            $isPickup = 0;
            if ($type === 'pickup') {
                $isPickup = 1;
            }

            // Determine city and address
            if ($isPickup == 1) {
                $city = $r['branch_city'];
                if ($city == "") {
                    $city = 'Pickup';
                }
                $addr = $r['branch_address'];
                if ($addr == "") {
                    $addr = $r['branch_address'];
                }
                $region = $r['pickup_region'];
                if ($region == "") {
                    $region = '—';
                }
            } else {
                $city = $r['ship_city'];
                if ($city == "") {
                    $city = '—';
                }
                $addr = $r['address'];
                if ($addr == "") {
                    $addr = '—';
                }
                $region = $r['ship_region'];
                if ($region == "") {
                    $region = '—';
                }
            }

            // Determine courier info
            if ($isPickup == 1) {
                $courierShow = "Self pickup";
            } else {
                $fn = $r['courier_fname'];
                $ln = $r['courier_lname'];
                $phone = $r['courier_phone'];

                if ($fn == "") {
                    $fn = "";
                }
                if ($ln == "") {
                    $ln = "";
                }
                if ($phone == "") {
                    $phone = "";
                }

                $name = $fn . " " . $ln;
                // Remove extra spaces
                $name = str_replace("  ", " ", $name);
                $name = trim($name);

                if ($name === "") {
                    $name = "—";
                }

                $courierShow = $name;
                if ($phone !== "") {
                    $courierShow = $courierShow . "<br><span style='font-weight:700;color:#6B7280;letter-spacing:.4px;'>$phone</span>";
                }
            }

            // Determine arrival time
            if ($isPickup == 1) {
                $from = $r['pickup_time_from'];
                $to = $r['pickup_time_to'];

                $fromTxt = "—";
                $toTxt = "—";

                if ($from != "") {
                    // Get first 5 characters (HH:MM)
                    $fromTxt = substr($from, 0, 5);
                }
                if ($to != "") {
                    $toTxt = substr($to, 0, 5);
                }

                $arrivalShow = $fromTxt . " - " . $toTxt;
            } else {
                $arrivalShow = "—";
                if ($r['arrival_time'] != "" && $r['arrival_time'] != null) {
                    $arrivalShow = date("d/m/Y H:i", strtotime($r['arrival_time']));
                }
            }

            // Determine completed time
            $completedShow = "Not yet";
            if ($r['completed_at'] != "" && $r['completed_at'] != null) {
                $completedShow = date("d/m/Y H:i", strtotime($r['completed_at']));
            }

            // Status class
            $statusClass = "pending";
            if ($status === "en route") {
                $statusClass = "enroute";
            }
            if ($status === "delivered") {
                $statusClass = "delivered";
            }

            $customer = $r['fullname'];
            if ($customer == "") {
                $customer = '—';
            }

            $email = $r['email'];
            if ($email == "") {
                $email = '—';
            }

            $typeEsc = "Delivery";
            if ($isPickup == 1) {
                $typeEsc = "Pickup";
            }

            $totalFormatted = number_format($total, 2);
            $statusDisplay = $status;
            // Capitalize first letter
            $statusDisplay = ucfirst($statusDisplay);

            // Write item HTML
            echo
            "<div class='rzr-item'>
                                <div class='rzr-itemTop'>
                                    <div>
                                        <div class='rzr-order'>Order #$orderId</div>
                                        <div class='rzr-muted'>$customer • $email</div>
                                    </div>
                                    <div class='rzr-right'>
                                        <div class='rzr-total'>₪$totalFormatted</div>
                                        <div style='margin-top:8px;'>
                                        <span class='rzr-status $statusClass'>$statusDisplay</span>
                                        </div>
                                    </div>
                                </div>

                                <div class='rzr-grid'>
                                    <div class='rzr-field'>
                                        <div class='rzr-label'>Address</div>
                                        <div class='rzr-value'>$city<br>$addr</div>
                                    </div>
                                    <div class='rzr-field'>
                                        <div class='rzr-label'>Region</div>
                                        <div class='rzr-value'>$region</div>
                                    </div>
                                    <div class='rzr-field'>
                                        <div class='rzr-label'>Courier</div>
                                        <div class='rzr-value'>$courierShow</div>
                                    </div>
                                    <div class='rzr-field'>
                                        <div class='rzr-label'>Arrival</div>
                                        <div class='rzr-value'>$arrivalShow</div>
                                    </div>
                                    <div class='rzr-field'>
                                        <div class='rzr-label'>Completed</div>
                                        <div class='rzr-value'>$completedShow</div>
                                    </div>
                                    <div class='rzr-field'>
                                        <div class='rzr-label'>Type</div>
                                        <div class='rzr-value'>$typeEsc</div>
                                    </div>
                                </div>
                            </div>";
        }
        $grandTotalFormatted = number_format($grandTotal, 2);

        // Write footer
        echo
        "</div>

                    <div class='rzr-foot'>
                        <div><b>Total deliveries:</b> $count</div>
                        <div><b>Grand total:</b> ₪$grandTotalFormatted</div>
                        <div class='rzr-note'>Generated by RevZone delivery manager</div>
                    </div>
                </div>
            </body>
        </html>";

        exit;
    }

    // ASSIGN COURIER & SEND (ship only)
    if (isset($_POST['assign'])) {
        $orderId = (int)$_POST['order_id'];
        $courierId = (int)$_POST['courier_id'];
        $arrivalTime = $_POST['arrival_time'];

        // Get customer and courier details
        $detailsQuery = mysqli_execute_query($conn, "SELECT d.email, d.fullname, o.user_id, c.fname, c.lname, c.phone FROM delivery d
            JOIN orders o ON o.order_id = d.order_id LEFT JOIN courier c ON c.id = ? WHERE d.order_id = ?", [$courierId, $orderId]);
        $details = mysqli_fetch_assoc($detailsQuery);

        mysqli_execute_query($conn, "UPDATE delivery SET status = 'en route', courier_id = ?, arrival_time = ?
            WHERE order_id = ?", [$courierId, $arrivalTime, $orderId]);

        // Send email to customer
        $customerEmail = $details['email'];
        $customerName = $details['fullname'];
        $username = $details['user_id'];
        $courierName = $details['fname'] . ' ' . $details['lname'];
        $courierPhone = $details['phone'];

        // Format arrival date and time
        $arrivalFormatted = date("d/m/Y", strtotime($arrivalTime));
        $arrivalTimeFormatted = date("H:i", strtotime($arrivalTime));

        $mainUrl = SITE_URL . "/logIn.php";
        $subject = "Your Order is On The Way! 🚚";
        $message  = "<b>Hello $customerName,</b>";
        $message .= "<p>Great news! Your order <strong>#$orderId</strong> is now on the way to you.</p>";
        $message .= "<h3 style='color:#2D7DFF;'>Delivery Details:</h3>";
        $message .= "<p><strong>Courier:</strong> $courierName<br>";
        $message .= "<strong>Phone:</strong> $courierPhone<br>";
        $message .= "<strong>Expected Arrival Date:</strong> $arrivalFormatted<br>";
        $message .= "<strong>Expected Arrival Time:</strong> $arrivalTimeFormatted</p>";
        $message .= "<p>Please make sure someone is available to receive the package at the expected time.</p>";
        $message .= "
        <p>
            Track your order:
            <br>
            <a href='$mainUrl' 
            style='display:inline-block;
                    background:#2D7DFF;
                    color:#fff;
                    padding:12px 20px;
                    text-decoration:none;
                    border-radius:5px;
                    margin-top:10px;'>
                View Order Status
            </a>
        </p>";
        $message .= "<p>If you have any questions, feel free to contact us.</p>";
        $message .= "<p>Thank you for choosing Revzone!<br>The Revzone Delivery Team</p>";

        $header  = "From: Revzone Delivery <" . MAIL_FROM . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=UTF-8\r\n";
        $retval = mail($customerEmail, $subject, $message, $header);
    }

    // CANCEL ORDER
    if (isset($_POST['cancel'])) {
        $orderId = (int)$_POST['order_id'];

        // Get customer email and order total before deleting
        $emailQuery = mysqli_execute_query($conn, "SELECT d.email, d.fullname, o.user_id, SUM(oh.amount * oh.price) + o.shipping AS total
            FROM delivery d JOIN orders o ON o.order_id = d.order_id JOIN orderh oh ON oh.order_id = d.order_id
            WHERE d.order_id = ? GROUP BY d.order_id", [$orderId]);

        $emailData = mysqli_fetch_assoc($emailQuery);
        $customerEmail = $emailData['email'];
        $customerName = $emailData['fullname'];
        $username = $emailData['user_id'];
        $orderTotal = $emailData['total'];

        // Delete order
        mysqli_begin_transaction($conn);

        // put the items back in stock before deleting the order
        mysqli_execute_query($conn, "UPDATE products JOIN orderh ON orderh.product_SKU = products.Product_SKU
            SET products.stock = products.stock + orderh.amount WHERE orderh.order_id = ?", [$orderId]);
        mysqli_execute_query($conn, "DELETE FROM delivery WHERE order_id = ?", [$orderId]);
        mysqli_execute_query($conn, "DELETE FROM orderh WHERE order_id = ?", [$orderId]);
        mysqli_execute_query($conn, "DELETE FROM orders WHERE order_id = ?", [$orderId]);
        mysqli_commit($conn);

        // Send cancellation email to customer
        $mainUrl = SITE_URL . "/logIn.php";
        $subject = "Order Canceled - Revzone";
        $message  = "<b>Hello $customerName,</b>";
        $message .= "<p>We regret to inform you that your order <strong>#$orderId</strong> has been canceled.</p>";
        $message .= "<h3 style='color:#FF3B30;'>Order Details:</h3>";
        $message .= "<p><strong>Order ID:</strong> #$orderId<br>";
        $message .= "<strong>Order Total:</strong> ₪" . number_format($orderTotal, 2) . "</p>";
        $message .= "<p>If you have any questions about this cancellation or if you believe this was done in error, please contact our support team immediately.</p>";
        $message .= "
        <p>
            You can place a new order anytime:
            <br>
            <a href='$mainUrl' 
            style='display:inline-block;
                    background:#F2F2F2;
                    color:#0D0D0D;
                    padding:12px 20px;
                    text-decoration:none;
                    border-radius:5px;
                    margin-top:10px;'>
                Browse Products
            </a>
        </p>";
        $message .= "<p>We apologize for any inconvenience this may have caused.</p>";
        $message .= "<p>Thank you for your understanding,<br>The Revzone Support Team</p>";

        $header  = "From: Revzone Support <" . MAIL_FROM . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=UTF-8\r\n";
        $retval = mail($customerEmail, $subject, $message, $header);

        // Set success message
        $_SESSION['cancel_message'] = "Order #$orderId has been canceled. Cancellation email sent to $customerName ($customerEmail).";
    }

    // SET DELIVERED (ship only)
    if (isset($_POST['delivered'])) {
        $orderId = (int)$_POST['order_id'];

        // Get customer details
        $detailsQuery = mysqli_execute_query($conn, "SELECT d.email, d.fullname, o.user_id FROM delivery d
            JOIN orders o ON o.order_id = d.order_id WHERE d.order_id = ?", [$orderId]);

        $details = mysqli_fetch_assoc($detailsQuery);

        // Update delivery status
        mysqli_execute_query($conn, "UPDATE delivery SET status = 'delivered', completed_at = NOW() WHERE order_id = ?", [$orderId]);

        // Send delivery confirmation email
        $customerEmail = $details['email'];
        $customerName = $details['fullname'];
        $username = $details['user_id'];

        $mainUrl = SITE_URL . "/logIn.php";
        $subject = "Order Delivered Successfully!";
        $message  = "<b>Hello $customerName,</b>";
        $message .= "<p>Great news! Your order <strong>#$orderId</strong> has been delivered successfully.</p>";
        $message .= "<p>We hope you enjoy your purchase!</p>";
        $message .= "<p>If you have any issues with your order or if you did not receive it, please contact us immediately.</p>";
        $message .= "
        <p>
            View your order history At my orders page:
            <br>
            <a href='$mainUrl' 
            style='display:inline-block;
                    background:#18D37A;
                    color:#fff;
                    padding:12px 20px;
                    text-decoration:none;
                    border-radius:5px;
                    margin-top:10px;'>
                My Orders
            </a>
        </p>";
        $message .= "<p>Thank you for shopping with Revzone!<br>The Revzone Team</p>";

        $header  = "From: Revzone <" . MAIL_FROM . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=UTF-8\r\n";
        $retval = mail($customerEmail, $subject, $message, $header);
    }

    // PICKED UP (pickup only)
    if (isset($_POST['picked_up'])) {
        $orderId = (int)$_POST['order_id'];

        // Get customer and pickup details
        $detailsQuery = mysqli_execute_query($conn, "SELECT 
        d.email,
        d.fullname,
        o.user_id,
        b.city AS branch_city
        FROM delivery d
        JOIN orders o ON o.order_id = d.order_id
        LEFT JOIN branches b ON b.id = d.branch_id
        WHERE d.order_id = ?", [$orderId]);
        $details = mysqli_fetch_assoc($detailsQuery);

        // Update delivery status
        mysqli_execute_query($conn, "UPDATE delivery SET status = 'delivered', completed_at = NOW() WHERE order_id = ?", [$orderId]);

        // Send pickup confirmation email
        $customerEmail = $details['email'];
        $customerName = $details['fullname'];
        $username = $details['user_id'];
        $pickupBranch = $details['branch_city'];

        $mainUrl = SITE_URL . "/logIn.php";
        $subject = "Order Picked Up Successfully!";
        $message  = "<b>Hello $customerName,</b>";
        $message .= "<p>Thank you for picking up your order <strong>#$orderId</strong> from our <strong>$pickupBranch</strong> branch.</p>";
        $message .= "<p>We hope you enjoy your purchase!</p>";
        $message .= "<p>If you have any issues with your order, please contact us.</p>";
        $message .= "
        <p>
            View your order history:
            <br>
            <a href='$mainUrl' 
            style='display:inline-block;
                    background:#18D37A;
                    color:#fff;
                    padding:12px 20px;
                    text-decoration:none;
                    border-radius:5px;
                    margin-top:10px;'>
                My Orders
            </a>
        </p>";
        $message .= "<p>Thank you for shopping with Revzone!<br>The Revzone Team</p>";

        $header  = "From: Revzone <" . MAIL_FROM . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-type: text/html; charset=UTF-8\r\n";
        $retval = mail($customerEmail, $subject, $message, $header);
    }

    // Redirect back with filters
    $redirectUrl = "manageD.php";
    $hasParams = 0;

    // Rebuild URL with GET parameters
    if (isset($_GET['delivery_type'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "delivery_type=" . $_GET['delivery_type'];
    }

    if (isset($_GET['status'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "status=" . $_GET['status'];
    }

    if (isset($_GET['customer'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "customer=" . $_GET['customer'];
    }

    if (isset($_GET['product'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "product=" . $_GET['product'];
    }

    if (isset($_GET['month'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "month=" . $_GET['month'];
    }

    if (isset($_GET['year'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "year=" . $_GET['year'];
    }

    if (isset($_GET['sort_total'])) {
        if ($hasParams == 0) {
            $redirectUrl = $redirectUrl . "?";
            $hasParams = 1;
        } else {
            $redirectUrl = $redirectUrl . "&";
        }
        $redirectUrl = $redirectUrl . "sort_total=" . $_GET['sort_total'];
    }

    header("Location: $redirectUrl");
    exit;
}

// FILTERS
$delivery_type_filter = "all";
if (isset($_GET['delivery_type'])) {
    $delivery_type_filter = $_GET['delivery_type'];
}

// Validate delivery type
if ($delivery_type_filter != "all" && $delivery_type_filter != "ship" && $delivery_type_filter != "pickup") {
    $delivery_type_filter = "all";
}

$filter = 'all';
if (isset($_GET['status'])) {
    $filter = $_GET['status'];
}

// Validate status
if ($filter !== 'all' && $filter !== 'pending' && $filter !== 'en route' && $filter !== 'delivered') {
    $filter = 'all';
}

$month = 0;
if (isset($_GET['month'])) {
    $month = (int)$_GET['month'];
}

$year = 0;
if (isset($_GET['year'])) {
    $year = (int)$_GET['year'];
}

$customer = '';
if (isset($_GET['customer'])) {
    $customer = trim($_GET['customer']);
}

$product = '';
if (isset($_GET['product'])) {
    $product = trim($_GET['product']);
}

$sort_total = '';
if (isset($_GET['sort_total'])) {
    $sort_total = trim($_GET['sort_total']);
}

// Build WHERE clause
$whereConditions = "";
$params = [];
$conditionCount = 0;

// Status filter
if ($filter == "pending") {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "delivery.status = 'pending'";
    $conditionCount++;
}
if ($filter == "en route") {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "delivery.status = 'en route'";
    $conditionCount++;
}
if ($filter == "delivered") {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "delivery.status = 'delivered'";
    $conditionCount++;
}

// Delivery type filter
if ($delivery_type_filter == "ship") {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "delivery.delivery_type = 'ship'";
    $conditionCount++;
}
if ($delivery_type_filter == "pickup") {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "delivery.delivery_type = 'pickup'";
    $conditionCount++;
}

// Month filter
if ($month >= 1 && $month <= 12) {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "MONTH(orders.order_date) = ?";
    $params[] = $month;
    $conditionCount++;
}

// Year filter
if ($year >= 2000) {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "YEAR(orders.order_date) = ?";
    $params[] = $year;
    $conditionCount++;
}

// Customer filter
if ($customer !== '') {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "orders.user_id = ?";
    $params[] = $customer;
    $conditionCount++;
}

// Product filter
if ($product !== '') {
    if ($conditionCount > 0) {
        $whereConditions = $whereConditions . " AND ";
    }
    $whereConditions = $whereConditions . "EXISTS (SELECT 1 FROM orderh oh2 JOIN products p2 ON p2.Product_SKU = oh2.product_SKU WHERE oh2.order_id = delivery.order_id AND (oh2.product_SKU = ? OR p2.name LIKE ?))";
    $params[] = $product;
    $params[] = "%$product%";
    $conditionCount++;
}

$whereSql = "";
if ($conditionCount > 0) {
    $whereSql = "WHERE " . $whereConditions;
}

// ORDER BY 
$orderBy = "ORDER BY delivery.order_id ASC";
if ($sort_total === 'asc') {
    $orderBy = "ORDER BY order_total ASC";
}
if ($sort_total === 'desc') {
    $orderBy = "ORDER BY order_total DESC";
}

// LOAD DELIVERIES 
$query = "
SELECT 
    delivery.order_id,
    delivery.fullname,
    delivery.email,
    city_ship.name AS ship_city,
    delivery.address,
    delivery.status,
    delivery.courier_id,
    delivery.arrival_time,
    delivery.delivery_type,
    delivery.branch_id,
    branches.name AS branch_name,
    delivery.pickup_time_from,
    delivery.pickup_time_to,
    delivery.completed_at,

    branches.city    AS branch_city,
    branches.address AS branch_address,

    city_ship.region   AS ship_region,
    city_pick.region   AS pickup_region,

    orders.user_id,
    orders.order_date,

    SUM(orderh.amount * orderh.price) + orders.shipping AS order_total

    FROM delivery

    JOIN orders
        ON delivery.order_id = orders.order_id

    JOIN orderh
        ON orderh.order_id = delivery.order_id

    LEFT JOIN city AS city_ship
        ON delivery.city_id = city_ship.id

    LEFT JOIN branches
        ON delivery.branch_id = branches.id

    LEFT JOIN city AS city_pick
        ON branches.city = city_pick.name

    $whereSql

    GROUP BY delivery.order_id

    $orderBy
    ";

$deliveries = mysqli_execute_query($conn, $query, $params);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Deliveries</title>
    <link rel="stylesheet" href="manageDStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <main class="page">

        <!-- COMBINED FILTERS ROW -->
        <div class="top-filters-combined">

            <!-- DELIVERY TYPE (LEFT) -->
            <form method="get" action="manageD.php" class="filter-form-left">
                <input type="hidden" name="status" value="<?php echo $filter; ?>">

                <button type="submit" name="delivery_type" value="all"
                    class="filter-btn <?php if ($delivery_type_filter == 'all') echo 'active'; ?>">All Types</button>

                <button type="submit" name="delivery_type" value="ship"
                    class="filter-btn <?php if ($delivery_type_filter == 'ship') echo 'active'; ?>">Shipping</button>

                <button type="submit" name="delivery_type" value="pickup"
                    class="filter-btn <?php if ($delivery_type_filter == 'pickup') echo 'active'; ?>">Pickup</button>
            </form>

            <!-- STATUS (RIGHT) -->
            <form method="get" action="manageD.php" class="filter-form-right">
                <input type="hidden" name="delivery_type" value="<?php echo $delivery_type_filter; ?>">

                <button type="submit" name="status" value="all"
                    class="filter-btn <?php if ($filter == 'all') echo 'active'; ?>">All</button>

                <button type="submit" name="status" value="pending"
                    class="filter-btn <?php if ($filter == 'pending') echo 'active'; ?>">Pending</button>

                <button type="submit" name="status" value="en route"
                    class="filter-btn <?php if ($filter == 'en route') echo 'active'; ?>">En Route</button>

                <button type="submit" name="status" value="delivered"
                    class="filter-btn <?php if ($filter == 'delivered') echo 'active'; ?>">Delivered</button>
            </form>
        </div>

        <!-- MANAGER FILTERS BAR -->
        <form class="mgr-filters" method="get" action="manageD.php">
            <input type="hidden" name="status" value="<?php echo $filter; ?>">
            <input type="hidden" name="delivery_type" value="<?php echo $delivery_type_filter; ?>">

            <div class="mgr-row">
                <input class="mgr-in" type="text" name="customer" placeholder="Customer (username)"
                    value="<?php echo htmlspecialchars($customer); ?>">

                <input class="mgr-in" type="text" name="product" placeholder="Product (SKU or name)"
                    value="<?php echo htmlspecialchars($product); ?>">

                <select class="mgr-in" name="month">
                    <option value="0">Month</option>
                    <?php
                    $m = 1;
                    while ($m <= 12) {
                        $selected = "";
                        if ($month === $m) {
                            $selected = "selected";
                        }
                        echo "<option value='$m' $selected>$m</option>";
                        $m++;
                    }
                    ?>
                </select>

                <select class="mgr-in" name="year">
                    <option value="0">Year</option>
                    <?php
                    $currentYear = (int)date('Y');
                    $startYear = $currentYear - 3;
                    $endYear = $currentYear + 1;

                    $y = $startYear;
                    while ($y <= $endYear) {
                        $selected = "";
                        if ($year === $y) {
                            $selected = "selected";
                        }
                        echo "<option value='$y' $selected>$y</option>";
                        $y++;
                    }
                    ?>
                </select>
            </div>

            <div class="mgr-row">
                <select class="mgr-in" name="sort_total">
                    <option value="">Sort total</option>
                    <option value="asc" <?php if ($sort_total === 'asc') echo 'selected'; ?>>Lowest first</option>
                    <option value="desc" <?php if ($sort_total === 'desc') echo 'selected'; ?>>Highest first</option>
                </select>

                <button class="mgr-btn" type="submit">Apply Filters</button>
            </div>
        </form>

        <section class="panel">
            <h1>Manage Deliveries</h1>

            <?php if (isset($_SESSION['cancel_message'])): ?>
                <div style="background: rgba(24,211,122,.15); padding: 15px; border-radius: 10px; border: 1px solid rgba(24,211,122,.35); margin-bottom: 20px;">
                    <p style="margin: 0; color: var(--accent); font-size: 14px;">
                        <b>✓ Success:</b> <?php echo htmlspecialchars($_SESSION['cancel_message']); ?>
                    </p>
                </div>
            <?php
                unset($_SESSION['cancel_message']);
            endif;
            ?>

            <form method="post" class="export-form" id="exportForm" target="_blank">
                <button class="mgr-btn export-btn" type="submit" name="export_selected">
                    Export Selected Receipt
                </button>
                <span class="export-hint">Select deliveries below, then export.</span>
            </form>

            <table>
                <thead>
                    <tr>
                        <th class="sel-col">Select</th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Region</th>
                        <th>Courier</th>
                        <th>Arrival</th>
                        <th>Status</th>
                        <th>Completed</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>

                    <?php if ($deliveries): ?>
                        <?php while ($d = mysqli_fetch_assoc($deliveries)): ?>

                            <?php
                            $isPickup = 0;
                            if ($d['delivery_type'] == 'pickup') {
                                $isPickup = 1;
                            }

                            $region = "";
                            if ($isPickup == 1) {
                                $region = $d['pickup_region'];
                            } else {
                                $region = $d['ship_region'];
                            }

                            if ($region == "") {
                                $region = "";
                            }

                            $displayCity = "";
                            $displayAddr = "";

                            if ($isPickup == 1) {
                                $displayCity = $d['branch_city'];
                                $displayAddr = $d['branch_address'];
                            } else {
                                $displayCity = $d['ship_city'];
                                $displayAddr = $d['address'];
                            }

                            if ($displayCity == "") {
                                $displayCity = "";
                            }
                            if ($displayAddr == "") {
                                $displayAddr = "";
                            }

                            $pickupBranchName = $d['branch_name'];
                            if ($pickupBranchName == "") {
                                $pickupBranchName = "";
                            }

                            $couriers = null;
                            if ($isPickup == 0 && $d['status'] == 'pending' && $region != "") {
                                $couriers = mysqli_execute_query($conn, "SELECT * FROM courier WHERE region = ?", [$region]);
                            }

                            $cr = null;
                            if ($isPickup == 0 && $d['status'] != 'pending' && $d['courier_id'] != "" && $d['courier_id'] != null) {
                                $cid = (int)$d['courier_id'];
                                $courierQuery = mysqli_execute_query($conn, "SELECT fname,lname,phone FROM courier WHERE id = ?", [$cid]);
                                if ($courierQuery) {
                                    $cr = mysqli_fetch_assoc($courierQuery);
                                }
                            }
                            ?>

                            <tr>

                                <td class="sel-col">
                                    <input class="sel-check" type="checkbox"
                                        name="selected_orders[]"
                                        value="<?php echo (int)$d['order_id']; ?>"
                                        form="exportForm">
                                </td>

                                <td>
                                    <b><?php echo (int)$d['order_id']; ?></b>
                                    <span class="muted">₪<?php echo number_format((float)$d['order_total'], 2); ?></span>
                                </td>

                                <td>
                                    <b><?php echo htmlspecialchars($d['user_id']); ?></b>
                                    <span class="muted"><?php echo htmlspecialchars($d['email']); ?></span>
                                </td>

                                <td>
                                    <?php if ($isPickup == 1): ?>
                                        <b><?php
                                            if ($displayCity != "") {
                                                echo htmlspecialchars($displayCity);
                                            } else {
                                                echo "Pickup";
                                            }
                                            ?></b>
                                        <span class="muted">
                                            <?php
                                            if ($displayAddr != "") {
                                                echo htmlspecialchars($displayAddr);
                                            } else {
                                                echo htmlspecialchars($pickupBranchName);
                                            }
                                            ?>
                                        </span>
                                    <?php else: ?>
                                        <b><?php echo htmlspecialchars($displayCity); ?></b>
                                        <span class="muted"><?php echo htmlspecialchars($displayAddr); ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <span class="pill blue">
                                        <?php
                                        if ($region != "") {
                                            echo htmlspecialchars($region);
                                        } else {
                                            echo "—";
                                        }
                                        ?>
                                    </span>
                                </td>

                                <form method="post">

                                    <td>
                                        <?php if ($isPickup == 1): ?>
                                            <b>Self pickup</b>
                                            <span class="muted"><?php echo htmlspecialchars($pickupBranchName); ?></span>

                                        <?php else: ?>

                                            <?php if ($d['status'] == 'pending'): ?>
                                                <select name="courier_id" class="input-like" required>
                                                    <option value="">Select courier</option>

                                                    <?php if ($couriers): ?>
                                                        <?php while ($c = mysqli_fetch_assoc($couriers)): ?>
                                                            <option value="<?php echo (int)$c['id']; ?>">
                                                                <?php echo htmlspecialchars($c['fname'] . ' ' . $c['lname'] . ' • ' . $c['phone']); ?>
                                                            </option>
                                                        <?php endwhile; ?>
                                                    <?php endif; ?>

                                                </select>
                                            <?php else: ?>
                                                <?php if ($cr): ?>
                                                    <b><?php echo htmlspecialchars($cr['fname'] . ' ' . $cr['lname']); ?></b>
                                                    <span class="muted"><?php echo htmlspecialchars($cr['phone']); ?></span>
                                                <?php else: ?>
                                                    <b>—</b>
                                                <?php endif; ?>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php if ($isPickup == 1): ?>

                                            <?php
                                            $from = $d['pickup_time_from'];
                                            $to = $d['pickup_time_to'];

                                            if ($from == "") {
                                                $from = "";
                                            }
                                            if ($to == "") {
                                                $to = "";
                                            }

                                            $fromTxt = "—";
                                            if ($from != "") {
                                                $fromTxt = substr($from, 0, 5);
                                            }

                                            $toTxt = "—";
                                            if ($to != "") {
                                                $toTxt = substr($to, 0, 5);
                                            }
                                            ?>
                                            <b><?php echo $fromTxt . " - " . $toTxt; ?></b>

                                        <?php else: ?>

                                            <?php if ($d['status'] == 'pending'): ?>
                                                <input type="datetime-local" name="arrival_time" class="input-like" required>
                                            <?php else: ?>
                                                <?php
                                                if ($d['arrival_time'] != "" && $d['arrival_time'] != null) {
                                                    echo "<b>" . date('d/m/y H:i', strtotime($d['arrival_time'])) . "</b>";
                                                } else {
                                                    echo "<b>—</b>";
                                                }
                                                ?>
                                            <?php endif; ?>

                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <?php
                                        $pillClass = "green";
                                        if ($d['status'] == "pending") {
                                            $pillClass = "orange";
                                        }
                                        if ($d['status'] == "en route") {
                                            $pillClass = "blue";
                                        }
                                        ?>
                                        <span class="pill <?php echo $pillClass; ?>">
                                            <?php
                                            if ($d['status'] == "delivered" && $isPickup == 1) {
                                                echo "Picked up";
                                            } else {
                                                echo ucfirst($d['status']);
                                            }
                                            ?>
                                        </span>
                                    </td>

                                    <td>
                                        <?php
                                        if ($d['completed_at'] != "" && $d['completed_at'] != null) {
                                            echo "<b>" . date('d/m/y H:i', strtotime($d['completed_at'])) . "</b>";
                                        } else {
                                            echo "<span class='muted'>Not yet</span>";
                                        }
                                        ?>
                                    </td>

                                    <td class="actions">
                                        <input type="hidden" name="order_id" value="<?php echo (int)$d['order_id']; ?>">

                                        <?php if ($d['status'] == 'pending' && $isPickup == 1): ?>
                                            <button name="picked_up" class="action-btn good" type="submit">Picked up</button>

                                        <?php elseif ($d['status'] == 'pending'): ?>
                                            <button name="assign" class="action-btn good" type="submit">Send</button>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo (int)$d['order_id']; ?>">
                                    <button name="cancel" class="action-btn danger" type="submit"
                                        onclick="return confirm('Are you sure you want to cancel Order #<?php echo $d['order_id']; ?>?\n\nCustomer: <?php echo $d['user_id']; ?>\nThis action cannot be undone.');">
                                        Cancel
                                    </button>
                                </form>
                                <form method="post">

                                <?php elseif ($d['status'] == 'en route'): ?>
                                    <button name="delivered" class="action-btn good" type="submit">Set Delivered</button>

                                <?php else: ?>
                                    <div class="delivered-gif">
                                        <img src="img/pd.gif" alt="Delivered">
                                    </div>
                                <?php endif; ?>

                                </td>
                                </form>

                            </tr>

                        <?php endwhile; ?>
                    <?php endif; ?>

                </tbody>
            </table>
        </section>
    </main>
</body>

</html>