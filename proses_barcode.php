<?php
include 'konekke_local.php';

// Ambil data barcode dari request POST
$barcode = $_POST['barcode'];

// Query database untuk mendapatkan informasi produk berdasarkan barcode
$query = "SELECT * FROM products WHERE barcode = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("s", $barcode);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    // Kirim respon dengan informasi produk
    echo json_encode($product);
} else {
    echo json_encode(["message" => "Produk tidak ditemukan"]);
}

$stmt->close();
?>
