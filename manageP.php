<?php
require('nav.php');

// AUTH CHECK (MANAGER ONLY) 
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $role != 1) {
    header("Location: logIn.php");
    exit;
}
$username = $_SESSION['username'];

// ADD CATEGORY 
if (isset($_POST['add_category'])) {
    $catName = strtolower(trim($_POST['category_name']));

    if ($catName == '') {
        echo "<script>alert('Category name cannot be empty'); window.location.href='manageP.php';</script>";
        exit;
    }

    $result = mysqli_execute_query($conn, "SELECT `name` FROM category WHERE `name` = ?", [$catName]);
    $row = mysqli_fetch_array($result);

    if ($row) {
        echo "<script>alert('Cant add already existing category'); window.location.href='manageP.php';</script>";
        exit;
    }

    mysqli_execute_query($conn, "INSERT INTO category (name) VALUES (?)", [$catName]);
    echo "<script>alert('Category added successfully'); window.location.href='manageP.php';</script>";
    exit;
}

// DELETE CATEGORY 
if (isset($_POST['delete_category'])) {
    $catId = intval($_POST['category_id']);

    // check if exist products from same category
    $check = mysqli_execute_query($conn, "SELECT 1 FROM products WHERE category_id = ? LIMIT 1", [$catId]);

    if (mysqli_num_rows($check) > 0) { // if exist product cant delete it
        echo "<script>alert('Cannot delete category: products (including removed ones) still use it'); window.location.href='manageP.php';</script>";
        exit;
    }

    // delete if no used category
    mysqli_execute_query($conn, "DELETE FROM category WHERE category_id = ?", [$catId]);
    echo "<script>alert('Category deleted successfully'); window.location.href='manageP.php';</script>";
    exit;
}

// ADD PRODUCT 
if (isset($_POST['add_product'])) {
    $sku   = isset($_POST['sku']) ? strtolower(trim($_POST['sku'])) : '';
    $name  = isset($_POST['name']) ? strtolower(trim($_POST['name'])) : '';
    $cat   = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $stock = isset($_POST['stock']) ? intval($_POST['stock']) : 0;

    if ($sku == '' || $name == '') {
        echo "<script>alert('SKU and Name are required'); window.location.href='manageP.php';</script>";
        exit;
    }

    if ($cat <= 0) {
        echo "<script>alert('Category is required'); window.location.href='manageP.php';</script>";
        exit;
    }

    if ($price < 0 || $stock < 0) {
        echo "<script>alert('Price and stock must be positive'); window.location.href='manageP.php';</script>";
        exit;
    }

    // sku exist
    $checkSKU = mysqli_execute_query($conn, "SELECT Product_SKU FROM products WHERE Product_SKU = ?", [$sku]);
    $row = mysqli_fetch_array($checkSKU);
    if ($row) {
        echo "<script>alert('SKU already exists'); window.location.href='manageP.php';</script>";
        exit;
    }

    // img
    $imgPath = '';
    if (!empty($_FILES['image']['name'])) {
        // only real images (checks the file content, not just the name), max 2MB
        $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
        $info = getimagesize($_FILES['image']['tmp_name']);

        if ($info === false || !isset($allowed[$info[2]]) || $_FILES['image']['size'] > 2 * 1024 * 1024) {
            echo "<script>alert('Image must be a JPG, PNG, WEBP or GIF file up to 2MB'); window.location.href='manageP.php';</script>";
            exit;
        }

        // our own random name: can't overwrite other images and can't end with .php
        $imgPath = "img/product_" . bin2hex(random_bytes(8)) . "." . $allowed[$info[2]];
        move_uploaded_file($_FILES['image']['tmp_name'], $imgPath);
    } else {
        echo "<script>alert('Image is required'); window.location.href='manageP.php';</script>";
        exit;
    }

    mysqli_execute_query($conn, "INSERT INTO products (Product_SKU, `name`, category_id, price, stock, `image`)
        VALUES (?, ?, ?, ?, ?, ?)", [$sku, $name, $cat, $price, $stock, $imgPath]);

    echo "<script>alert('Product added successfully'); window.location.href='manageP.php';</script>";
    exit;
}

// UPDATE STOCK 
if (isset($_POST['update_stock'])) {
    $sku = strtolower(trim($_POST['sku']));
    $stock = intval($_POST['new_stock']);
    mysqli_execute_query($conn, "UPDATE products SET stock = ? WHERE Product_SKU = ?", [$stock, $sku]);
    header("Location: manageP.php");
    exit;
}

// UPDATE PRICE 
if (isset($_POST['update_price'])) {
    $sku   = strtolower(trim($_POST['sku']));
    $price = isset($_POST['new_price']) ? floatval($_POST['new_price']) : -1;

    if ($price < 0) {
        echo "<script>alert('Price must be positive'); window.location.href='manageP.php';</script>";
        exit;
    }
    // update
    mysqli_execute_query($conn, "UPDATE products SET price = ? WHERE Product_SKU = ?", [$price, $sku]);
    echo "<script>alert('Price updated successfully'); window.location.href='manageP.php';</script>";
    exit;
}

// DELETE PRODUCT (SOFT / HARD) 
if (isset($_POST['delete_product'])) {
    $sku = strtolower(trim($_POST['sku']));

    $check = mysqli_execute_query($conn, "SELECT 1 FROM orderh oh LEFT JOIN delivery d ON d.order_id = oh.order_id WHERE oh.product_SKU = ? LIMIT 1", [$sku]);

    if (!$check || mysqli_num_rows($check) == 0) {
        mysqli_execute_query($conn, "DELETE FROM cart WHERE product_SKU = ?", [$sku]);
        mysqli_execute_query($conn, "DELETE FROM products WHERE Product_SKU = ?", [$sku]);

        echo "<script>alert('Product deleted permanently (never ordered)'); window.location.href='manageP.php';</script>";
        exit;
    }

    mysqli_execute_query($conn, "UPDATE products SET active = 0 WHERE Product_SKU = ?", [$sku]);

    echo "<script>alert('Product removed from store (can be restored)'); window.location.href='manageP.php';</script>";
    exit;
}

// RESTORE PRODUCT 
if (isset($_POST['restore_product'])) {
    $sku = trim($_POST['sku']);

    mysqli_execute_query($conn, "UPDATE products SET active = 1 WHERE Product_SKU = ?", [$sku]);
    echo "<script>alert('Product restored to store'); window.location.href='manageP.php';</script>";
    exit;
}

// FILTER BY CATEGORY 
$filterCatId = isset($_POST['filter_category']) ? intval($_POST['filter_category']) : 0;

// FETCH DATA 
$categories = mysqli_execute_query($conn, "SELECT * FROM category");

if ($filterCatId > 0) {
    $products = mysqli_execute_query($conn, "SELECT products.*, category.name AS cat_name FROM products
        JOIN category ON products.category_id = category.category_id WHERE products.category_id = ?
        AND products.active = 1 ORDER BY products.name", [$filterCatId]);
} else {
    $products = mysqli_execute_query($conn, "SELECT products.*, category.name AS cat_name FROM products
        JOIN category ON products.category_id = category.category_id WHERE products.active = 1
        ORDER BY category.name, products.name");
}

// if clicked 
if (isset($_POST['toggle_removed'])) {
    $showRemoved = isset($_POST['currently_showing']) && $_POST['currently_showing'] == '1' ? false : true;
} else {
    $showRemoved = false;
}

// show only if the showremoved true
$removedProducts = [];
if ($showRemoved) {
    $removedProducts = mysqli_execute_query($conn, "SELECT products.*, category.name AS cat_name 
        FROM products JOIN category ON products.category_id = category.category_id 
        WHERE products.active = 0 ORDER BY category.name, products.name");
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Manage Products</title>
    <link rel="stylesheet" href="managePStyle.css">
</head>

<body>
    <?php require('header.php'); ?>
    <div class="page-container">
        <h1>Manage Products</h1>
        <div class="top-grid">

            <!-- ADD PRODUCT -->
            <div class="panel">
                <div class="panel-title">Add New Product</div>
                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <div class="field">
                        <label>SKU</label>
                        <input type="text" name="sku" required>
                    </div>

                    <div class="field">
                        <label>Name</label>
                        <input type="text" name="name" required>
                    </div>

                    <div class="field">
                        <label>Category</label>
                        <select name="category_id" required>
                            <?php
                            $catRes = mysqli_execute_query($conn, "SELECT * FROM category");
                            while ($c = mysqli_fetch_assoc($catRes)) {
                                echo "<option value='{$c['category_id']}'>" . htmlspecialchars($c['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="field">
                        <label>Price</label>
                        <input type="number" min="0" step="0.01" name="price" required>
                    </div>

                    <div class="field">
                        <label>Stock</label>
                        <input type="number" min="0" name="stock" required>
                    </div>

                    <div class="field">
                        <label>Image</label>
                        <input type="file" name="image" required>
                    </div>

                    <button name="add_product" class="btn span-3">Add Product</button>
                </form>
            </div>

            <!-- RIGHT SIDE -->
            <div class="stack">
                <!-- Category -->
                <div class="panel">
                    <div class="panel-title">Category</div>

                    <form method="post" class="form-inline">
                        <input type="text" name="category_name" placeholder="New category name" required>
                        <button type="submit" name="add_category" class="btn">Add</button>
                    </form>

                    <div class="divider"></div>

                    <form method="post" onsubmit="return confirm('Delete this category?');" class="form-inline danger">
                        <select name="category_id" required>
                            <?php
                            $catRes = mysqli_execute_query($conn, "SELECT * FROM category");
                            while ($c = mysqli_fetch_assoc($catRes)) {
                                echo "<option value='{$c['category_id']}'>" . htmlspecialchars($c['name']) . "</option>";
                            }
                            ?>
                        </select>
                        <button name="delete_category" class="btn danger">Delete</button>
                    </form>

                </div>

                <!-- Filter -->
                <div class="panel">
                    <div class="panel-title">Filter Products</div>

                    <form method="post" class="form-inline">
                        <select name="filter_category">
                            <option value="">All Categories</option>
                            <?php
                            $catRes = mysqli_execute_query($conn, "SELECT * FROM category");
                            while ($c = mysqli_fetch_assoc($catRes)) {
                                $sel = ($filterCatId == $c['category_id']) ? "selected" : "";
                                echo "<option value='{$c['category_id']}' $sel>" . htmlspecialchars($c['name']) . "</option>";
                            }
                            ?>
                        </select>
                        <button type="submit" class="btn">Filter</button>
                    </form>

                    <?php if ($filterCatId > 0): ?>
                        <form method="post" class="form-inline" style="margin-top:10px;">
                            <button type="submit" class="btn ghost">Clear Filter</button>
                        </form>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- PRODUCTS TABLE -->
        <div class="table-wrap">
            <table class="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Update Stock</th>
                        <th>Price</th>
                        <th>Update Price</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($p = mysqli_fetch_assoc($products)): ?>
                        <tr>
                            <td><img src="<?php echo htmlspecialchars($p['image']); ?>" class="product-img"></td>

                            <td>
                                <div class="pname"><?php echo htmlspecialchars($p['name']); ?></div>
                                <div class="psku">SKU: <?php echo htmlspecialchars($p['Product_SKU']); ?></div>
                            </td>

                            <td><span class="tag"><?php echo htmlspecialchars($p['cat_name']); ?></span></td>

                            <td class="stock-text"><?php echo $p['stock']; ?></td>

                            <td>
                                <form method="post" class="inline-mini">
                                    <input type="hidden" name="sku" value="<?php echo htmlspecialchars($p['Product_SKU']); ?>">
                                    <input type="number" name="new_stock" min="0" value="<?php echo $p['stock']; ?>">
                                    <button name="update_stock" class="btn small">Update</button>
                                </form>
                            </td>

                            <td class="price-text">₪<?php echo number_format($p['price'], 2); ?></td>

                            <td>
                                <form method="post" class="inline-mini">
                                    <input type="hidden" name="sku" value="<?php echo htmlspecialchars($p['Product_SKU']); ?>">
                                    <input type="number" step="0.01" name="new_price" value="<?php echo $p['price']; ?>">
                                    <button name="update_price" class="btn small">Update</button>
                                </form>
                            </td>

                            <td>
                                <form method="post" onsubmit="return confirm('Delete product?');">
                                    <input type="hidden" name="sku" value="<?php echo htmlspecialchars($p['Product_SKU']); ?>">
                                    <button name="delete_product" class="btn danger small delete-btn">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <form method="post" class="form-inline" style="margin-top:10px;">
            <input type="hidden" name="currently_showing" value="<?php echo $showRemoved ? '1' : '0'; ?>">
            <button type="submit" name="toggle_removed" class="btn ghost">
                <?php echo $showRemoved ? 'Hide Removed Products' : 'Show Removed Products'; ?>
            </button>
        </form>

        <?php if ($showRemoved && mysqli_num_rows($removedProducts) > 0): ?>
            <h2 style="margin-top:40px;">Removed Products</h2>
            <div class="table-wrap">
                <table class="products-table faded">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($rp = mysqli_fetch_assoc($removedProducts)): ?>
                            <tr>
                                <td><img src="<?php echo htmlspecialchars($rp['image']); ?>" class="product-img"></td>
                                <td>
                                    <div class="pname"><?php echo htmlspecialchars($rp['name']); ?></div>
                                    <div class="psku">SKU: <?php echo htmlspecialchars($rp['Product_SKU']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($rp['cat_name']); ?></td>
                                <td><?php echo $rp['stock']; ?></td>
                                <td>₪<?php echo number_format($rp['price'], 2); ?></td>
                                <td>
                                    <form method="post">
                                        <input type="hidden" name="sku" value="<?php echo htmlspecialchars($rp['Product_SKU']); ?>">
                                        <button name="restore_product" class="btn small">
                                            Restore
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>