<?php
include 'konekke_local.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_id = $_POST['brand_id'];

    // Query untuk mengambil data brand berdasarkan brand_id
    $sql = "SELECT * FROM brands WHERE brand_id = $brand_id";
    $result = $koneklocalhost->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode($row);
    } else {
        echo json_encode([]);
    }
}
?>
