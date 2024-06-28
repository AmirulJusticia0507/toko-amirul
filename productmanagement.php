<?php
// Periksa apakah pengguna telah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    // Jika tidak ada sesi pengguna, alihkan ke halaman login
    header('Location: login.php');
    exit;
}
// Ambil nama lengkap pengguna dari sesi
$namalengkap = isset($_SESSION['namalengkap']) ? $_SESSION['namalengkap'] : '';
include 'konekke_local.php';

// Proses untuk menyimpan atau mengupdate produk
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';
    $product_name = cleanInput($_POST['product_name']);
    $description = cleanInput($_POST['description']);
    $price = cleanInput($_POST['price']);
    $stock_quantity = cleanInput($_POST['stock_quantity']);
    $category_id = cleanInput($_POST['category_id']);
    $brand_id = cleanInput($_POST['brand_id']);
    $status = cleanInput($_POST['status']);
    $weight = cleanInput($_POST['weight']);
    $dimensions = cleanInput($_POST['dimensions']);
    $sku = cleanInput($_POST['sku']);

    // Handle file upload
    $image_path = ''; // Initialize empty image path
    if ($_FILES['product_image']['size'] > 0) {
        $target_dir = "uploads/product/"; // Directory where images will be uploaded
        $target_file = $target_dir . basename($_FILES["product_image"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Valid file extensions
        $extensions_arr = array("jpg", "jpeg", "png", "gif");

        // Check extension
        if (in_array($imageFileType, $extensions_arr)) {
            // Upload file
            move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file);
            $image_path = $target_file;
        } else {
            $error = "Invalid file format. Allowed formats: JPG, JPEG, PNG, GIF.";
        }
    }

    if (!empty($product_id)) {
        // Update produk jika product_id ada
        $query = "UPDATE products SET product_name=?, description=?, price=?, stock_quantity=?, category_id=?, brand_id=?, image_url=?, status=?, weight=?, dimensions=?, sku=? WHERE product_id=?";
        $stmt = $koneklocalhost->prepare($query);
        $stmt->bind_param("ssdiiisssds", $product_name, $description, $price, $stock_quantity, $category_id, $brand_id, $image_path, $status, $weight, $dimensions, $sku, $product_id);
    } else {
        // Tambahkan produk baru jika product_id kosong
        $query = "INSERT INTO products (product_name, description, price, stock_quantity, category_id, brand_id, image_url, status, weight, dimensions, sku) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $koneklocalhost->prepare($query);
        $stmt->bind_param("ssdiiisssds", $product_name, $description, $price, $stock_quantity, $category_id, $brand_id, $image_path, $status, $weight, $dimensions, $sku);
    }

    if ($stmt->execute()) {
        // Redirect or handle success as needed
        header('Location: productmanagement.php');
        exit;
    } else {
        $error = "Error saving product";
    }
}

function cleanInput($input) {
    $search = array(
        '@<script[^>]*?>.*?</script>@si',   // Hapus script
        '@<[\/\!]*?[^<>]*?>@si',            // Hapus tag HTML
        '@<style[^>]*?>.*?</style>@siU',    // Hapus style tag
        '@<![\s\S]*?--[ \t\n\r]*>@'         // Hapus komentar
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
                <?php
                include 'navigation.php';
                ?>

                <!-- Tombol tambah produk -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <a href="#" class="btn btn-info" onclick="showProductForm(null)"><i class="fas fa-plus"></i> Add New Product</a>
                    </div>
                </div>

                <!-- Tabel daftar produk -->
                <div class="row">
                    <div class="col-md-12">
                        <table class="display table table-bordered table-striped table-hover responsive nowrap" style="width:100%" id="productsTable">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Product Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Stock Quantity</th>
                                    <th>Category</th>
                                    <th>Brand</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT p.*, c.category_name, b.brand_name FROM products p 
                                          JOIN categories c ON p.category_id = c.category_id 
                                          JOIN brands b ON p.brand_id = b.brand_id";
                                $result = $koneklocalhost->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>{$row['product_id']}</td>";
                                    echo "<td>{$row['product_name']}</td>";
                                    echo "<td>{$row['description']}</td>";
                                    echo "<td>{$row['price']}</td>";
                                    echo "<td>{$row['stock_quantity']}</td>";
                                    echo "<td>{$row['category_name']}</td>";
                                    echo "<td>{$row['brand_name']}</td>";
                                    echo "<td>
                                            <a href='#' class='btn btn-info btn-sm' onclick='showProductForm({$row['product_id']})'>Edit</a>
                                            <a href='#' class='btn btn-danger btn-sm' onclick='deleteProduct({$row['product_id']})'>Delete</a>
                                            <a href='showdetailsproduct?product_id={$row['product_id']}' class='btn btn-primary btn-sm'>Show Details</a>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Form tambah/edit produk -->
                <div id="productFormContainer" style="display: none;">
                    <form id="productForm" class="card">
                        <div class="card-header">
                            <h3 class="card-title" id="productFormTitle">Add New Product</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" onclick="hideProductForm()">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <input type="hidden" id="product_id" name="product_id">
                            <div class="form-group">
                                <label for="product_name">Product Name</label>
                                <input type="text" class="form-control" id="product_name" name="product_name" required>
                            </div>
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea class="form-control" id="description" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="price">Price</label>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label for="stock_quantity">Stock Quantity</label>
                                <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" required>
                            </div>
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select class="form-control" id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php
                                    $query = "SELECT * FROM categories";
                                    $result = $koneklocalhost->query($query);
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
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
                                        echo "<option value='{$row['brand_id']}'>{$row['brand_name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="image_url">Image URL</label>
                                <input type="url" class="form-control" id="image_url" name="image_url">
                            </div>
                            <div class="form-group">
                                <label for="product_image">Product Image</label>
                                <input type="file" class="form-control-file" id="product_image" name="product_image" onchange="previewImage(event)">
                                <!-- Tambahkan elemen img untuk preview -->
                                <img id="preview" src="#" alt="Preview" style="max-width: 200px; margin-top: 10px; display: none;">
                            </div>
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select class="form-control" id="status" name="status" required>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="weight">Weight (kg)</label>
                                <input type="number" class="form-control" id="weight" name="weight" step="0.01">
                            </div>
                            <div class="form-group">
                                <label for="dimensions">Dimensions (LxWxH in cm)</label>
                                <input type="text" class="form-control" id="dimensions" name="dimensions">
                            </div>
                            <div class="form-group">
                                <label for="sku">SKU</label>
                                <input type="text" class="form-control" id="sku" name="sku" >
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-success"><i class="fas fa-plus"></i> Save Product</button>
                            <button type="button" class="btn btn-danger float-right" onclick="resetForm()"><i class="fas fa-power-off"></i> Reset</button>
                        </div>
                    </form>
                </div>
            </main>
        </div>

        <!-- Footer -->
        <?php include 'footer.php'; ?>
    </div>

    <!-- Sertakan JavaScript -->
    <!-- Tambahkan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <!-- Tambahkan Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Sertakan AdminLTE JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <!-- Sertakan DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <!-- Sertakan Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <!-- Sertakan skrip kustom -->
    <script>
        $(document).ready(function() {
            // Tambahkan event click pada tombol pushmenu
            $('.nav-link[data-widget="pushmenu"]').on('click', function() {
                // Toggle class 'sidebar-collapse' pada elemen body
                $('body').toggleClass('sidebar-collapse');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#productsTable').DataTable();
            // Inisialisasi Select2
            $('#category_id').select2();
            $('#brand_id').select2();
        });

        // Fungsi untuk menampilkan form tambah/edit produk
        function showProductForm(product_id) {
            // Set judul form
            if (product_id) {
                $('#productFormTitle').text('Edit Product');
                $('#product_id').val(product_id);
                // Ambil data produk untuk diedit
                $.ajax({
                    url: 'get_product.php',
                    type: 'POST',
                    data: { product_id: product_id },
                    dataType: 'json',
                    success: function(response) {
                        $('#product_name').val(response.product_name);
                        $('#description').val(response.description);
                        $('#price').val(response.price);
                        $('#stock_quantity').val(response.stock_quantity);
                        $('#category_id').val(response.category_id).trigger('change');
                        $('#brand_id').val(response.brand_id).trigger('change');
                        $('#image_url').val(response.image_url);
                        $('#status').val(response.status);
                        $('#weight').val(response.weight);
                        $('#dimensions').val(response.dimensions);
                        $('#sku').val(response.sku);
                        // Tampilkan form
                        $('#productFormContainer').slideDown();
                    },
                    error: function(xhr, status, error) {
                        alert('Error fetching product data');
                    }
                });
            } else {
                $('#productFormTitle').text('Add New Product');
                // Bersihkan nilai form
                $('#productForm')[0].reset();
                // Tampilkan form
                $('#productFormContainer').slideDown();
            }
        }

        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var preview = document.getElementById('preview');
                preview.src = reader.result;
                preview.style.display = 'block'; // Tampilkan gambar yang dipilih
            }
            reader.readAsDataURL(event.target.files[0]);
        }

        // Fungsi untuk menyembunyikan form tambah/edit produk
        function hideProductForm() {
            $('#productFormContainer').slideUp();
        }

        // Fungsi untuk mereset form tambah/edit produk
        function resetForm() {
            $('#productForm')[0].reset();
        }

        // Fungsi untuk menghapus produk
        function deleteProduct(product_id) {
            if (confirm('Are you sure you want to delete this product?')) {
                $.ajax({
                    url: 'delete_product.php',
                    type: 'POST',
                    data: { product_id: product_id },
                    success: function(response) {
                        // Reload halaman setelah penghapusan
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        alert('Error deleting product');
                    }
                });
            }
        }
    </script>
            <script>
                $(document).ready(function () {
                    $('#productsTable').DataTable({
                        responsive: true,
                        scrollX: true,
                        searching: true,
                        lengthMenu: [10, 25, 50, 100, 500, 1000],
                        pageLength: 10,
                        dom: 'lBfrtip'
                    });
                });
            </script>
</body>
</html>
