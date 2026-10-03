<?php
require('nav.php');

/* USER MUST BE LOGGED IN */
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 0) {
    header("Location: revZone.php");
    exit;
}

$username = $_SESSION['username'];

/* ADD TO CART */
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $product_sku = $_POST['product_id'];
    $quantity   = intval($_POST['quantity']);

    // product must exist, be active, and have enough stock (including what is already in the cart)
    $stockRes = mysqli_execute_query($conn, "SELECT stock FROM products WHERE Product_SKU = ? AND active = 1", [$product_sku]);
    $stockRow = mysqli_fetch_assoc($stockRes);

    $inCartRes = mysqli_execute_query($conn, "SELECT amount FROM cart WHERE username = ? AND product_SKU = ?", [$username, $product_sku]);
    $inCartRow = mysqli_fetch_assoc($inCartRes);
    $inCart = $inCartRow ? (int)$inCartRow['amount'] : 0;

    if (!$stockRow || $quantity < 1 || $inCart + $quantity > (int)$stockRow['stock']) {
        header("Location: products.php?added=0");
        exit;
    }

    if ($inCartRow) {
        mysqli_execute_query($conn, "UPDATE cart SET amount = amount + ? WHERE username = ? AND product_SKU = ?", [$quantity, $username, $product_sku]);
    } else {
        mysqli_execute_query($conn, "INSERT INTO cart (username, product_SKU, amount) VALUES (?, ?, ?)", [$username, $product_sku, $quantity]);
    }
    // it sends the user back to products.php with a "success" flag
    header("Location: products.php?added=1");
    exit;
}

/* UPDATE QUANTITY (WITH STOCK CHECK) */
if (isset($_POST['update_qty']) && isset($_POST['product_id']) && isset($_POST['new_qty'])) {
    $product_sku = $_POST['product_id'];
    $qty = intval($_POST['new_qty']);

    // Get current stock for this specific product
    $stockRes = mysqli_execute_query($conn, "SELECT stock FROM products WHERE Product_SKU = ?", [$product_sku]);
    $stockRow = mysqli_fetch_assoc($stockRes);
    $maxStock = $stockRow['stock'];

    // Only update if qty is more than 0 AND not exceeding stock
    if ($qty > 0 && $qty <= $maxStock) {
        mysqli_execute_query($conn, "UPDATE cart SET amount = ? WHERE username = ? AND product_SKU = ?", [$qty, $username, $product_sku]);
    }
}

/* REMOVE ITEM */
if (isset($_POST['remove_item']) && isset($_POST['product_id'])) {
    $product_sku = $_POST['product_id'];
    mysqli_execute_query($conn, "DELETE FROM cart WHERE username = ? AND product_SKU = ?", [$username, $product_sku]);
}

/* FETCH CART */
$cartQuery = mysqli_execute_query($conn, "SELECT cart.product_SKU, cart.amount, products.name, products.price,
        products.image, products.stock FROM cart JOIN products ON cart.product_SKU = products.Product_SKU
        WHERE cart.username = ?", [$username]);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Cart</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cartStyle.css">

    <script>
        /* Auto update quantity without needing "Update" button */
        function autoUpdate(form) {
            form.submit();
        }
    </script>
</head>

<body>
    <?php require('header.php'); ?>
    <div class="page-container">
        <h1>My Cart</h1>

        <?php if (mysqli_num_rows($cartQuery) == 0): ?>
            <div style="text-align:center; padding: 60px 20px;">
                <p style="color:var(--muted); font-size: 18px; margin-bottom: 30px;">Your cart is empty.</p>
                <a href="products.php" class="Proceed-btn" style="display:inline-block;">Start Shopping</a>
            </div>
            <?php
        else:
            $total = 0;
            while ($row = mysqli_fetch_assoc($cartQuery)):
                $itemTotal = $row['price'] * $row['amount'];
                $total += $itemTotal;
            ?>

                <div class="cart-card">
                    <img class="cart-img" src="<?php echo htmlspecialchars($row['image']); ?>">
                    <div class="cart-info">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p>Price: ₪<?php echo number_format($row['price'], 2); ?></p>
                    </div>

                    <div class="cart-actions">
                        <form method="post">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_SKU']); ?>">
                            <input type="hidden" name="update_qty" value="1">
                            <input type="number" name="new_qty" value="<?php echo $row['amount']; ?>" min="1" max="<?php echo $row['stock']; ?>" onchange="autoUpdate(this.form)">
                        </form>

                        <div class="item-total">₪<?php echo number_format($itemTotal, 2); ?></div>

                        <div class="remove-container">
                            <form method="post">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($row['product_SKU']); ?>">
                                <button name="remove_item" class="remove-x">×</button>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endwhile; ?>

            <div class="cart-total">
                Total: ₪<?php echo number_format($total, 2); ?>
            </div>

            <div class="cart-proceed">
                <a href="checkout.php" class="Proceed-btn">Proceed to Payment</a>
            </div>

        <?php endif; ?>
    </div>
</body>

</html>