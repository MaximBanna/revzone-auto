<?php
require('revZone_DB.php');
$conn = OpenCon();
session_start();

// must be logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];
$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// who is looking (admin can see every receipt)
$userQ = mysqli_execute_query($conn, "SELECT `role` FROM users WHERE username = ?", [$username]);
$userRow = mysqli_fetch_assoc($userQ);

// the order must exist and belong to this user
$orderQ = mysqli_execute_query($conn, "SELECT * FROM orders WHERE order_id = ?", [$orderId]);
$order = mysqli_fetch_assoc($orderQ);

if (!$order || ($order['user_id'] !== $username && $userRow['role'] != 1)) {
    echo "Receipt not found.";
    exit;
}

// products of the order (prices saved at the time of purchase)
$items = mysqli_execute_query($conn, "SELECT products.name, products.image, orderh.amount, orderh.price FROM orderh
    JOIN products ON products.Product_SKU = orderh.product_SKU WHERE orderh.order_id = ?", [$orderId]);
$subtotal = 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Order Receipt</title>
    <link rel="stylesheet" href="checkoutStyle.css">
</head>

<body>

    <h1>RevZone Auto</h1>
    <h3>Order ID: <?php echo $orderId; ?></h3>

    <table>
        <tr>
            <th>Product Name</th>
            <th>Quantity</th>
            <th>Image</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($items)):
            $total = $row['amount'] * $row['price'];
            $subtotal += $total;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo $row['amount']; ?></td>
                <td><img src="<?php echo htmlspecialchars($row['image']); ?>"></td>
                <td><?php echo number_format($row['price'], 2); ?> ₪</td>
                <td><?php echo number_format($total, 2); ?> ₪</td>
            </tr>
        <?php endwhile; ?>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Shipping</td>
            <td><?php echo number_format($order['shipping'], 2); ?> ₪</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Total</td>
            <td><?php echo number_format($subtotal + $order['shipping'], 2); ?> ₪</td>
        </tr>
    </table>

</body>

</html>