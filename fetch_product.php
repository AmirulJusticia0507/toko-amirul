<?php
include 'konekke_local.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_id = isset($_POST['product_id']) ? $_POST['product_id'] : '';

    if (!empty($product_id)) {
        $query = "SELECT * FROM products WHERE product_id = ?";
        $stmt = $koneklocalhost->prepare($query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            echo json_encode(['error' => 'Product not found']);
        }
    } else {
        echo json_encode(['error' => 'Invalid product ID']);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
