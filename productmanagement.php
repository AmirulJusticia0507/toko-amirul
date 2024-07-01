<?php
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

include 'konekke_local.php';

// Check user role
$user_id = $_SESSION['userid'];
$query = "SELECT status FROM users WHERE userid = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_status);
$stmt->fetch();
$stmt->close();

// Jika status user adalah Customer, redirect atau tampilkan pesan bahwa mereka tidak memiliki akses
if ($user_status == 'Customer') {
    // Misalnya, alihkan ke halaman lain atau tampilkan pesan error
    header('Location: no-access.php');
    exit;
}

// Initialize variables
$product_id = '';
$product_name = '';
$description = '';
$price = 0.0;
$stock_quantity = 0;
$category_id = '';
$brand_id = '';
$status = '';
$weight = 0.0;
$image_url = '';

// Initialize action variable based on product_id presence
$action = isset($_POST['product_id']) ? 'edit' : 'add';

// Process for saving or updating products
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = cleanInput($_POST['product_name']);
    $description = cleanInput($_POST['description']);
    $price = cleanInput($_POST['price']);
    $stock_quantity = cleanInput($_POST['stock_quantity']);
    $category_id = cleanInput($_POST['category_id']);
    $brand_id = cleanInput($_POST['brand_id']);
    $status = cleanInput($_POST['status']);
    $weight = cleanInput($_POST['weight']);
    $product_id = cleanInput($_POST['product_id']); // Added for edit mode

    // Handle file upload
    if ($_FILES['product_image']['size'] > 0) {
        $target_dir = "uploads/product/";
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $extensions_arr = array("jpg", "jpeg", "png", "gif");

        if (in_array($imageFileType, $extensions_arr)) {
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file);
            $image_url = basename($_FILES["product_image"]["name"]);
        } else {
            $error = "Invalid file format. Allowed formats: JPG, JPEG, PNG, GIF.";
        }
    }

    // Update product if product_id is provided (edit mode)
    if (!empty($product_id)) {
        $query = "UPDATE products SET product_name=?, description=?, price=?, stock_quantity=?, category_id=?, brand_id=?, product_image=?, status=?, weight=? WHERE product_id=?";
        $stmt = $koneklocalhost->prepare($query);
        $stmt->bind_param("sssiiisssi", $product_name, $description, $price, $stock_quantity, $category_id, $brand_id, $image_url, $status, $weight, $product_id);
    } else {
        // Generate unique product_id for new product
        $product_id = 'P' . date('YmdHis');

        // Insert new product
        $query = "INSERT INTO products (product_id, product_name, description, price, stock_quantity, category_id, brand_id, product_image, status, weight) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneklocalhost->prepare($query);
        $stmt->bind_param("sssiiissss", $product_id, $product_name, $description, $price, $stock_quantity, $category_id, $brand_id, $image_url, $status, $weight);
    }

    if ($stmt->execute()) {
        // Redirect or handle success as needed
        header('Location: productmanagement.php');
        exit;
    } else {
        $error = "Error saving product: " . $stmt->error;
    }
}

// Function to clean input data
function cleanInput($input) {
    $search = array(
        '@<script[^>]*?>.*?</script>@si', // Remove JavaScript
        '@<[\/\!]*?[^ <>]*?>@si', // Remove HTML tags
        '@<style[^>]*?>.*?</style>@siU', // Remove style tags
        '@<![ \t\n\r]*--[ \t\n\r]*>@' // Remove comments
    );
    $output = preg_replace($search, '', $input);
    return $output;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Product Management - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahkan link AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Tambahkan link DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <!-- Tambahkan Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Sertakan CSS Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
    <!-- Tambahkan CSS kustom -->
    <link rel="icon" href="img/amirulshop.png" type="image/png">
    <style>
        /* Gaya kustom */
        .content-wrapper {
            min-height: 100vh;
        }
        .form-control-feedback {
            margin-right: 20px;
        }
        .img-thumbnail {
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .badge {
            padding: 0.5em 1em;
            font-size: 0.9em;
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
            <!-- Sisipkan header -->
            <?php include 'header.php'; ?>
        </nav>
        
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Konten Utama -->
        <div class="content-wrapper">
            <main class="content">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Product Management</li>
                    </ol>
                </nav>
                <?php include 'navigation.php'; ?>

                    <!-- Form tambah/edit produk -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title" id="productFormTitle">Add New Product</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="hideProductForm()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form id="productForm" action="" method="post" enctype="multipart/form-data">
                                <input type="hidden" id="product_id" name="product_id" value="<?php echo isset($product_id) ? $product_id : ''; ?>">
                                <div class="form-group">
                                    <label for="product_name">Product Name</label>
                                    <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo isset($product_name) ? $product_name : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control" id="description" name="description" required><?php echo isset($description) ? $description : ''; ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="number" class="form-control" id="price" name="price" step="0.01" value="<?php echo isset($price) ? $price : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="stock_quantity">Stock Quantity</label>
                                    <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo isset($stock_quantity) ? $stock_quantity : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="category_id">Category</label>
                                    <select class="form-control" id="category_id" name="category_id" required>
                                        <option value="">Select Category</option>
                                        <?php
                                        $query = "SELECT * FROM categories";
                                        $result = $koneklocalhost->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            $selected = isset($category_id) && $category_id == $row['category_id'] ? 'selected' : '';
                                            echo "<option value='{$row['category_id']}' $selected>{$row['category_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="brand_id">Brand</label>
                                    <select class="form-control" id="brand_id" name="brand_id" required>
                                        <option value="">Select Brand</option>
                                        <?php
                                        $query = "SELECT * FROM brands";
                                        $result = $koneklocalhost->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            $selected = isset($brand_id) && $brand_id == $row['brand_id'] ? 'selected' : '';
                                            echo "<option value='{$row['brand_id']}' $selected>{$row['brand_name']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="available" <?php echo isset($status) && $status == 'available' ? 'selected' : ''; ?>>Available</option>
                                        <option value="out of stock" <?php echo isset($status) && $status == 'out of stock' ? 'selected' : ''; ?>>Out of Stock</option>
                                        <option value="discontinued" <?php echo isset($status) && $status == 'discontinued' ? 'selected' : ''; ?>>Discontinued</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="weight">Weight (kg)</label>
                                    <input type="number" class="form-control" id="weight" name="weight" step="0.01" value="<?php echo isset($weight) ? $weight : ''; ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="product_image">Product Image</label>
                                    <input type="file" class="form-control-file" id="product_image" name="product_image" onchange="previewImage(this)">
                                    <small class="form-text text-muted">Accepted formats: JPG, JPEG, PNG</small>
                                    <div id="imagePreview" class="mt-2">
                                        <?php if (!empty($product_image)): ?>
                                            <img src="uploads/product/<?php echo $product_image; ?>" alt="Product Image Preview" style="max-width: 200px; max-height: 200px;">
                                        <?php else: ?>
                                            <p>No image uploaded</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if ($action == 'edit'): ?>
                                    <button type="submit" name="update" class="btn btn-primary">Update Product</button>
                                <?php else: ?>
                                    <button type="submit" name="simpan" class="btn btn-primary">Simpan Product</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <!-- Tampilkan produk dalam tabel -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title">Products List</h3>
                        </div>
                        <div class="card-body">
                                <table id="productTable" class="display table table-bordered table-striped table-hover responsive nowrap" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Product Name</th>
                                            <th>Photo Product</th>
                                            <th>Description</th>
                                            <th>Price</th>
                                            <th nowrap>Stock Quantity</th>
                                            <th>Category</th>
                                            <th>Brand</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                <?php
                                    $query = "SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, c.category_name, b.brand_name, p.status, p.product_image FROM products p LEFT JOIN categories c ON p.category_id = c.category_id LEFT JOIN brands b ON p.brand_id = b.brand_id ORDER BY p.product_id DESC";
                                    $result = $koneklocalhost->query($query);
                                    if ($result->num_rows > 0) {
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<tr>";
                                            echo "<td>{$row['product_id']}</td>";
                                            echo "<td>{$row['product_name']}</td>";
                                            echo "<td style='text-align: center; vertical-align: middle;'>";
                                            if (!empty($row['product_image'])) {
                                                $photoPath = "uploads/product/{$row['product_image']}";
                                                echo "<a href='{$photoPath}' data-fancybox='gallery'>";
                                                echo "<img src='{$photoPath}' alt='Product Image' style='max-width: 100px; max-height: 100px;' class='img-thumbnail'>";
                                                echo "</a>";
                                            } else {
                                                echo "No photo available";
                                            }
                                            echo "</td>";
                                            echo "<td>{$row['description']}</td>";
                                            echo "<td>" . number_format($row['price'], 0, ',', '.') . "</td>";
                                            echo "<td style='text-align: center; vertical-align: middle;'>{$row['stock_quantity']}</td>";
                                            echo "<td>{$row['category_name']}</td>";
                                            echo "<td>{$row['brand_name']}</td>";
                                            // echo "<td>";
                                            // if ($row['status'] == 'Available') {
                                            //     echo "<span class='badge badge-success'>Available</span>";
                                            // } elseif ($row['status'] == 'Out of Stock') {
                                            //     echo "<span class='badge badge-warning'>Out of Stock</span>";
                                            // } elseif ($row['status'] == 'Discontinued') {
                                            //     echo "<span class='badge badge-danger'>Discontinued</span>";
                                            // }
                                            // echo "</td>";
                                            echo "<td>{$row['status']}</td>";
                                            echo "<td>";
                                            echo "<button type='button' class='btn btn-sm btn-primary' onclick='editProduct(\"{$row['product_id']}\")' title='Edit'><i class='fas fa-edit'></i></button> ";
                                            echo "<button type='button' class='btn btn-sm btn-danger' onclick='confirmDelete(\"{$row['product_id']}\")' title='Delete'><i class='fas fa-trash'></i></button>&nbsp;";
                                            echo "<a href='showdetailsproduct.php?product_id={$row['product_id']}' class='btn btn-sm btn-info' title='Details' target='_blank'><i class='fas fa-eye'></i></a>";
                                            echo "</td>";
                                            
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='9' class='text-center'>No products found</td></tr>";
                                    }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </main>
            </div>
            <!-- Footer -->
    <?php include 'footer.php'; ?>
</div>

<!-- Tambahkan script JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.min.js"></script>
<!-- Sertakan jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Tambahkan AdminLTE JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
<!-- Sertakan DataTables JS -->
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
<!-- Sertakan Select2 JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTable
            $('#productTable').DataTable({
                responsive: true,
                scrollX: true,
                searching: true,
                lengthMenu: [10, 25, 50, 100, 500, 1000],
                pageLength: 10,
                dom: 'lBfrtip'
            });
            // Inisialisasi Select2 untuk pilihan kategori dan merek
            $('#category_id').select2();
            $('#brand_id').select2();
        });

        function editProduct(product_id) {
            // AJAX request to fetch product details
            $.ajax({
                url: 'fetch_product.php',
                type: 'post',
                data: { product_id: product_id },
                dataType: 'json',
                success: function(data) {
                    // Fill form fields with fetched data
                    $('#productFormTitle').text('Edit Product');
                    $('#product_id').val(data.product_id);
                    $('#product_name').val(data.product_name);
                    $('#description').val(data.description);
                    $('#price').val(data.price);
                    $('#stock_quantity').val(data.stock_quantity);
                    $('#category_id').val(data.category_id).trigger('change'); // Trigger change event for select2
                    $('#brand_id').val(data.brand_id).trigger('change'); // Trigger change event for select2
                    $('#status').val(data.status);
                    $('#weight').val(data.weight);
                    // Preview product image
                    if (data.product_image !== '') {
                        $('#imagePreview').html('<img src="uploads/product/' + data.product_image + '" alt="Product Image Preview" style="max-width: 200px; max-height: 200px;">');
                    } else {
                        $('#imagePreview').html('<p>No image uploaded</p>');
                    }
                    // Show the form
                    $('#productForm').slideDown();
                    // Scroll to the form
                    $('html, body').animate({
                        scrollTop: $("#productForm").offset().top
                    }, 1000);
                },
                error: function(xhr, status, error) {
                    Swal.fire(
                        'Error!',
                        'Failed to fetch product details.',
                        'error'
                    );
                }
            });
        }

        function confirmDelete(product_id) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to delete this product.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'delete_product.php',
                        type: 'post',
                        data: { product_id: product_id },
                        success: function(response) {
                            Swal.fire(
                                'Deleted!',
                                'Your product has been deleted.',
                                'success'
                            ).then(() => {
                                window.location.reload(); // Reload page after deletion
                            });
                        },
                        error: function(xhr, status, error) {
                            Swal.fire(
                                'Error!',
                                'Failed to delete product.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // Function to hide product form
        function hideProductForm() {
            $('#productForm').slideUp();
            $('#productFormTitle').text('Add New Product');
            $('#product_id').val('');
            $('#product_name').val('');
            $('#description').val('');
            $('#price').val('');
            $('#stock_quantity').val('');
            $('#category_id').val('');
            $('#brand_id').val('');
            $('#status').val('');
            $('#weight').val('');
            $('#imagePreview').html('<p>No image uploaded</p>');
        }

        // Function to preview selected image
        function previewImage(input) {
            var file = input.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                var imagePreview = document.getElementById('imagePreview');
                imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Product Image Preview" style="max-width: 200px; max-height: 200px;">';
            };

            reader.readAsDataURL(file);
        }
    </script>
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