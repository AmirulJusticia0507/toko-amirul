<?php
include 'konekke_local.php';

$product_id = $_POST['product_id'];

$query = "SELECT * FROM products WHERE product_id = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

echo json_encode($product);
?>
