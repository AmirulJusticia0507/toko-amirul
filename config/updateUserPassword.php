<?php
// Include file koneksi database
include 'konekke_local.php';

// Function untuk membuat salt random
function generateSalt() {
    return bin2hex(random_bytes(16));
}

// Function untuk melakukan hashing password dengan salt
function hashPassword($password, $salt) {
    return hash('sha256', $password . $salt);
}

// Function untuk mengubah password user di database
function updateUserPassword($id, $password) {
    global $koneklocalhost;

    // Generate salt baru
    $salt = generateSalt();
    // Hash password baru dengan salt
    $hashedPassword = hashPassword($password, $salt);

    // Prepared statement untuk mencegah SQL injection
    $stmt = $koneklocalhost->prepare("UPDATE users SET password = ?, salt = ? WHERE id = ?");
    $stmt->bind_param("ssi", $hashedPassword, $salt, $id);
    $stmt->execute();
    $stmt->close();

    // Redirect ke halaman profile.php setelah operasi selesai
    header("Location: profile.php");
    exit(); // Pastikan tidak ada output lain yang di-generate setelah header
}
?>
