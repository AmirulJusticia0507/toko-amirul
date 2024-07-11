<?php
include 'konekke_local.php';

// Periksa apakah pengguna telah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    // Jika tidak ada sesi pengguna, alihkan ke halaman login
    header('Location: login.php');
    exit;
}

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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create or update brand
    if (isset($_POST['submit'])) {
        $brand_name = $_POST['brand_name'];
        $description = $_POST['description'];

        // Insert or update query based on brand_id existence
        if (isset($_POST['brand_id']) && !empty($_POST['brand_id'])) {
            // Update brand
            $brand_id = $_POST['brand_id'];
            $sql = "UPDATE brands SET brand_name='$brand_name', description='$description' WHERE brand_id=$brand_id";
        } else {
            // Insert new brand
            $sql = "INSERT INTO brands (brand_name, description) VALUES ('$brand_name', '$description')";
        }

        if ($koneklocalhost->query($sql) === TRUE) {
            echo '<div id="notification" class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    Brand saved successfully!
                  </div>';
        } else {
            echo '<div id="notification" class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    Error: ' . $koneklocalhost->error . '
                  </div>';
        }
    }

    // Delete brand
    if (isset($_POST['delete'])) {
        $brand_id = $_POST['brand_id'];

        // Retrieve brand details before deletion
        $querySelect = "SELECT brand_name, description FROM brands WHERE brand_id = ?";
        $stmtSelect = $koneklocalhost->prepare($querySelect);
        $stmtSelect->bind_param("i", $brand_id);
        $stmtSelect->execute();
        $stmtSelect->bind_result($brand_name, $description);
        $stmtSelect->fetch();
        $stmtSelect->close();

        // Delete brand
        $sqlDelete = "DELETE FROM brands WHERE brand_id=$brand_id";
        if ($koneklocalhost->query($sqlDelete) === TRUE) {
            // Insert into log_delete_brands
            $deletedBy = $_SESSION['username'];
            $additionalInfo = "Brand '$brand_name' deleted by $deletedBy";
            $logQuery = "INSERT INTO log_delete_brands (brand_id, deleted_by, additional_info) VALUES (?, ?, ?)";
            $stmtLog = $koneklocalhost->prepare($logQuery);
            $stmtLog->bind_param("iss", $brand_id, $deletedBy, $additionalInfo);

            if ($stmtLog->execute()) {
                echo '<div id="notification" class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        Brand deleted successfully!
                      </div>';
            } else {
                echo '<div id="notification" class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        Error logging delete action: ' . $stmtLog->error . '
                      </div>';
            }
        } else {
            echo '<div id="notification" class="alert alert-danger alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    Error deleting brand: ' . $koneklocalhost->error . '
                  </div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Brands Product - Toko Amirul</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Brands Product</li>
                    </ol>
                </nav>
                <?php
                include 'navigation.php';
                ?>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Brands Product</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Brand Form -->
                                    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
                                        <div class="mb-3">
                                            <label for="brand_name" class="form-label">Brand Name</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="brand_name" name="brand_name" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                                </div>
                                                <textarea class="form-control" id="description" name="description"></textarea>
                                            </div>
                                        </div>

                                        <input type="hidden" id="brand_id" name="brand_id">
                                        <button type="submit" name="submit" class="btn btn-primary">Save Brand</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Brand List</h3>
                                </div>
                                <div class="card-body">
                                    <table class="display table table-bordered table-striped table-hover responsive nowrap" style="width:100%" id="brandsTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Brand Name</th>
                                                <th>Description</th>
                                                <th>Created At</th>
                                                <th>Updated At</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Retrieve brands from database
                                            $sql = "SELECT * FROM brands";
                                            $result = $koneklocalhost->query($sql);

                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo '<tr>
                                                            <td>' . $row['brand_id'] . '</td>
                                                            <td>' . $row['brand_name'] . '</td>
                                                            <td>' . $row['description'] . '</td>
                                                            <td>' . $row['created_at'] . '</td>
                                                            <td>' . $row['updated_at'] . '</td>
                                                            <td>
                                                                <button class="btn btn-sm btn-primary edit-brand" data-id="' . $row['brand_id'] . '">Edit</button>
                                                                <form method="post" action="' . $_SERVER['PHP_SELF'] . '" style="display:inline;">
                                                                    <input type="hidden" name="brand_id" value="' . $row['brand_id'] . '">
                                                                    <button type="submit" name="delete" class="btn btn-sm btn-danger">Delete</button>
                                                                </form>
                                                            </td>
                                                        </tr>';
                                                }
                                            } else {
                                                echo '<tr><td colspan="6" class="text-center">No brands found</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
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
        $(document).ready(function() {
            $('#brandsTable').DataTable({
                responsive: true,
                scrollX: true,
                searching: true,
                lengthMenu: [10, 25, 50, 100, 500, 1000],
                pageLength: 10,
                dom: 'lBfrtip'
            });

            // Handle Edit button click
            $('.edit-brand').on('click', function() {
                var brand_id = $(this).data('id');

                // Fetch brand details using AJAX
                $.ajax({
                    url: 'fetch_brand.php', // URL file PHP untuk mengambil data brand
                    method: 'POST',
                    data: { brand_id: brand_id },
                    dataType: 'json',
                    success: function(response) {
                        if (response) {
                            // Populate form fields with brand data
                            $('#brand_name').val(response.brand_name);
                            $('#description').val(response.description);
                            $('#brand_id').val(response.brand_id);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                    }
                });
            });
        });
    </script>
</body>
</html>