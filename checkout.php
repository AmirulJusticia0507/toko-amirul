<?php
// Include your database connection and session management here
include 'konekke_local.php';

// Check if user is authenticated
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Retrieve user_id from session
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
    echo "User not found.";
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

// Handle payment process
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['payment_method'])) {
        $payment_method = $_POST['payment_method'];

        // Handle specific payment method actions
        switch ($payment_method) {
            case 'AmirulPay':
                // Ambil saldo dari pengguna
                $saldo_pengguna = $saldo;

                // Handle AmirulPay payment method
                $amount = calculateTotalAmount($cart_items); // Function to calculate total amount

                // Insert transaction into amirulpay_transactions table
                $insert_query = "INSERT INTO amirulpay_transactions (user_id, amount, payment_method) VALUES (?, ?, 'AmirulPay')";
                $insert_stmt = $koneklocalhost->prepare($insert_query);
                $insert_stmt->bind_param("id", $user_id, $amount);
                $insert_stmt->execute();
                $insert_stmt->close();

                // Perform other necessary actions for AmirulPay payment process

                // Redirect to success page or process
                header('Location: success.php');
                exit;
                break;
            // Tambahkan case untuk metode pembayaran lain jika diperlukan
        }
    }
}

// Close database connection
$koneklocalhost->close();

// Function to calculate total amount from cart items
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>Checkout Pesanan - Toko Amirul</title>
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
                        <li class="breadcrumb-item active" aria-current="page">Checkout Pesanan</li>
                    </ol>
                </nav>
                <?php
                include 'navigation.php';
                ?>

<div class="container-fluid">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Metode Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
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
                                    <h5 class="card-title">Alamat Pengiriman</h5>
                                </div>
                                <div class="card-body">
                                    <form>
                                        <div class="mb-3">
                                            <label for="alamat" class="form-label">Alamat</label>
                                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($alamat); ?></textarea>
                                        </div>
                                        <!-- Tambahkan field alamat lainnya jika diperlukan -->
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Rincian Produk</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
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
                                                <?php foreach ($cart_items as $item): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['price']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                                                    <td><?php echo htmlspecialchars($item['price'] * $item['quantity']); ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
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