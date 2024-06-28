<?php
include 'konekke_local.php';

$product_id = $_POST['product_id'];

$query = "DELETE FROM products WHERE product_id = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();

echo "Product deleted successfully";
?>
