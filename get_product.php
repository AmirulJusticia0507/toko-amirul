<?php
// Sambungkan ke database
include 'konekke_local.php';

// Ambil product_id dari POST data
$product_id = $_POST['product_id'];

// Query untuk mengambil detail produk dengan parameterized query
$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("s", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Jika produk ditemukan, kirim data dalam format JSON
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    // Jika produk tidak ditemukan, kirim respons kosong
    echo json_encode([]);
}

// Tutup statement dan koneksi
$stmt->close();
$koneklocalhost->close();
?>
