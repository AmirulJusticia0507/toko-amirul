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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $fullname = mysqli_real_escape_string($koneklocalhost, $_POST['fullname']);
    $alamat = mysqli_real_escape_string($koneklocalhost, $_POST['alamat']);
    $no_hp = mysqli_real_escape_string($koneklocalhost, $_POST['no_hp']);
    $edit_userid = mysqli_real_escape_string($koneklocalhost, $_POST['userid']);

    // Query untuk update data profile
    $update_query = "UPDATE `users` SET `fullname` = '$fullname', `alamat` = '$alamat', `no_hp` = '$no_hp' WHERE `userid` = '$edit_userid'";
    $update_result = mysqli_query($koneklocalhost, $update_query);

    if ($update_result) {
        echo '<script>alert("Profile updated successfully!");</script>';
    } else {
        echo '<script>alert("Failed to update profile. Please try again.");</script>';
    }
}

// Proses delete user jika button di klik
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $delete_userid = mysqli_real_escape_string($koneklocalhost, $_POST['userid']);

    if ($status != 'Customer') {
        // Log the deletion before actually deleting the user
        $log_query = "INSERT INTO `log_delete_users` (`user_id`, `deleted_at`, `deleted_by`, `additional_info`) VALUES ('$delete_userid', NOW(), '$userid', 'User deleted by admin')";
        $log_result = mysqli_query($koneklocalhost, $log_query);

        if ($log_result) {
            // Query untuk delete user
            $delete_query = "DELETE FROM `users` WHERE `userid` = '$delete_userid'";
            $delete_result = mysqli_query($koneklocalhost, $delete_query);

            if ($delete_result) {
                echo '<script>alert("User deleted successfully!");</script>';
            } else {
                echo '<script>alert("Failed to delete user. Please try again.");</script>';
            }
        } else {
            echo '<script>alert("Failed to log the deletion. Please try again.");</script>';
        }
    } else {
        echo '<script>alert("Customers are not allowed to delete users!");</script>';
    }
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <!-- Sertakan CSS Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css">
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
                    <!-- <div class="row">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Profile Information</h3>
                                </div>
                                <div class="card-body">
                                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label for="username" class="form-label">Username</label>
                                            <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($row['username']); ?>" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fullname" class="form-label">Full Name</label>
                                            <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($row['fullname']); ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label">Address</label>
                                            <input type="text" class="form-control" id="alamat" name="alamat" value="<?php echo isset($row['alamat']) ? htmlspecialchars($row['alamat']) : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="no_hp" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo isset($row['no_hp']) ? htmlspecialchars($row['no_hp']) : ''; ?>">
                                        </div>
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-info"><i class="fas fa-square-pen"></i> Update Profile</button>
                                            <button type="reset" class="btn btn-danger"><i class="fas fa-power-off"></i> Reset</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div> -->
                    <?php if ($status != 'Customer'): ?>
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">User List</h3>
                                </div>
                                <div class="card-body">
                                    <table id="userTable" class="display table table-bordered table-striped table-hover responsive nowrap" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>UserID</th>
                                                <th>Username</th>
                                                <th>Full Name</th>
                                                <th>Address</th>
                                                <th>Phone Number</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $user_query = "SELECT * FROM `users`";
                                            $user_result = mysqli_query($koneklocalhost, $user_query);
                                            $nomorUrutTerakhir = 1;
                                            while ($user = mysqli_fetch_assoc($user_result)) {
                                                echo '<tr>';
                                                echo "<td style='text-align:center; width: 2px; font-size: 10pt; white-space: normal;'>" . $nomorUrutTerakhir . "</td>";
                                                // echo '<td>' . htmlspecialchars($user['userid']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['username']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['fullname']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['alamat']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['no_hp']) . '</td>';
                                                echo '<td>' . htmlspecialchars($user['status']) . '</td>';
                                                echo '<td>
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#editModal" data-userid="' . htmlspecialchars($user['userid']) . '" data-fullname="' . htmlspecialchars($user['fullname']) . '" data-alamat="' . htmlspecialchars($user['alamat']) . '" data-no_hp="' . htmlspecialchars($user['no_hp']) . '">Edit</button>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="userid" value="' . htmlspecialchars($user['userid']) . '">
                                                        <button type="submit" name="delete_user" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this user?\');">Delete</button>
                                                    </form>
                                                </td>';
                                                $nomorUrutTerakhir++;
                                                echo '</tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Modal untuk Edit Profile -->
                <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="editModalLabel">Edit User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                                <div class="modal-body">
                                    <input type="hidden" id="edit_userid" name="userid">
                                    <div class="mb-3">
                                        <label for="edit_fullname" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="edit_fullname" name="fullname">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_alamat" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="edit_alamat" name="alamat">
                                    </div>
                                    <div class="mb-3">
                                        <label for="edit_no_hp" class="form-label">Phone Number</label>
                                        <input type="text" class="form-control" id="edit_no_hp" name="no_hp">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="update_profile" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
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
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
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
    <script>
        $(document).ready(function () {
            $('#userTable').DataTable({
                responsive: true,
                scrollX: true,
                searching: true,
                lengthMenu: [10, 25, 50, 100, 500, 1000],
                pageLength: 10,
                dom: 'lBfrtip'
            });
        });
    </script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var editModal = document.getElementById('editModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var userid = button.getAttribute('data-userid');
            var fullname = button.getAttribute('data-fullname');
            var alamat = button.getAttribute('data-alamat');
            var no_hp = button.getAttribute('data-no_hp');

            var modalUserId = editModal.querySelector('#edit_userid');
            var modalFullName = editModal.querySelector('#edit_fullname');
            var modalAlamat = editModal.querySelector('#edit_alamat');
            var modalNoHp = editModal.querySelector('#edit_no_hp');

            modalUserId.value = userid;
            modalFullName.value = fullname;
            modalAlamat.value = alamat;
            modalNoHp.value = no_hp;
        });
    });
    </script>
</body>
</html>