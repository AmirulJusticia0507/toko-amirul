<?php
include 'konekke_local.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = cleanInput($_POST['product_name']);
    $description = cleanInput($_POST['description']);
    $price = cleanInput($_POST['price']);
    $stock_quantity = cleanInput($_POST['stock_quantity']);
    $category_id = cleanInput($_POST['category_id']);
    $brand_id = cleanInput($_POST['brand_id']);
    $image_url = cleanInput($_POST['image_url']);
    $status = cleanInput($_POST['status']);
    $weight = cleanInput($_POST['weight']);
    $dimensions = cleanInput($_POST['dimensions']);
    $sku = cleanInput($_POST['sku']);

    // Generate product code
    $date = date('Y.m.d'); // Get current date in year.month.day format

    // Retrieve the latest sequence number for the current date
    $query = "SELECT MAX(product_code) AS max_code FROM products WHERE product_code LIKE '$date%'";
    $result = $koneklocalhost->query($query);
    $row = $result->fetch_assoc();

    // Extract sequence number from the max product code
    if ($row['max_code']) {
        $sequence = (int)substr($row['max_code'], 11) + 1; // Increment sequence number
    } else {
        $sequence = 1; // Start with sequence 1 if no previous codes exist
    }

    // Generate new product code
    $product_code = $date . '.' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

    // Prepare and execute the insert query
    $query = "INSERT INTO products (product_code, product_name, description, price, stock_quantity, category_id, brand_id, image_url, status, weight, dimensions, sku) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $koneklocalhost->prepare($query);
    $stmt->bind_param("ssssiiissdss", $product_code, $product_name, $description, $price, $stock_quantity, $category_id, $brand_id, $image_url, $status, $weight, $dimensions, $sku);

    if ($stmt->execute()) {
        header('Location: productmanagement.php');
        exit;
    } else {
        $error = "Error saving product";
    }
}

function cleanInput($input) {
    $search = array(
        '@<script[^>]*?>.*?</script>@si',   // Hapus script
        '@<[\/\!]*?[^<>]*?>@si',            // Hapus tag HTML
        '@<style[^>]*?>.*?</style>@siU',    // Hapus style tag
        '@<![\s\S]*?--[ \t\n\r]*>@'         // Hapus komentar
    );
    $output = preg_replace($search, '', $input);
    return $output;
}
?>
