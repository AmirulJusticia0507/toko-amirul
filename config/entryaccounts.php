<?php
// Include file koneksi database
include '../konekke_local.php';

// Function untuk membuat salt random
function generateSalt() {
    return bin2hex(random_bytes(16));
}

// Function untuk melakukan hashing password dengan salt
function hashPassword($password, $salt) {
    return hash('sha256', $password . $salt);
}

// Function untuk menambahkan user baru ke database
function addUser($username, $password, $fullname, $birthplace, $birthdate, $sex, $photo) {
    global $koneklocalhost;

    // Generate salt baru
    $salt = generateSalt();
    // Hash password dengan salt
    $hashedPassword = hashPassword($password, $salt);

    // Prepared statement untuk mencegah SQL injection
    $stmt = $koneklocalhost->prepare("INSERT INTO users (username, password, salt, fullname, birthplace, birthdate, sex, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $username, $hashedPassword, $salt, $fullname, $birthplace, $birthdate, $sex, $photo);
    $stmt->execute();
    $stmt->close();

    // Redirect ke halaman profile.php setelah operasi selesai
    header("Location: profile.php");
    exit(); // Pastikan tidak ada output lain yang di-generate setelah header
}
?>
