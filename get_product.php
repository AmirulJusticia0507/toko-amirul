<?php
// Include your database connection file
include 'konekke_local.php';

// Check if product_id is sent via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    // Sanitize the input (optional but recommended)
    $product_id = intval($_POST['product_id']);

    // Prepare SQL statement to fetch product details
    $query = "SELECT * FROM products WHERE product_id = ?";
    $stmt = $koneklocalhost->prepare($query);

    // Bind product_id parameter
    $stmt->bind_param("i", $product_id);

    // Execute the statement
    $stmt->execute();

    // Get result set
    $result = $stmt->get_result();

    // Check if product exists
    if ($result->num_rows > 0) {
        // Fetch product details
        $row = $result->fetch_assoc();
        // Return product details as JSON response
        echo json_encode($row);
    } else {
        // Product not found error
        echo json_encode(['error' => 'Product not found']);
    }
} else {
    // Invalid request error
    echo json_encode(['error' => 'Invalid request']);
}

// Close the statement
$stmt->close();

// Close the database connection
$koneklocalhost->close();
?>
