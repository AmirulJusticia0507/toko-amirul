<?php
include 'konekke_local.php';

session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Pastikan hanya admin yang dapat mengakses halaman ini
if ($_SESSION['status'] !== 'Admin') {
    die("Access denied. Only Admin can perform this action.");
}

// Ambil ID pengguna yang akan dihapus
if (isset($_GET['userid'])) {
    $userid = $_GET['userid'];
    
    // Lakukan penghapusan pengguna dari tabel users
    $deleteQuery = "DELETE FROM `users` WHERE `userid` = '$userid'";
    $result = mysqli_query($koneklocalhost, $deleteQuery);

    if (!$result) {
        die("Query error: " . mysqli_error($koneklocalhost));
    }

    // Simpan log penghapusan ke dalam tabel log_delete_users
    $deletedBy = $_SESSION['username'];
    $additionalInfo = "User ID $userid deleted by $deletedBy";
    $logQuery = "INSERT INTO `log_delete_users` (`user_id`, `deleted_by`, `additional_info`) VALUES ('$userid', '$deletedBy', '$additionalInfo')";
    $logResult = mysqli_query($koneklocalhost, $logQuery);

    if (!$logResult) {
        die("Error logging delete action: " . mysqli_error($koneklocalhost));
    }

    // Redirect ke halaman user list dengan pesan sukses
    header('Location: profile.php?success=delete');
    exit;
} else {
    // Jika tidak ada parameter userid yang diberikan, redirect ke halaman user list
    header('Location: profile.php');
    exit;
}
?>
