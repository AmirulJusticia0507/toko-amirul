<?php
include 'konekke_local.php';

// Check if user is authenticated
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Retrieve product_id from URL parameter (make sure it's an integer)
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Prepare the SQL statement using a prepared statement with JOINs
    $query = "SELECT p.*, c.category_name, b.brand_name
              FROM products p
              LEFT JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN brands b ON p.brand_id = b.brand_id
              WHERE p.product_id = ?";
    $stmt = $koneklocalhost->prepare($query);

    // Bind the parameter to the statement
    $stmt->bind_param("s", $product_id); // Assuming product_id is a string

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if there's a row returned
    if ($result->num_rows > 0) {
        // Fetch product data
        $row = $result->fetch_assoc();

        // Check if keys exist before accessing
        $product_name = isset($row['product_name']) ? htmlspecialchars($row['product_name']) : 'N/A';
        $description = isset($row['description']) ? htmlspecialchars($row['description']) : 'N/A';
        $price = isset($row['price']) ? number_format($row['price'], 0, ',', '.') : 'N/A';
        $stock_quantity = isset($row['stock_quantity']) ? $row['stock_quantity'] : 'N/A';
        $category_name = isset($row['category_name']) ? htmlspecialchars($row['category_name']) : 'N/A';
        $brand_name = isset($row['brand_name']) ? htmlspecialchars($row['brand_name']) : 'N/A';
        $status = isset($row['status']) ? htmlspecialchars($row['status']) : 'N/A';
        $weight = isset($row['weight']) ? $row['weight'] . ' kg' : 'N/A';
        $product_image = isset($row['product_image']) ? $row['product_image'] : '';

        // Start HTML output
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

                                        <!-- Add to Cart and Wishlist buttons -->
                                        <div class="row mt-4">
                                            <form action="cart.php" method="post">
                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                <button type="submit" name="add_to_cart" class="btn btn-primary btn-lg btn-flat">
                                                    <i class="fas fa-cart-plus fa-lg mr-2"></i>
                                                    Add to Cart
                                                </button>
                                            </form>&emsp;

                                            <form action="wishlist.php" method="post">
                                                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                                                <button type="submit" name="add_to_wishlist" class="btn btn-default btn-lg btn-flat">
                                                    <i class="fas fa-heart fa-lg mr-2"></i>
                                                    Add to Wishlist
                                                </button>
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
        </body>
        </html>
        <?php
    } else {
        echo "<p>No product found.</p>";
    }
} else {
    echo "<p>Product ID not specified.</p>";
}
?>
