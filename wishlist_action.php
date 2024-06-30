<?php
session_start();
include 'konekke_local.php';

if (!isset($_SESSION['userid'])) {
    echo json_encode(['message' => 'Login required']); // Return error message if not logged in
    exit;
}

$user_id = $_SESSION['userid'];
$product_id = $_POST['product_id'];
$action = $_POST['action'];

if ($action === 'add') {
    $check_wishlist_query = "SELECT * FROM wishlist WHERE product_id = ? AND user_id = ?";
    $check_wishlist_stmt = $koneklocalhost->prepare($check_wishlist_query);
    $check_wishlist_stmt->bind_param("ii", $product_id, $user_id);
    $check_wishlist_stmt->execute();
    $check_wishlist_result = $check_wishlist_stmt->get_result();

    if ($check_wishlist_result->num_rows === 0) {
        $insert_wishlist_query = "INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)";
        $insert_wishlist_stmt = $koneklocalhost->prepare($insert_wishlist_query);
        $insert_wishlist_stmt->bind_param("ii", $user_id, $product_id);

        if ($insert_wishlist_stmt->execute()) {
            echo json_encode(['message' => 'Product added to wishlist']);
        } else {
            echo json_encode(['message' => 'Failed to add product to wishlist']);
        }

        $insert_wishlist_stmt->close();
    } else {
        echo json_encode(['message' => 'Product already in wishlist']);
    }

    $check_wishlist_stmt->close();
} elseif ($action === 'remove') {
    $delete_wishlist_query = "DELETE FROM wishlist WHERE product_id = ? AND user_id = ?";
    $delete_wishlist_stmt = $koneklocalhost->prepare($delete_wishlist_query);
    $delete_wishlist_stmt->bind_param("ii", $product_id, $user_id);

    if ($delete_wishlist_stmt->execute()) {
        echo json_encode(['message' => 'Product removed from wishlist']);
    } else {
        echo json_encode(['message' => 'Failed to remove product from wishlist']);
    }

    $delete_wishlist_stmt->close();
} else {
    echo json_encode(['message' => 'Invalid action']);
}

$koneklocalhost->close();
?>
