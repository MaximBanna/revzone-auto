<?php
require('nav.php');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 0) {
    header("Location: revZone.php");
    exit;
}
$username = $_SESSION['username'];

$sql = "
    SELECT 
        orders.order_id,
        orders.order_date,
        IFNULL(delivery.delivery_type, 'N/A') AS delivery_type,
        IFNULL(delivery.status, 'pending') AS status,
        SUM(orderh.price * orderh.amount) + orders.shipping AS total_cost
    FROM orders
    JOIN orderh ON orders.order_id = orderh.order_id
    LEFT JOIN delivery ON orders.order_id = delivery.order_id
    WHERE orders.user_id = ?
    GROUP BY orders.order_id
    ORDER BY orders.order_date DESC
";

$res = mysqli_execute_query($conn, $sql, [$username]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>RevZone - Order Details</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="manageUStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <div class="wrap">
        <div class="panel">
            <div class="top">
                <h1>Order Details</h1>
            </div>

            <?php if (mysqli_num_rows($res) == 0): ?>
                <div class="msg">You haven’t placed any orders yet.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total Cost</th>
                            <th>Delivery Type</th>
                            <th>Status</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($o = mysqli_fetch_assoc($res)): ?>
                            <tr>
                                <td><b style="color: #F2F2F2;">#<?php echo $o['order_id']; ?></b></td>
                                <td>
                                    <?php echo date("d M Y", strtotime($o['order_date'])); ?>
                                    <span style="opacity:0.4; font-size:10px;">
                                        <?php echo date("H:i", strtotime($o['order_date'])); ?>
                                    </span>
                                </td>
                                <td style="font-weight: 800; color: #F2F2F2;">₪<?php echo number_format($o['total_cost'], 2); ?></td>
                                <td>
                                    <span class="badge user">
                                        <?php echo $o['delivery_type']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status = strtolower($o['status']);
                                    if ($status == 'delivered') {
                                        if ($o['delivery_type'] == 'ship') {
                                            echo '<span class="badge active">Delivered</span>';
                                        } else {
                                            echo '<span class="badge active">Picked up</span>';
                                        }
                                    } elseif ($status == 'en route') {
                                        echo '<span class="badge admin">En Route</span>';
                                    } else {
                                        echo '<span class="badge blocked">Pending</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="receipt.php?id=<?php echo $o['order_id'] ?>" class="smallbtn orange"
                                        target="_blank" style="text-transform: uppercase; letter-spacing: 0.5px;">
                                        View Receipt
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>