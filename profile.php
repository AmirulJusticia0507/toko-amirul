<?php
include 'konekke_local.php';

session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Ambil informasi pengguna yang sedang login
$userid = $_SESSION['userid'];
$query = "SELECT * FROM `users` WHERE `userid` = '$userid'";
$result = mysqli_query($koneklocalhost, $query);

if (!$result) {
    die("Query error: " . mysqli_error($koneklocalhost));
}

$row = mysqli_fetch_assoc($result);
$status = $row['status'];

// Jika yang login adalah Customer, batasi hanya menampilkan data dirinya sendiri
if ($status == 'Customer') {
    $query = "SELECT * FROM `users` WHERE `userid` = '$userid'";
} else {
    // Jika yang login adalah Admin, tampilkan semua data pengguna
    $query = "SELECT * FROM `users`";
}

$result = mysqli_query($koneklocalhost, $query);

if (!$result) {
    die("Query error: " . mysqli_error($koneklocalhost));
}

// Proses update profile jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $alamat = $_POST['alamat'];
    $no_hp = $_POST['no_hp'];

    // Query untuk update data profile
    $update_query = "UPDATE `users` SET `fullname` = '$fullname', `alamat` = '$alamat', `no_hp` = '$no_hp' WHERE `userid` = '$userid'";
    $update_result = mysqli_query($koneklocalhost, $update_query);

    if ($update_result) {
        echo '<script>alert("Profile updated successfully!");</script>';
    } else {
        echo '<script>alert("Failed to update profile. Please try again.");</script>';
    }

    // Refresh data setelah update
    $result = mysqli_query($koneklocalhost, "SELECT * FROM `users` WHERE `userid` = '$userid'");
    $row = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Profile - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahkan link AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Tambahkan link DataTables CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="checkbox.css">
    <!-- Sertakan CSS Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
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
                        <li class="breadcrumb-item active" aria-current="page">Profile</li>
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
                                    <h3 class="card-title">Profile Information</h3>
                                </div>
                                <div class="card-body">
                                    <!-- Form Edit Profile -->
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo $row['username']; ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo $row['fullname']; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo $row['alamat']; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="no_hp" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo $row['no_hp']; ?>">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Update Profile</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($status != 'Customer'): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">User List</h3>
                                </div>
                                <div class="card-body">
                                    <table id="userTable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>UserID</th>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                                                <tr>
                                                    <td><?php echo $user['userid']; ?></td>
                                                    <td><?php echo $user['username']; ?></td>
                                                    <td><?php echo $user['fullname']; ?></td>
                                                    <td><?php echo $user['status']; ?></td>
                                                    <td>
                                                        <a href="edit_user.php?userid=<?php echo $user['userid']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                        <?php if ($status == 'Admin'): ?>
                                                            <a href="delete_user.php?userid=<?php echo $user['userid']; ?>" class="btn btn-sm btn-danger">Delete</a>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
<?php include 'footer.php'; ?>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.9.2/umd/popper.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<!-- Tambahkan Select2 -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
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