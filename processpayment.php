<?php
include 'konekke_local.php';
session_start();

if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['userid'];

// Query untuk mengambil informasi keranjang belanja
$query_cart = "SELECT ci.cart_item_id, p.product_name, p.price, ci.quantity 
               FROM cart_items ci 
               JOIN products p ON ci.product_id = p.product_id 
               WHERE ci.user_id = ?";
$stmt_cart = $koneklocalhost->prepare($query_cart);
$stmt_cart->bind_param("i", $user_id);
$stmt_cart->execute();
$result_cart = $stmt_cart->get_result();
$cart_items = $result_cart->fetch_all(MYSQLI_ASSOC);

// Fungsi untuk menghitung total jumlah dari barang belanja
function calculateTotalAmount($cart_items) {
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['payment_method'])) {
        $payment_method = $_POST['payment_method'];

        // Ambil saldo pengguna
        $query_user = "SELECT saldo FROM users WHERE userid = ?";
        $stmt_user = $koneklocalhost->prepare($query_user);
        $stmt_user->bind_param("i", $user_id);
        $stmt_user->execute();
        $result_user = $stmt_user->get_result();
        $row_user = $result_user->fetch_assoc();
        $saldo_pengguna = $row_user['saldo'];

        $amount = calculateTotalAmount($cart_items); // Hitung total jumlah

        // Handle proses pembayaran sesuai metode
        if ($payment_method == 'AmirulPay' && $saldo_pengguna >= $amount) {
            // Kurangi saldo pengguna
            $new_saldo = $saldo_pengguna - $amount;
            $update_saldo_query = "UPDATE users SET saldo = ? WHERE userid = ?";
            $update_saldo_stmt = $koneklocalhost->prepare($update_saldo_query);
            $update_saldo_stmt->bind_param("di", $new_saldo, $user_id);
            $update_saldo_stmt->execute();

            // Masukkan transaksi ke tabel amirulpay_transactions
            $insert_query = "INSERT INTO amirulpay_transactions (user_id, amount, payment_method, status) VALUES (?, ?, ?, 'Dibayar')";
            $insert_stmt = $koneklocalhost->prepare($insert_query);
            $insert_stmt->bind_param("ids", $user_id, $amount, $payment_method);
            $insert_stmt->execute();
            
            // Hapus item dari keranjang setelah pembayaran sukses
            $delete_cart_query = "DELETE FROM cart_items WHERE user_id = ?";
            $delete_cart_stmt = $koneklocalhost->prepare($delete_cart_query);
            $delete_cart_stmt->bind_param("i", $user_id);
            $delete_cart_stmt->execute();

            // Redirect ke halaman riwayat transaksi
            header('Location: riwayattransaksi.php');
            exit;
        } else {
            $error_message = "Saldo tidak cukup atau metode pembayaran tidak valid.";
        }
    } else {
        $error_message = "Metode pembayaran tidak dipilih.";
    }
} else {
    $error_message = "Permintaan tidak valid.";
}

if (isset($error_message)) {
    $_SESSION['error_message'] = $error_message;
    header('Location: checkout.php');
    exit;
}

// Tutup koneksi database
$koneklocalhost->close();
?>
