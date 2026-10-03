<?php
require('nav.php');

// One query to get all the prodcuts
$sql = "SELECT category.name AS cat_name, products.* FROM products JOIN category  
        ON products.category_id = category.category_id WHERE products.active = 1 ";

$res = mysqli_execute_query($conn, $sql);

$productsList = [];
while ($row = mysqli_fetch_assoc($res)) {
    $productsList[$row['cat_name']][] = [
        'id' => $row['Product_SKU'],
        'name' => $row['name'],
        'price' => $row['price'],
        'inStock' => $row['stock'],
        'image' => $row['image']
    ];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Car Parts Store</title>
    <link rel="stylesheet" href="productStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <div class="page-container">
        <h1>Car Parts Store</h1>

        <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
            <div class="cart-success-msg">
                <i class="fas fa-check-circle"></i>
                Item added to your cart successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['added']) && $_GET['added'] == 0): ?>
            <div class="cart-success-msg" style="color:var(--danger);">
                Not enough stock for this quantity.
            </div>
        <?php endif; ?>

        <?php foreach ($productsList as $categoryName => $items): ?>
            <div class="category">
                <h2><?php echo htmlspecialchars($categoryName); ?></h2>

                <div class="products">
                    <?php foreach ($items as $product): ?>
                        <div class="product">
                            <?php if ($product['image']): ?>
                                <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php endif; ?>

                            <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                            <div class="product-price">₪<?php echo number_format($product['price'], 2); ?></div>

                            <?php
                            if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $role == 0): ?>
                                <?php if ($product['inStock'] > 0): ?>
                                    <form method="post" action="cart.php">
                                        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['inStock']; ?>">
                                        <button type="submit">Add to Cart</button>
                                    </form>
                                <?php else: ?>
                                    <div class="product-price" style="color:var(--danger);">SOLD OUT</div>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if ($product['inStock'] > 0): ?>
                                    <div class="product-price" style="font-size: 12px; opacity:0.6;">
                                        In Stock: <?php echo $product['inStock']; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="product-price" style="color: var(--danger); font-size: 12px; font-weight: 800;">
                                        OUT OF STOCK
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>