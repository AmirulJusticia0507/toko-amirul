<?php
session_start();
include 'konekke_local.php';

if (isset($_SESSION['userid'])) {

    // Update logout_date ke dalam tabel users_new
    date_default_timezone_set('Asia/Jakarta');
    $logoutDate = date('Y-m-d H:i:s');
    $userId = $_SESSION['userid'];

    $updateQuery = "UPDATE db_toko_amirul.users SET logout_date = ? WHERE userid = ?";
    $updateStmt = $koneklocalhost->prepare($updateQuery);
    $updateStmt->bind_param("si", $logoutDate, $userId);
    $updateStmt->execute();

    // Hapus semua variabel sesi
    session_unset();

    // Hapus sesi
    session_destroy();

    // Arahkan ke halaman login
    header("Location: login.php");
    exit; // Menghentikan eksekusi skrip setelah redirect
} else {
    // Jika tidak ada sesi, arahkan kembali ke halaman login default
    header("Location: login.php");
    exit; // Menghentikan eksekusi skrip setelah redirect
}
?>
