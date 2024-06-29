<?php
include 'konekke_local.php';

// Periksa apakah pengguna telah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    // Jika tidak ada sesi pengguna, alihkan ke halaman login
    header('Location: login.php');
    exit;
}

// Ambil user_id dari sesi
$user_id = $_SESSION['userid'];

// Ambil informasi pengguna dari database
$query = "SELECT fullname, no_hp, saldo FROM users WHERE userid = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $fullname = $row['fullname'];
    $no_hp = $row['no_hp'];
    $saldo = $row['saldo'];
} else {
    // Handle case where user is not found (though it should not happen if authentication is correct)
    // Optionally redirect or show an error
    echo "User not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_no_hp = $_POST['no_hp'];
    $new_saldo = intval($_POST['saldo']);

    // Update user data in the database
    $update_query = "UPDATE users SET no_hp = ?, saldo = ? WHERE userid = ?";
    $update_stmt = $koneklocalhost->prepare($update_query);
    $update_stmt->bind_param("sii", $new_no_hp, $new_saldo, $user_id);
    if ($update_stmt->execute()) {
        $message = "User data updated successfully.";
    } else {
        $message = "Error updating user data.";
    }
    $update_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Saldo - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tambahkan link AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Tambahkan link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="icon" href="img/amirulshop.png" type="image/png">
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
                        <li class="breadcrumb-item active" aria-current="page">Saldo</li>
                    </ol>
                </nav>
                <?php
                include 'navigation.php';
                ?>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Informasi Saldo</h4>
                                <?php if (isset($message)): ?>
                                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                                <?php endif; ?>
                                <form method="post">
                                    <div class="mb-3">
                                        <label for="fullname" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="fullname" value="<?php echo htmlspecialchars($fullname); ?>" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label">Nomor HP</label>
                                        <input type="text" class="form-control" id="no_hp" name="no_hp" value="<?php echo htmlspecialchars($no_hp); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="saldo" class="form-label">Saldo</label>
                                        <input type="number" class="form-control" id="saldo" name="saldo" value="<?php echo htmlspecialchars($saldo); ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </form>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-center text-muted">Saldo Terkini</span>
                                        <span id="saldoTerkini" class="info-box-number text-center text-muted mb-0"><?php echo "Rp " . number_format($saldo, 0, ',', '.'); ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
            </main>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/numeral.js/2.0.6/numeral.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <script>
        // Fungsi untuk memformat angka menjadi format mata uang Rupiah
        function formatRupiah(angka) {
            return numeral(angka).format('0,0'); // Format sesuai dengan kebutuhan (misalnya '0,0.00' untuk dua desimal)
        }

        // Ketika halaman dimuat, format saldo ke format Rupiah
        document.addEventListener("DOMContentLoaded", function() {
            var saldoInput = document.getElementById('saldo');
            saldoInput.value = formatRupiah(<?php echo $saldo; ?>);
            
            // Event listener untuk menghapus tanda titik saat form disubmit
            document.querySelector('form').addEventListener('submit', function() {
                saldoInput.value = saldoInput.value.replace(/\./g, "");
            });
        });
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
