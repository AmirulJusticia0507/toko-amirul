<?php
include 'konekke_local.php';

$category_id = $_GET['category_id'];
$brand_id = $_GET['brand_id'];

$query = "SELECT p.product_id, p.product_name, p.description, p.price, p.stock_quantity, c.category_name, b.brand_name, p.status, p.product_image 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.category_id 
          LEFT JOIN brands b ON p.brand_id = b.brand_id 
          WHERE p.category_id = ? AND p.brand_id = ? 
          ORDER BY p.product_id DESC";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("ii", $category_id, $brand_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$row['product_id']}</td>";
        echo "<td>{$row['product_name']}</td>";
        echo "<td>";
        if (!empty($row['product_image'])) {
            $photoPath = "uploads/product/{$row['product_image']}";
            echo "<a href='{$photoPath}' data-fancybox='gallery'>";
            echo "<img src='{$photoPath}' alt='Product Image' style='max-width: 100px; max-height: 100px;' class='img-thumbnail'>";
            echo "</a>";
        } else {
            echo "No photo available";
        }
        echo "</td>";
        echo "<td>{$row['description']}</td>";
        echo "<td>" . number_format($row['price'], 0, ',', '.') . "</td>";
        echo "<td>{$row['stock_quantity']}</td>";
        echo "<td>{$row['category_name']}</td>";
        echo "<td>{$row['brand_name']}</td>";
        echo "<td>{$row['status']}</td>";
        echo "<td style='text-align:center; width: 2px; font-size: 10pt; white-space: normal;'>";
        // echo "<button type='button' class='btn btn-sm btn-primary' onclick='editProduct(\"{$row['product_id']}\")' title='Edit'><i class='fas fa-edit'></i></button> ";
        // echo "<button type='button' class='btn btn-sm btn-danger' onclick='confirmDelete(\"{$row['product_id']}\")' title='Delete'><i class='fas fa-trash'></i></button>&nbsp;";
        echo "<a href='showdetailsproduct.php?product_id={$row['product_id']}' class='btn btn-sm btn-info' title='Details' target='_blank'><i class='fas fa-eye'></i></a>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='10' class='text-center'>No products found</td></tr>";
}

$stmt->close();
$koneklocalhost->close();
?>
