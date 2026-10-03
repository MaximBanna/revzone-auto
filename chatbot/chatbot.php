<?php
require_once('../nav.php');

/* ================================
   SETTINGS
================================ */
$BASE_URL = ".";

/* ================================
   READ MESSAGE
================================ */
$msg = "";
if (isset($_POST['message'])) {
    $msg = trim($_POST['message']);
}
$msgLower = strtolower($msg);

/* ================================
   RESET / MENU WORDS
================================ */
if (
    !isset($_SESSION['chat_step']) ||
    $msgLower == "hi" ||
    $msgLower == "hello" ||
    $msgLower == "start" ||
    $msgLower == "reset" ||
    $msgLower == "menu"
) {

    $_SESSION['chat_step'] = "menu";

    $_SESSION['brand']  = "";
    $_SESSION['model']  = "";
    $_SESSION['year']   = "";
    $_SESSION['engine'] = "";

    echo "👋 Welcome to <b>RevZone Assistant</b>!<br><br>";
    echo "Choose an option:<br>";
    echo "1️⃣ <b>Check Part Fitment</b><br>";
    echo "2️⃣ <b>Track Delivery</b><br><br>";
    echo "Type <b>1</b> or <b>2</b>.";
    exit;
}

$step = $_SESSION['chat_step'];

/* ================================
   STEP: MENU
================================ */
if ($step == "menu") {

    if ($msg == "1") {
        $_SESSION['chat_step'] = "brand";
        echo "🚗 Enter your <b>Car Brand</b> (example: BMW, Subaru):";
        exit;
    }

    if ($msg == "2") {
        if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
            echo "🔒 Please log in to track your orders.<br>Type <b>menu</b> to go back.";
            exit;
        }
        $_SESSION['chat_step'] = "delivery_id";
        echo "📦 Enter your <b>Order ID</b> (numbers only):";
        exit;
    }

    echo "🤔 Please type <b>1</b> (parts) or <b>2</b> (delivery).";
    exit;
}

/* ================================
   STEP: DELIVERY TRACKING
================================ */
if ($step == "delivery_id") {

    if ($msg == "" || !ctype_digit($msg)) {
        echo "⚠️ Please enter a valid numeric <b>Order ID</b>:";
        exit;
    }

    $orderId = (int)$msg;

    $q = mysqli_execute_query($conn, "
    SELECT
        d.status,
        d.delivery_type,
        city.name        AS city_name,
        d.address,
        d.arrival_time,
        b.name           AS branch_name,
        d.pickup_time_from,
        d.pickup_time_to,
        d.completed_at,
        c.fname          AS courier_fname,
        c.lname          AS courier_lname,
        c.phone          AS courier_phone
        FROM delivery d
    JOIN orders o ON o.order_id = d.order_id
    LEFT JOIN city     ON d.city_id = city.id
    LEFT JOIN branches b ON d.branch_id = b.id
    LEFT JOIN courier c  ON d.courier_id = c.id
    WHERE d.order_id = ? AND o.user_id = ?
    LIMIT 1
    ", [$orderId, $_SESSION['username']]);


    if (!$q || mysqli_num_rows($q) == 0) {
        echo "❌ Order <b>#$orderId</b> not found.<br>";
        echo "Type <b>menu</b> to go back.";
        exit;
    }

    $r = mysqli_fetch_assoc($q);

    $status = $r['status'];
    if ($status == "") $status = "pending";

    if ($status == "pending")   $statusShow = "⏳ Pending";
    if ($status == "en route")  $statusShow = "🚚 En route";
    if ($status == "delivered") $statusShow = "✅ Delivered";

    echo "🔎 Order <b>#$orderId</b><br>";
    echo "<b>Status:</b> $statusShow<br><br>";

    if ($r['delivery_type'] == "pickup") {

        $branch = $r['branch_name'];
        if ($branch == "") $branch = "Branch not set";

        $from = $r['pickup_time_from'];
        $to   = $r['pickup_time_to'];

        $fromTxt = ($from != "") ? substr($from, 0, 5) : "—";
        $toTxt   = ($to   != "") ? substr($to, 0, 5)   : "—";

        echo "🏬 <b>Pickup branch:</b> " . htmlspecialchars($branch) . "<br>";
        echo "🕒 <b>Pickup hours:</b> $fromTxt - $toTxt<br>";
    } else {

        $city = $r['city_name'];
        $addr = $r['address'];
        if ($city == "") $city = "—";
        if ($addr == "") $addr = "—";

        echo "📍 <b>Shipping to:</b> " . htmlspecialchars($city . " " . $addr) . "<br>";

        if ($r['arrival_time'] != "") {
            echo "⏱️ <b>Estimated arrival:</b> "
                . date("d/m/Y H:i", strtotime($r['arrival_time'])) . "<br>";
        }

        $name = trim($r['courier_fname'] . " " . $r['courier_lname']);
        if ($name == "") $name = "—";

        echo "🚚 <b>Courier:</b> $name<br>";
        if ($r['courier_phone'] != "") {
            echo "📞 <b>Phone:</b> " . $r['courier_phone'] . "<br>";
        }
    }

    echo "<br>Type <b>menu</b> to go back.";
    $_SESSION['chat_step'] = "done";
    exit;
}

/* ================================
   STEP: BRAND
================================ */
if ($step == "brand") {

    if ($msg == "") {
        echo "Please enter a car brand:";
        exit;
    }

    $q = mysqli_execute_query($conn, "
        SELECT id FROM vehicles
        WHERE brand LIKE ?
        LIMIT 1
    ", ["%$msg%"]);

    if (!$q || mysqli_num_rows($q) == 0) {
        echo "🔍 Brand '<b>" . htmlspecialchars($msg) . "</b>' not found. Try again:";
        exit;
    }

    $_SESSION['brand'] = $msg;
    $_SESSION['chat_step'] = "model";
    echo "✅ Now enter the <b>model</b> for <b>" . htmlspecialchars($msg) . "</b>:";
    exit;
}

/* ================================
   STEP: MODEL
================================ */
if ($step == "model") {

    if ($msg == "") {
        echo "Please enter the car model:";
        exit;
    }

    $brand = $_SESSION['brand'];

    $q = mysqli_execute_query($conn, "
        SELECT id FROM vehicles
        WHERE brand LIKE ?
          AND model LIKE ?
        LIMIT 1
    ", ["%$brand%", "%$msg%"]);

    if (!$q || mysqli_num_rows($q) == 0) {
        echo "🔍 Model '<b>" . htmlspecialchars($msg) . "</b>' not found for <b>" . htmlspecialchars($brand) . "</b>. Try again:";
        exit;
    }

    $_SESSION['model'] = $msg;
    $_SESSION['chat_step'] = "year";
    echo "📅 Enter the <b>year</b> (example: 2018):";
    exit;
}

/* ================================
   STEP: YEAR
================================ */
if ($step == "year") {

    if ($msg == "" || !ctype_digit($msg)) {
        echo "⚠️ Please enter a valid year:";
        exit;
    }

    $year  = (int)$msg;
    $brand = $_SESSION['brand'];
    $model = $_SESSION['model'];

    $q = mysqli_execute_query($conn, "
        SELECT id FROM vehicles
        WHERE brand LIKE ?
          AND model LIKE ?
          AND (? BETWEEN yearFrom AND yearTo)
        LIMIT 1
    ", ["%$brand%", "%$model%", $year]);

    if (!$q || mysqli_num_rows($q) == 0) {
        echo "📅 No data for '<b>" . htmlspecialchars($brand . " " . $model) . "</b>' in '<b>" . htmlspecialchars($year) . "</b>'. Try again:";
        exit;
    }

    $_SESSION['year'] = $year;
    $_SESSION['chat_step'] = "engine";
    echo "⚙️ Enter engine or type <b>skip</b>:";
    exit;
}

/* ================================
   STEP: ENGINE
================================ */
if ($step == "engine") {

    $_SESSION['engine'] = ($msgLower == "skip") ? "" : $msg;
    $_SESSION['chat_step'] = "category";
    echo "📂 What category? (Oil, Brakes, Filters):";
    exit;
}

/* ================================
   STEP: CATEGORY + PRODUCTS
================================ */
if ($step == "category") {

    if ($msg == "") {
        echo "Please enter a category:";
        exit;
    }

    $catQ = mysqli_execute_query($conn, "
        SELECT category_id FROM category
        WHERE name LIKE ?
        LIMIT 1
    ", ["%$msg%"]);

    if (!$catQ || mysqli_num_rows($catQ) == 0) {
        echo "📂 Category not found. Try again:";
        exit;
    }

    $cat = mysqli_fetch_assoc($catQ);
    $cid = (int)$cat['category_id'];

    $brand  = $_SESSION['brand'];
    $model  = $_SESSION['model'];
    $year   = (int)$_SESSION['year'];
    $engine = $_SESSION['engine'];

    $sql = "
        SELECT id FROM vehicles
        WHERE brand LIKE ?
          AND model LIKE ?
          AND (? BETWEEN yearFrom AND yearTo)
    ";
    $params = ["%$brand%", "%$model%", $year];
    if ($engine != "") {
        $sql .= " AND engine LIKE ?";
        $params[] = "%$engine%"; // add a value when you add a ?
    }
    $vQ = mysqli_execute_query($conn, $sql, $params);
    if (!$vQ || mysqli_num_rows($vQ) == 0) {
        echo "⚠️ Vehicle not found. Type <b>menu</b>.";
        exit;
    }

    $v = mysqli_fetch_assoc($vQ);
    $vid = (int)$v['id'];

    $ruleQ = mysqli_execute_query($conn, "
        SELECT required_keyword
        FROM vehiclesr
        WHERE vehicle_id = ?
          AND category_id = ?
        LIMIT 1
    ", [$vid, $cid]);

    if (!$ruleQ || mysqli_num_rows($ruleQ) == 0) {
        echo "⚠️ No parts mapped. Type <b>menu</b>.";
        exit;
    }

    $rule = mysqli_fetch_assoc($ruleQ);
    $keyword = $rule['required_keyword'];

    $pQ = mysqli_execute_query($conn, "
        SELECT name, price, image
        FROM products
        WHERE category_id = ?
          AND active = 1
          AND (name LIKE ? OR Product_SKU LIKE ?)
    ", [$cid, "%$keyword%", "%$keyword%"]);

    if (!$pQ || mysqli_num_rows($pQ) == 0) {
        echo "📦 No parts available. Type <b>menu</b>.";
        exit;
    }

    echo "✅ Parts for <b>" . htmlspecialchars($brand . " " . $model . " (" . $year . ")") . "</b>:<br>";
    echo "<div class='product-cards'>";

    while ($p = mysqli_fetch_assoc($pQ)) {

        $img = $p['image'];
        if ($img == "") $img = "img/no-image.png";

        $imgSrc = $BASE_URL . "/" . $img;
        if ($img[0] == "/") {
            $imgSrc = $BASE_URL . $img;
        }

        echo "
        <div class='product-card'>
          <img src='$imgSrc'>
          <div class='pc-body'>
            <div class='pc-name'>{$p['name']}</div>
            <div class='pc-price'>₪" . number_format($p['price'], 2) . "</div>
          </div>
        </div>";
    }

    echo "</div><br>Type <b>menu</b> to go back.";
    $_SESSION['chat_step'] = "done";
    exit;
}

/* ================================
   STEP: DONE
================================ */
echo "Type <b>menu</b> to continue.";
exit;
