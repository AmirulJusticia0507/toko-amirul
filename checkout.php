<?php
// Sertakan file koneksi database dan manajemen sesi di sini
include 'konekke_local.php';

// Periksa apakah pengguna sudah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Ambil user_id dari sesi
$user_id = $_SESSION['userid'];

// Query untuk mengambil informasi pengguna
$query_user = "SELECT fullname, no_hp, saldo, alamat FROM users WHERE userid = ?";
$stmt_user = $koneklocalhost->prepare($query_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows > 0) {
    $row_user = $result_user->fetch_assoc();
    $fullname = $row_user['fullname'];
    $no_hp = $row_user['no_hp'];
    $saldo = $row_user['saldo'];
    $alamat = $row_user['alamat'];

    // Format saldo to Indonesian Rupiah
    $saldo_formatted = "Rp " . number_format($saldo, 0, ',', '.');
} else {
    echo "Pengguna tidak ditemukan.";
    exit;
}

// Query untuk mengambil produk dari keranjang belanja
$query_cart = "SELECT ci.cart_item_id, p.product_name, p.price, ci.quantity 
               FROM cart_items ci 
               JOIN products p ON ci.product_id = p.product_id 
               WHERE ci.user_id = ?";
$stmt_cart = $koneklocalhost->prepare($query_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();
$cart_items = $result_cart->fetch_all(MYSQLI_ASSOC);

// Handle proses pembayaran
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['payment_method'])) {
        $payment_method = $_POST['payment_method'];

        // Handle tindakan khusus untuk metode pembayaran
        switch ($payment_method) {
            case 'AmirulPay':
                // Ambil saldo dari pengguna
                $saldo_pengguna = $saldo;

                // Handle proses pembayaran AmirulPay
                $amount = calculateTotalAmount($cart_items); // Fungsi untuk menghitung total jumlah

                // Masukkan transaksi ke tabel amirulpay_transactions
                $insert_query = "INSERT INTO amirulpay_transactions (user_id, amount, payment_method) VALUES (?, ?, 'AmirulPay')";
                $insert_stmt = $koneklocalhost->prepare($insert_query);
                $insert_stmt->bind_param("id", $user_id, $amount);
                $insert_stmt->execute();
                $insert_stmt->close();

                // Lakukan tindakan lain yang diperlukan untuk proses pembayaran AmirulPay

                // Redirect ke halaman sukses atau proses
                header('Location: processpayment.php');
                exit;
                break;
            // Tambahkan case untuk metode pembayaran lain jika diperlukan
        }
    }
}

// Tutup koneksi database
$koneklocalhost->close();

// Fungsi untuk menghitung total jumlah dari barang belanja
function calculateTotalAmount($cart_items) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pesanan - Toko Amirul</title>
    <!-- Sertakan Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Sertakan AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Sertakan Font Awesome CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Sertakan CSS Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <!-- Sertakan jQuery UI CSS -->
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <!-- Sertakan ikon -->
    <link rel="icon" href="img/amirulshop.png" type="image/png">
    <!-- Gaya tambahan -->
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
                        <li class="breadcrumb-item active" aria-current="page">Checkout Pesanan</li>
                    </ol>
                </nav>
                <?php include 'navigation.php'; ?>

                <!-- Notifikasi Waktu Pembayaran Terbatas -->
                <?php
                $deadline = strtotime('+24 hours', strtotime(date('Y-m-d H:i:s')));
                $current_time = time();
                $time_left = $deadline - $current_time;
                $hours_left = floor($time_left / 3600);
                $minutes_left = floor(($time_left % 3600) / 60);

                if ($hours_left <= 24) {
                    echo "<script>
                            $(document).ready(function() {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Waktu Pembayaran Terbatas',
                                    text: 'Anda memiliki $hours_left jam dan $minutes_left menit untuk menyelesaikan pembayaran Anda.',
                                    timer: 10000 // Sesuaikan timer sesuai kebutuhan
                                });
                            });
                        </script>";
                }
                ?>

                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Metode Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Existing form in checkout.php -->
                                    <form method="post" action="processpayment.php">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="payment_amirulpay" value="AmirulPay" checked>
                                            <label class="form-check-label" for="payment_amirulpay">
                                                AmirulPay (Saldo Anda: <?php echo htmlspecialchars($saldo_formatted); ?>)
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="payment_qris" value="QRIS">
                                            <label class="form-check-label" for="payment_qris">
                                                QRIS
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="payment_method" id="payment_transfer" value="Transfer">
                                            <label class="form-check-label" for="payment_transfer">
                                                Transfer
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">Proses Pembayaran</button>
                                    </form>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title">Ringkasan Pesanan</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nama Produk</th>
                                                <th>Harga</th>
                                                <th>Jumlah</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cart_items as $item) : ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                    <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total</th>
                                                <th>Rp <?php echo number_format(calculateTotalAmount($cart_items), 0, ',', '.'); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Ringkasan Pengiriman</h5>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled">
                                        <li><strong>Nama Lengkap:</strong> <?php echo htmlspecialchars($fullname); ?></li>
                                        <li><strong>No. HP:</strong> <?php echo htmlspecialchars($no_hp); ?></li>
                                        <li>
                                            <strong>Alamat:</strong> <span id="alamat"><?php echo htmlspecialchars($alamat); ?></span>
                                            <button id="editAlamatBtn" class="btn btn-sm btn-outline-primary ms-2" onclick="editAlamat()">Edit</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title">Status Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    <p>Pembayaran akan diverifikasi dalam waktu 1x24 jam.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="timer" class="text-center mt-3"></div>
                </div>
            </main>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <!-- Sertakan jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Sertakan Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Sertakan AdminLTE JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <!-- Sertakan Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- Sertakan SweetAlert2 -->
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sertakan jQuery UI JS -->
    <script src="//code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
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
        // Menghitung waktu tersisa dalam detik
        var timeLeft = <?php echo $time_left; ?>; // Sisa waktu dalam detik dari PHP

        // Fungsi untuk menampilkan dan mengupdate timer setiap detik
        function updateTimer() {
            var hours = Math.floor(timeLeft / 3600);
            var minutes = Math.floor((timeLeft % 3600) / 60);
            var seconds = timeLeft % 60;

            // Format waktu dengan dua digit untuk menampilkan timer
            var formattedTime = pad(hours, 2) + ":" + pad(minutes, 2) + ":" + pad(seconds, 2);

            // Memperbarui elemen HTML dengan waktu yang tersisa
            document.getElementById('timer').innerHTML = "<p>Anda memiliki " + formattedTime + " untuk menyelesaikan pembayaran Anda.</p>";

            // Kurangi sisa waktu
            timeLeft--;

            // Jika waktu habis, lakukan tindakan sesuai kebutuhan
            if (timeLeft < 0) {
                clearInterval(timerInterval); // Hentikan timer jika waktu telah habis
                document.getElementById('timer').innerHTML = "<p>Waktu pembayaran telah habis.</p>";
            }
        }

        // Memanggil fungsi updateTimer setiap detik
        var timerInterval = setInterval(updateTimer, 1000);

        // Fungsi untuk memastikan format waktu dua digit
        function pad(num, size) {
            var s = "000" + num;
            return s.substr(s.length - size);
        }
    </script>
    <script>
    function editAlamat() {
        var alamatSpan = document.getElementById('alamat');
        var currentAlamat = alamatSpan.innerHTML;

        // Ubah elemen HTML menjadi form input
        alamatSpan.innerHTML = '<input type="text" id="newAlamat" class="form-control" value="' + currentAlamat + '">';

        // Ganti tombol Edit menjadi tombol Simpan
        var editButton = document.getElementById('editAlamatBtn');
        editButton.innerHTML = 'Simpan';
        editButton.setAttribute('onclick', 'simpanAlamat()');
        editButton.classList.remove('btn-outline-primary');
        editButton.classList.add('btn-outline-success');
    }

    function simpanAlamat() {
        var newAlamat = document.getElementById('newAlamat').value;

        // Kirim data alamat yang baru ke server menggunakan AJAX atau form submit
        // Misalnya, menggunakan AJAX dengan jQuery
        $.ajax({
            url: 'update_alamat.php', // Ganti dengan URL atau endpoint yang benar
            method: 'POST',
            data: { newAlamat: newAlamat },
            success: function(response) {
                // Jika berhasil, update tampilan alamat di halaman
                var alamatSpan = document.getElementById('alamat');
                alamatSpan.innerHTML = newAlamat;

                // Kembalikan tombol ke mode Edit
                var editButton = document.getElementById('editAlamatBtn');
                editButton.innerHTML = 'Edit';
                editButton.setAttribute('onclick', 'editAlamat()');
                editButton.classList.remove('btn-outline-success');
                editButton.classList.add('btn-outline-primary');
            },
            error: function(xhr, status, error) {
                // Handle error jika diperlukan
                console.error('Error: ' + error);
            }
        });
    }
    </script>

</body>
</html>
