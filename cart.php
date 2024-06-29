<?php
// Include your database connection and session management here
include 'konekke_local.php';

// Check if user is authenticated
session_start();
if (!isset($_SESSION['userid'])) {
    header('Location: login.php');
    exit;
}

// Retrieve user_id from session
$user_id = $_SESSION['userid'];

// Handle Remove from Cart action
if (isset($_POST['remove_from_cart'])) {
    $cart_item_id = $_POST['cart_item_id'];

    // Delete the cart item from the database
    $delete_cart_query = "DELETE FROM cart_items WHERE cart_item_id = ? AND user_id = ?";
    $delete_stmt = $koneklocalhost->prepare($delete_cart_query);
    $delete_stmt->bind_param("ii", $cart_item_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Redirect to cart page with a success message
    header("Location: cart.php?message=Product removed from cart successfully");
    exit;
}

// Handle Update Quantity action
if (isset($_POST['update_quantity'])) {
    $cart_item_id = $_POST['cart_item_id'];
    $quantity = intval($_POST['quantity']);

    // Update the quantity in the database
    $update_cart_query = "UPDATE cart_items SET quantity = ? WHERE cart_item_id = ? AND user_id = ?";
    $update_stmt = $koneklocalhost->prepare($update_cart_query);
    $update_stmt->bind_param("iii", $quantity, $cart_item_id, $user_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect to cart page with a success message
    header("Location: cart.php?message=Quantity updated successfully");
    exit;
}

// Retrieve cart items for the user with additional product details
$query = "SELECT ci.cart_item_id, ci.quantity, p.product_name, p.price, p.product_image, p.status, b.brand_name, c.category_name
          FROM cart_items ci
          LEFT JOIN products p ON ci.product_id = p.product_id
          LEFT JOIN brands b ON p.brand_id = b.brand_id
          LEFT JOIN categories c ON p.category_id = c.category_id
          WHERE ci.user_id = ?";
$stmt = $koneklocalhost->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Toko Amirul</title>
    <!-- Tambahkan link Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/css/bootstrap.min.css">
    <!-- Tambahkan link AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/css/adminlte.min.css">
    <!-- Tambahkan link Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">
    <link rel="icon" href="img/amirulshop.png" type="image/png">
</head>
<body>
    <div class="container">
        
        <h1 class="mt-4 mb-4">Keranjang</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows > 0): ?>
            <table id="cartTable" class="display table table-bordered table-striped table-hover responsive nowrap" style="width:100%">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="uploads/product/<?php echo $row['product_image']; ?>" alt="Gambar Produk" width="100"></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                        <td>Rp. <?php echo number_format($row['price'], 0, ',', '.'); ?></td>
                        <td>
                            <form action="cart.php" method="post" class="d-inline">
                                <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                                <input type="number" name="quantity" value="<?php echo $row['quantity']; ?>" class="form-control" style="width: 80px;">
                                <button type="submit" name="update_quantity" class="btn btn-primary mt-2">Update</button>
                            </form>
                        </td>
                        <td>Rp. <?php echo number_format($row['price'] * $row['quantity'], 0, ',', '.'); ?></td>
                        <td>
                            <form action="cart.php" method="post" class="d-inline">
                                <input type="hidden" name="cart_item_id" value="<?php echo $row['cart_item_id']; ?>">
                                <button type="submit" name="remove_from_cart" class="btn btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Your cart is empty.</p>
        <?php endif; ?>
        <?php
        // Close statement and connection
        $stmt->close();
        $koneklocalhost->close();
        ?>
        <div class="card-footer">
            <a href="checkout.php" class="btn btn-success"><i class="fas fa-shopping-cart"></i> Proceed to Checkout</a>
        </div>
    </div>

    <!-- Tambahkan script Bootstrap dan AdminLTE -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/admin-lte/3.1.0/js/adminlte.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
</body>
</html>
