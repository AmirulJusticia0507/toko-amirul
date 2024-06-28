<?php
include 'konekke_local.php';

// Periksa apakah pengguna telah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    // Jika tidak ada sesi pengguna, alihkan ke halaman login
    header('Location: login.php');
    exit;
}

// Fungsi untuk mendapatkan semua kategori dari database
function getCategories() {
    global $koneklocalhost;
    $query = "SELECT * FROM categories";
    $result = $koneklocalhost->query($query);
    return $result;
}

// Fungsi untuk menambahkan kategori baru
if (isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $description = $_POST['description'];
    $current_time = date('Y-m-d H:i:s', strtotime('now')); // Mendapatkan waktu saat ini dengan format yang diinginkan

    $query = "INSERT INTO categories (category_name, description, created_at, updated_at) VALUES (?, ?, ?, ?)";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("ssss", $category_name, $description, $current_time, $current_time);
    $stmt->execute();

    header("Location: categories.php");
    exit;
}

// Fungsi untuk mengupdate kategori
if (isset($_POST['edit_category'])) {
    $category_id = $_POST['edit_category_id'];
    $category_name = $_POST['edit_category_name'];
    $description = $_POST['edit_description'];
    $current_time = date('Y-m-d H:i:s', strtotime('now')); // Mendapatkan waktu saat ini dengan format yang diinginkan

    $query = "UPDATE categories SET category_name=?, description=?, updated_at=? WHERE category_id=?";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("sssi", $category_name, $description, $current_time, $category_id);
    $stmt->execute();

    header("Location: categories.php");
    exit;
}

// Fungsi untuk menghapus kategori
if (isset($_POST['delete_category'])) {
    $category_id = $_POST['delete_category_id'];

    $query = "DELETE FROM categories WHERE category_id=?";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();

    header("Location: categories.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Categories Product - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
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
<!-- <link rel="stylesheet" href="uploadfoto.css"> -->
    <link rel="icon" href="img/amirulshop.png" type="image/png">
    <style>
        /* Tambahkan CSS agar tombol accordion terlihat dengan baik */
        .btn-link {
            text-decoration: none;
            color: #007bff; /* Warna teks tombol */
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .card-header {
            background-color: #f7f7f7; /* Warna latar belakang header card */
        }

        #notification {
            display: none;
            margin-top: 10px; /* Adjust this value based on your layout */
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f8f8f8;
            color: #333;
        }
    </style>
    <style>
        .myButtonCekSaldo {
            box-shadow: 3px 4px 0px 0px #899599;
            background:linear-gradient(to bottom, #ededed 5%, #bab1ba 100%);
            background-color:#ededed;
            border-radius:15px;
            border:1px solid #d6bcd6;
            display:inline-block;
            cursor:pointer;
            color:#3a8a9e;
            font-family:Arial;
            font-size:17px;
            padding:7px 25px;
            text-decoration:none;
            text-shadow:0px 1px 0px #e1e2ed;
        }
        .myButtonCekSaldo:hover {
            background:linear-gradient(to bottom, #bab1ba 5%, #ededed 100%);
            background-color:#bab1ba;
        }
        .myButtonCekSaldo:active {
            position:relative;
            top:1px;
        }

        #imagePreview img {
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 5px;
            height: 150px;
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
            <!-- Konten Utama -->
            <main class="content">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Categories</li>
                    </ol>
                </nav>
                <?php
                include 'navigation.php';
                ?>

<div class="container-fluid">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Manage Categories</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Form untuk menambah kategori baru -->
                                    <form action="" method="post">
                                        <div class="mb-3">
                                            <label for="category_name" class="form-label">Category Name</label>
                                            <input type="text" class="form-control" id="category_name" name="category_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea class="form-control" id="description" name="description"></textarea>
                                        </div>
                                        <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Category List</h3>
                                </div>
                                <div class="card-body">
                                    <table class="display table table-bordered table-striped table-hover responsive nowrap" style="width:100%" id="categoriesTable">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Category Name</th>
                                                <th>Description</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $categories = getCategories();
                                            if ($categories->num_rows > 0) {
                                                while ($row = $categories->fetch_assoc()) {
                                                    echo "<tr>";
                                                    echo "<td>{$row['category_id']}</td>";
                                                    echo "<td>{$row['category_name']}</td>";
                                                    echo "<td>{$row['description']}</td>";
                                                    echo "<td>
                                                            <form action='' method='post' style='display: inline;'>
                                                                <input type='hidden' name='delete_category_id' value='{$row['category_id']}'>
                                                                <button type='submit' name='delete_category' class='btn btn-danger btn-sm'>Delete</button>
                                                            </form>
                                                            <button type='button' class='btn btn-primary btn-sm' onclick='editCategory({$row['category_id']}, `{$row['category_name']}`, `{$row['description']}`)'>Edit</button>
                                                          </td>";
                                                    echo "</tr>";
                                                }
                                            } else {
                                                echo "<tr><td colspan='4'>No categories found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form untuk edit kategori -->
                    <div id="editCategoryModal" class="modal fade" tabindex="-1" role="dialog">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Category</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form action="" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" id="edit_category_id" name="edit_category_id">
                                        <div class="mb-3">
                                            <label for="edit_category_name" class="form-label">Category Name</label>
                                            <input type="text" class="form-control" id="edit_category_name" name="edit_category_name" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="edit_description" class="form-label">Description</label>
                                            <textarea class="form-control" id="edit_description" name="edit_description"></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="edit_category" class="btn btn-primary">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
<?php include 'footer.php'; ?>
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
        function editCategory(category_id, category_name, description) {
            $('#edit_category_id').val(category_id);
            $('#edit_category_name').val(category_name);
            $('#edit_description').val(description);
            $('#editCategoryModal').modal('show');
        }
    </script>
                <script>
                $(document).ready(function () {
                    $('#categoriesTable').DataTable({
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