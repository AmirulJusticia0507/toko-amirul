<?php
// Include file koneksi database
include 'konekke_local.php';

// Function untuk menghapus user dari database
function deleteUser($id) {
    global $koneklocalhost;

    // Prepared statement untuk mencegah SQL injection
    $stmt = $koneklocalhost->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    // Redirect ke halaman profile.php setelah operasi selesai
    header("Location: profile.php");
    exit(); // Pastikan tidak ada output lain yang di-generate setelah header
}
?>
