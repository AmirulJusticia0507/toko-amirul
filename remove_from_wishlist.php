<?php
include 'konekke_local.php';

// Periksa apakah pengguna telah terautentikasi
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    $user_id = $_SESSION['userid'];

    // Hapus produk dari wishlist
    $query = "DELETE FROM wishlist WHERE product_id = ? AND user_id = ?";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Kembali ke halaman wishlist
header('Location: wishlist.php');
exit;
?>
