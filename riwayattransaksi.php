<?php
include 'konekke_local.php';
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['userid'];
$is_admin = $_SESSION['status'] === 'Admin';

// Fetch user transactions
$query = "SELECT * FROM amirulpay_transactions WHERE user_id = ? ORDER BY transaction_date DESC";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user details
$query_user = "SELECT tanggalpengemasan, tanggalpengiriman FROM users WHERE userid = ?";
$stmt_user = $koneklocalhost->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

// Function to format amount to Indonesian Rupiah
function format_rupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Handle form submission for tanggalpengemasan and tanggalpengiriman
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tanggalpengemasan'])) {
        $tanggalpengemasan = $_POST['tanggalpengemasan'];

        // Update tanggalpengemasan in users table
        $update_query = "UPDATE users SET tanggalpengemasan = ? WHERE userid = ?";
        $stmt = $koneklocalhost->prepare($update_query);
        $stmt->bind_param("si", $tanggalpengemasan, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['tanggalpengiriman'])) {
        $tanggalpengiriman = $_POST['tanggalpengiriman'];

        // Update tanggalpengiriman in users table
        $update_query = "UPDATE users SET tanggalpengiriman = ? WHERE userid = ?";
        $stmt = $koneklocalhost->prepare($update_query);
        $stmt->bind_param("si", $tanggalpengiriman, $user_id);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['mark_complete'])) {
        $transaction_id = $_POST['transaction_id'];

        // Update transaction status to Completed
        $update_query = "UPDATE amirulpay_transactions SET status = 'Completed' WHERE transaction_id = ?";
        $stmt = $koneklocalhost->prepare($update_query);
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $stmt->close();
    }

    header('Location: riwayattransaksi.php');
    exit;
}

// Function to check and update pending transactions to failed after 24 hours
function update_failed_transactions($koneklocalhost) {
    $query = "UPDATE amirulpay_transactions 
              SET status = 'Failed' 
              WHERE status = 'Pending' 
              AND transaction_date < NOW() - INTERVAL 1 DAY";
    $koneklocalhost->query($query);
}

// Call the function to update failed transactions
update_failed_transactions($koneklocalhost);
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
    <link rel="icon" href="img/amirulshop.png" type="image/png">
    <style>
        .btn-link {
            text-decoration: none;
            color: #007bff;
        }

        .btn-link:hover {
            text-decoration: underline;
        }

        .card-header {
            background-color: #f7f7f7;
        }

        #notification {
            display: none;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f8f8f8;
            color: #333;
        }

        .myButtonCekSaldo {
            box-shadow: 3px 4px 0px 0px #899599;
            background: linear-gradient(to bottom, #ededed 5%, #bab1ba 100%);
            background-color: #ededed;
            border-radius: 15px;
            border: 1px solid #d6bcd6;
            display: inline-block;
            cursor: pointer;
            color: #3a8a9e;
            font-family: Arial;
            font-size: 17px;
            padding: 7px 25px;
            text-decoration: none;
            text-shadow: 0px 1px 0px #e1e2ed;
        }

        .myButtonCekSaldo:hover {
            background: linear-gradient(to bottom, #bab1ba 5%, #ededed 100%);
            background-color: #bab1ba;
        }

        .myButtonCekSaldo:active {
            position: relative;
            top: 1px;
        }

        #imagePreview img {
            margin-right: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            padding: 5px;
            height: 150px;
        }

        .timeline {
            position: relative;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 6px;
            background-color: #ddd;
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -3px;
        }

        .timeline .container {
            padding: 10px 40px;
            position: relative;
            background-color: inherit;
            width: 50%;
        }

        .timeline .container.left {
            left: 0;
        }

        .timeline .container.right {
            left: 50%;
        }

        .timeline .container::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            right: -17px;
            background-color: white;
            border: 4px solid #ddd;
            top: 15px;
            border-radius: 50%;
            z-index: 1;
        }

        .timeline .container.right::after {
            left: -16px;
        }

        .timeline .content {
            padding: 20px 30px;
            background-color: white;
            position: relative;
            border-radius: 6px;
        }

        .timeline-date {
            font-size: 1.2em;
            font-weight: bold;
            color: #007BFF;
        }

        .btn-custom {
            margin: 5px 0;
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

                    <div class="timeline">
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="container left">
                            <div class="content">
                                <span
                                    class="timeline-date"><?= date('d M Y H:i:s', strtotime($row['transaction_date'])) ?></span>
                                <h2><?= htmlspecialchars($row['payment_method']) ?></h2>
                                <p>Amount: <?= format_rupiah($row['amount']) ?></p>
                                <p>Status: <?= htmlspecialchars($row['status']) ?></p>

                                <?php if ($is_admin && $row['status'] == 'Dikemas' && empty($user_data['tanggalpengemasan'])): ?>
                                <form method="post">
                                    <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
                                    <div class="form-group">
                                        <label for="tanggalpengemasan">Tanggal Pengemasan:</label>
                                        <input type="date" class="form-control" id="tanggalpengemasan"
                                            name="tanggalpengemasan" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-custom">Simpan Tanggal
                                        Pengemasan</button>
                                </form>
                                <?php endif; ?>
                                <?php if ($is_admin && $row['status'] == 'Dikirim' && empty($user_data['tanggalpengiriman'])): ?>
                                <form method="post">
                                    <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
                                    <div class="form-group">
                                        <label for="tanggalpengiriman">Tanggal Pengiriman:</label>
                                        <input type="date" class="form-control" id="tanggalpengiriman"
                                            name="tanggalpengiriman" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-custom">Simpan Tanggal
                                        Pengiriman</button>
                                </form>
                                <?php endif; ?>

                                <?php if ($row['status'] == 'Dikemas' && !empty($user_data['tanggalpengemasan'])): ?>
                                <p>Tanggal Pengemasan: <?= date('d M Y', strtotime($user_data['tanggalpengemasan'])) ?>
                                </p>
                                <?php endif; ?>

                                <?php if ($row['status'] == 'Dikirim' && !empty($user_data['tanggalpengiriman'])): ?>
                                <p>Tanggal Pengiriman: <?= date('d M Y', strtotime($user_data['tanggalpengiriman'])) ?>
                                </p>
                                <?php endif; ?>

                                <?php if ($is_admin && $row['status'] == 'Dikirim'): ?>
                                <form method="post">
                                    <input type="hidden" name="transaction_id" value="<?= $row['transaction_id'] ?>">
                                    <button type="submit" name="mark_complete" class="btn btn-success btn-custom">Tandai
                                        Selesai</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            </main>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <!-- Sertakan script Bootstrap dan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        // JavaScript untuk inisialisasi plugin Select2
        $(document).ready(function () {
            $('.select2').select2();
        });
    </script>
</body>

</html>