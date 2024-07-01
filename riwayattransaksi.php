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

// Ambil data transaksi dari database
$query = "SELECT * FROM amirulpay_transactions WHERE user_id = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Close statement
$stmt->close();

// Function to format amount to Indonesian Rupiah
function format_rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>History Transaction - Toko Amirul</title>
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
    <style>
        /* CSS untuk Timeline */
.timeline {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
}

.timeline::after {
    content: '';
    position: absolute;
    width: 6px;
    background-color: white;
    top: 0;
    bottom: 0;
    left: 50%;
    margin-left: -3px;
}

.container {
    padding: 10px 40px;
    position: relative;
    background-color: inherit;
    width: 50%;
}

.container::after {
    content: '';
    position: absolute;
    width: 25px;
    height: 25px;
    right: -17px;
    background-color: white;
    border: 4px solid #FF9F55;
    top: 15px;
    border-radius: 50%;
    z-index: 1;
}

.left {
    left: 0;
}

.right {
    left: 50%;
}

.left::before {
    content: " ";
    height: 0;
    position: absolute;
    top: 22px;
    width: 0;
    z-index: 1;
    right: 30px;
    border: medium solid white;
    border-width: 10px 0 10px 10px;
    border-color: transparent transparent transparent white;
}

.right::before {
    content: " ";
    height: 0;
    position: absolute;
    top: 22px;
    width: 0;
    z-index: 1;
    left: 30px;
    border: medium solid white;
    border-width: 10px 10px 10px 0;
    border-color: transparent white transparent transparent;
}

.right::after {
    left: -16px;
}

.content {
    padding: 20px 30px;
    background-color: white;
    position: relative;
    border-radius: 6px;
}

/* Responsive timeline */
@media screen and (max-width: 600px) {
    .timeline::after {
        left: 31px;
    }

    .container {
        width: 100%;
        padding-left: 70px;
        padding-right: 25px;
    }

    .container::before {
        left: 60px;
        border: medium solid white;
        border-width: 10px 10px 10px 0;
        border-color: transparent white transparent transparent;
    }

    .left::after, .right::after {
        left: 15px;
    }

    .right {
        left: 0%;
    }
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
                        <li class="breadcrumb-item active" aria-current="page">Transaction History</li>
                    </ol>
                </nav>
                <?php
                include 'navigation.php';
                ?>

            <div class="container">
                <h1 class="mt-4 mb-4">Riwayat Transaksi</h1>

                <!-- Timeline -->
                <div class="timeline">
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="container left">
                            <div class="content">
                                <span class="timeline-date"><?= date('d M Y H:i:s', strtotime($row['transaction_date'])) ?></span>
                                <h2><?= $row['payment_method'] ?></h2>
                                <p>Amount: <?= format_rupiah($row['amount']) ?></p>
                                <p>Status: <?= $row['status'] ?></p>

                                <?php if ($row['status'] == 'Dikemas' && empty($row['tanggalpengemasan'])): ?>
                                    <!-- Form untuk input tanggal pengemasan -->
                                    <form method="post">
                                        <div class="mb-3">
                                            <label for="tanggalpengemasan">Tanggal Pengemasan:</label>
                                            <input type="date" class="form-control" id="tanggalpengemasan" name="tanggalpengemasan">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </form>
                                <?php elseif ($row['status'] == 'Dikirim' && empty($row['tanggalpengiriman'])): ?>
                                    <!-- Form untuk input tanggal pengiriman -->
                                    <form method="post">
                                        <div class="mb-3">
                                            <label for="tanggalpengiriman">Tanggal Pengiriman:</label>
                                            <input type="date" class="form-control" id="tanggalpengiriman" name="tanggalpengiriman">
                                        </div>
                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
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
<?php
// Handle form submission for tanggalpengemasan and tanggalpengiriman
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tanggalpengemasan'])) {
        $tanggalpengemasan = $_POST['tanggalpengemasan'];
        $transaction_id = $_POST['transaction_id'];

        // Update tanggalpengemasan in amirulpay_transactions table
        $update_query = "UPDATE amirulpay_transactions SET tanggalpengemasan = ? WHERE transaction_id = ?";
        $stmt = $koneklocalhost->prepare($update_query);
        $stmt->bind_param("si", $tanggalpengemasan, $transaction_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['tanggalpengiriman'])) {
        $tanggalpengiriman = $_POST['tanggalpengiriman'];
        $transaction_id = $_POST['transaction_id'];

        // Update tanggalpengiriman in amirulpay_transactions table
        $update_query = "UPDATE amirulpay_transactions SET tanggalpengiriman = ? WHERE transaction_id = ?";
        $stmt = $koneklocalhost->prepare($update_query);
        $stmt->bind_param("si", $tanggalpengiriman, $transaction_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect to prevent form resubmission on refresh
    header('Location: riwayattransaksi.php');
    exit;
}
?>

</body>
</html>