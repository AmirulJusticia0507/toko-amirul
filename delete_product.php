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

// Ambil product_id dari POST request
if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    
    // Persiapkan query untuk menghapus produk
    $deleteQuery = "DELETE FROM products WHERE product_id = ?";
    $stmt = $koneklocalhost->prepare($deleteQuery);
    $stmt->bind_param("i", $product_id);
    
    // Eksekusi query untuk menghapus produk
    if ($stmt->execute()) {
        // Simpan log penghapusan ke dalam tabel log_delete_products
        $deletedBy = $_SESSION['username'];
        $additionalInfo = "Product ID $product_id deleted by $deletedBy";
        $logQuery = "INSERT INTO log_delete_products (product_id, deleted_by, additional_info) VALUES (?, ?, ?)";
        $stmtLog = $koneklocalhost->prepare($logQuery);
        $stmtLog->bind_param("iss", $product_id, $deletedBy, $additionalInfo);
        
        // Eksekusi query untuk menyimpan log penghapusan
        if ($stmtLog->execute()) {
            echo "Product deleted successfully";
        } else {
            die("Error logging delete action: " . $stmtLog->error);
        }
    } else {
        die("Error deleting product: " . $stmt->error);
    }
} else {
    // Jika tidak ada product_id yang diberikan, beri pesan error
    die("Product ID not provided");
}
?>
