<?php
include 'konekke_local.php';
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

$product_id = null;
$product_name = 'N/A';
$description = 'N/A';
$price = 'N/A';
$stock_quantity = 'N/A';
$category_name = 'N/A';
$brand_name = 'N/A';
$status = 'N/A';
$weight = 'N/A';
$product_image = '';

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['userid'];
    $quantity = 1;

    $koneklocalhost->begin_transaction();

    $check_cart_query = "SELECT * FROM cart_items WHERE product_id = ? AND user_id = ?";
    $check_stmt = $koneklocalhost->prepare($check_cart_query);
    $check_stmt->bind_param("ii", $product_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $update_cart_query = "UPDATE cart_items SET quantity = quantity + 1 WHERE product_id = ? AND user_id = ?";
        $update_cart_stmt = $koneklocalhost->prepare($update_cart_query);
        $update_cart_stmt->bind_param("ii", $product_id, $user_id);
        $update_cart_stmt->execute();
    } else {
        $insert_cart_query = "INSERT INTO cart_items (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $insert_cart_stmt = $koneklocalhost->prepare($insert_cart_query);
        $insert_cart_stmt->bind_param("iii", $user_id, $product_id, $quantity);
        $insert_cart_stmt->execute();
    }

    $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - 1 WHERE product_id = ? AND stock_quantity > 0";
    $update_stock_stmt = $koneklocalhost->prepare($update_stock_query);
    $update_stock_stmt->bind_param("s", $product_id);
    $update_stock_stmt->execute();

    if ($update_stock_stmt->affected_rows > 0) {
        $koneklocalhost->commit();
        header("Location: cart.php?message=Product added to cart successfully");
        exit;
    } else {
        $koneklocalhost->rollback();
        echo "<p>Failed to add product to cart. Not enough stock available.</p>";
    }

    $check_stmt->close();
    $insert_cart_stmt->close();
    $update_cart_stmt->close();
    $update_stock_stmt->close();
}

// Jika form Add to Wishlist diklik
if (isset($_POST['add_to_wishlist'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['userid'];

    $check_wishlist_query = "SELECT * FROM wishlist WHERE product_id = ? AND user_id = ?";
    $check_wishlist_stmt = $koneklocalhost->prepare($check_wishlist_query);
    $check_wishlist_stmt->bind_param("ii", $product_id, $user_id);
    $check_wishlist_stmt->execute();
    $check_wishlist_result = $check_wishlist_stmt->get_result();

    if ($check_wishlist_result->num_rows === 0) {
        $insert_wishlist_query = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $insert_wishlist_stmt = $koneklocalhost->prepare($insert_wishlist_query);
        $insert_wishlist_stmt->bind_param("ii", $user_id, $product_id);

        if ($insert_wishlist_stmt->execute()) {
            $_SESSION['wishlist_message'] = "Produk ini telah ditambahkan ke wishlist Anda. Silakan cek di wishlist Anda.";
            header("Location: wishlist.php");
            exit;
        } else {
            echo "Failed to add product to wishlist: " . $koneklocalhost->error;
        }

        $insert_wishlist_stmt->close();
    } else {
        echo "Product already in wishlist.";
    }

    $check_wishlist_stmt->close();
}

// Define the query to check wishlist
$check_wishlist_query = "SELECT * FROM wishlist WHERE product_id = ? AND user_id = ?";

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    $query = "SELECT p.*, c.category_name, b.brand_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              WHERE p.product_id = ?";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("s", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $product_name = htmlspecialchars($row['product_name']);
        $description = htmlspecialchars($row['description']);
        $price = number_format($row['price'], 0, ',', '.');
        $stock_quantity = $row['stock_quantity'];
        $category_name = htmlspecialchars($row['category_name']);
        $brand_name = htmlspecialchars($row['brand_name']);
        $status = htmlspecialchars($row['status']);
        $weight = $row['weight'] . ' kg';
        $product_image = $row['product_image'];

        // Check if product is in wishlist
        $check_wishlist_stmt = $koneklocalhost->prepare($check_wishlist_query);
        $check_wishlist_stmt->bind_param("ii", $product_id, $_SESSION['userid']);
        $check_wishlist_stmt->execute();
        $isInWishlist = $check_wishlist_stmt->num_rows > 0;

        $check_wishlist_stmt->close();
    } else {
        echo "<p>No product found.</p>";
        exit;
    }

    $stmt->close();
} else {
    echo "<p>Product ID not specified.</p>";
    exit;
}

$koneklocalhost->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details - Toko Amirul</title>
    <!-- Include your CSS links -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Additional CSS styles -->
    <link rel="icon" href="img/amirulshop.png" type="image/png">
    <style>
        /* Custom styles */
        .content-wrapper {
            min-height: 100vh;
        }
    </style>
    <style>
        .content-wrapper {
            min-height: 100vh;
        }
        .wishlist-btn .fa-heart {
            color: gray;
        }
        .wishlist-btn.added .fa-heart {
            color: red;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">
    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>
        <!-- Include header -->
        <?php include 'header.php'; ?>
    </nav>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="content-wrapper">
        <main class="content">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                    <li class="breadcrumb-item"><a href="productmanagement.php?page=productmanagement">Product Management</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Product Details - <?php echo $product_name; ?></li>
                </ol>
            </nav>

            <?php include 'navigation.php'; ?>

            <!-- Product details card -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title">Product Details - <?php echo $product_name; ?></h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <?php if (!empty($product_image)): ?>
                                <img src="uploads/product/<?php echo htmlspecialchars($product_image); ?>" alt="Product Image" class="img-fluid img-thumbnail">
                            <?php else: ?>
                                <p>No image available</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5><?php echo $product_name; ?></h5>
                            <p><strong>Description:</strong> <?php echo $description; ?></p>
                            <p><strong>Price:</strong> Rp <?php echo $price; ?></p>
                            <p><strong>Stock Quantity:</strong> <?php echo $stock_quantity; ?></p>
                            <p><strong>Category:</strong> <?php echo $category_name; ?></p>
                            <p><strong>Brand:</strong> <?php echo $brand_name; ?></p>
                            <p><strong>Status:</strong> <?php echo $status; ?></p>
                            <p><strong>Weight:</strong> <?php echo $weight; ?></p>
                            <a href="productmanagement.php" class="btn btn-info"><i class="fas fa-arrow-left"></i> Back to Product Management</a>

                            <!-- Add to Cart and Wishlist buttons -->
                            <div class="row mt-4">
                                <form action="showdetailsproduct.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg btn-flat">
                                        <i class="fas fa-cart-plus fa-lg mr-2"></i>
                                        Add to Cart
                                    </button>
                                </form>&emsp;

                                <!-- Modify form for Wishlist button -->
                                <form action="showdetailsproduct.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                    <?php if ($isInWishlist): ?>
                                        <button type="button" class="btn btn-danger disabled"><i class="fas fa-heart"></i> Added to Wishlist</button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-outline-secondary" name="add_to_wishlist"><i class="fas fa-heart"></i> Add to Wishlist</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Include footer -->
    <?php include 'footer.php'; ?>
</div>

<!-- Include your JavaScript links -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>

<!-- Your custom scripts -->
<script>
    $(document).ready(function() {
        // Toggle sidebar collapse
        $('.nav-link[data-widget="pushmenu"]').on('click', function() {
            $('body').toggleClass('sidebar-collapse');
        });
    });
</script>
<script>
$(document).ready(function() {
    // Handle Wishlist button click
    $('#wishlistButton').click(function() {
        var product_id = $(this).data('product-id');
        var action = $(this).hasClass('text-danger') ? 'remove' : 'add';

        $.ajax({
            type: 'POST',
            url: 'wishlist_action.php',
            data: { product_id: product_id, action: action },
            dataType: 'json',
            success: function(response) {
                $('#wishlistButton').toggleClass('text-danger');
                $('#wishlistButton').text(response.message);
                alert(response.message); // Optional: Show alert message
            },
            error: function(xhr, textStatus, errorThrown) {
                console.error('Error:', errorThrown);
            }
        });
    });
});
</script>
</body>
</html>
