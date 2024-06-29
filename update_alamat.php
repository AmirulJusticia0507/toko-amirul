<?php
// Include your database connection and session management here
include 'konekke_local.php';

session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Retrieve user_id from session
$user_id = $_SESSION['userid'];

if (isset($_POST['newAlamat'])) {
    $newAlamat = $_POST['newAlamat'];

    // Query untuk update alamat pengguna
    $update_query = "UPDATE users SET alamat = ? WHERE userid = ?";
    $stmt = $koneklocalhost->prepare($update_query);
    $stmt->bind_param("si", $newAlamat, $user_id);
    
    if ($stmt->execute()) {
        echo "Alamat berhasil diperbarui.";
    } else {
        echo "Gagal memperbarui alamat: " . $stmt->error;
    }

    $stmt->close();
}

// Close database connection
$koneklocalhost->close();
?>
