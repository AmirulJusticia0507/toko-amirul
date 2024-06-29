<?php
include 'konekke_local.php';

session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Proses penambahan produk ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];

    // Query untuk mengambil detail produk berdasarkan product_id
    $stmtProduct = $koneklocalhost->prepare("SELECT product_name, stock_quantity FROM products WHERE product_id = ?");
    $stmtProduct->bind_param("i", $product_id);
    $stmtProduct->execute();
    $resultProduct = $stmtProduct->get_result();

    if ($resultProduct->num_rows > 0) {
        $rowProduct = $resultProduct->fetch_assoc();
        $product_name = $rowProduct['product_name'];
        $stock_quantity = $rowProduct['stock_quantity'];

        // Cek apakah stok mencukupi
        if ($stock_quantity > 0) {
            // Tambahkan produk ke keranjang
            $insertCart = $koneklocalhost->prepare("INSERT INTO cart_items (user_id, product_id, quantity, added_at) VALUES (?, ?, 1, NOW())");
            $insertCart->bind_param("ii", $_SESSION['userid'], $product_id);
            $insertCart->execute();

            // Kurangi stok produk
            $updateStock = $koneklocalhost->prepare("UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_id = ?");
            $updateStock->bind_param("i", $product_id);
            $updateStock->execute();

            echo "<script>alert('{$product_name} added to cart successfully.')</script>";
        } else {
            echo "<script>alert('{$product_name} is out of stock.')</script>";
        }
    } else {
        echo "<script>alert('Product not found.')</script>";
    }

    // Redirect user back to product details page
    echo "<script>window.location.href = 'showdetailsproduct.php?product_id={$product_id}';</script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css">
    <!-- Tambahkan link AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Tambahkan link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .user-block {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .user-block img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 50%;
            margin-right: 10px;
        }
        .user-block .username {
            font-weight: bold;
        }
        .user-block .description {
            margin-left: auto;
        }
        .post {
            margin-bottom: 20px;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
        }
        .post p {
            margin-bottom: 10px;
        }
        .post a {
            color: #333;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>
            <?php include 'header.php'; ?>
        </nav>
        
        <?php include 'sidebar.php'; ?>

        <div class="content-wrapper">
            <main class="content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Keranjang</li>
                    </ol>
                </nav>

                <?php include 'navigation.php'; ?>

                <div class="row">
                    <div class="col-12">
                        <h4>Keranjang</h4>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <div class="post clearfix">
                                    <div class="user-block">
                                        <img class="img-circle img-bordered-sm" src="<?php echo $row['product_image']; ?>" alt="Product Image">
                                        <span class="username">
                                            <a href="#"><?php echo $row['product_name']; ?></a>
                                        </span>
                                        <span class="description">Harga: Rp <?php echo number_format($row['price'], 0, ',', '.'); ?></span>
                                    </div>

                                    <form method="post" action="cart.php">
                                        <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                                        <div class="input-group">
                                            <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" class="form-control" min="1">
                                            <button type="submit" name="update" class="btn btn-sm btn-primary ms-2">Update</button>
                                            <button type="submit" name="delete" class="btn btn-sm btn-danger ms-2">Delete</button>
                                        </div>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>Keranjang Anda kosong.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tombol Checkout -->
                <div class="row mt-3">
                    <div class="col-12">
                        <a href="checkout.php" class="btn btn-success">Checkout</a>
                    </div>
                </div>

            </main>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <!-- Tambahkan script JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tambahkan event click pada tombol pushmenu
            $('.nav-link[data-widget="pushmenu"]').on('click', function() {
                // Toggle class 'sidebar-collapse' pada elemen body
                $('body').toggleClass('sidebar-collapse');
            });
        });
    </script>
</body>
</html>
