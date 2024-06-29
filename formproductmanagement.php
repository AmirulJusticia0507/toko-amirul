<!-- Form produk -->
<form id="productForm" method="post" enctype="multipart/form-data">
    <input type="hidden" id="product_id" name="product_id" value="<?php echo $product_id; ?>">
    <div class="mb-3">
        <label for="product_name" class="form-label">Product Name</label>
        <input type="text" class="form-control" id="product_name" name="product_name" value="<?php echo $product_name; ?>" required>
    </div>
    <div class="mb-3">
        <label for="description" class="form-label">Description</label>
        <textarea class="form-control" id="description" name="description"><?php echo $description; ?></textarea>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price</label>
        <input type="number" class="form-control" id="price" name="price" value="<?php echo $price; ?>" required>
    </div>
    <div class="mb-3">
        <label for="stock_quantity" class="form-label">Stock Quantity</label>
        <input type="number" class="form-control" id="stock_quantity" name="stock_quantity" value="<?php echo $stock_quantity; ?>" required>
    </div>
    <div class="mb-3">
        <label for="category_id" class="form-label">Category</label>
        <select class="form-control select2" id="category_id" name="category_id" required>
            <!-- Isi opsi dari database -->
            <?php
            $query = "SELECT * FROM categories";
            $result = mysqli_query($koneklocalhost, $query);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='{$row['category_id']}'";
                    if ($category_id == $row['category_id']) echo " selected";
                    echo ">{$row['category_name']}</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="brand_id" class="form-label">Brand</label>
        <select class="form-control select2" id="brand_id" name="brand_id" required>
            <!-- Isi opsi dari database -->
            <?php
            $query = "SELECT * FROM brands";
            $result = mysqli_query($koneklocalhost, $query);
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<option value='{$row['brand_id']}'";
                    if ($brand_id == $row['brand_id']) echo " selected";
                    echo ">{$row['brand_name']}</option>";
                }
            }
            ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="product_image" class="form-label">Product Image</label>
        <input type="file" class="form-control" id="product_image" name="product_image">
        <!-- Tampilkan gambar produk saat ini (jika ada) -->
        <?php if (!empty($image_url)) : ?>
            <img id="product_image_preview" src="<?php echo $image_url; ?>" alt="Product Image" class="mt-2 img-fluid" style="max-width: 200px;">
            <input type="hidden" id="product_image_current" name="product_image_current" value="<?php echo $image_url; ?>">
        <?php else : ?>
            <img id="product_image_preview" src="img/noimage.png" alt="No Image" class="mt-2 img-fluid" style="max-width: 200px;">
            <input type="hidden" id="product_image_current" name="product_image_current" value="">
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label for="status" class="form-label">Status</label>
        <select class="form-control" id="status" name="status" required>
            <option value="1" <?php if ($status == 1) echo "selected"; ?>>Active</option>
            <option value="0" <?php if ($status == 0) echo "selected"; ?>>Inactive</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="weight" class="form-label">Weight (kg)</label>
        <input type="number" step="0.01" class="form-control" id="weight" name="weight" value="<?php echo $weight; ?>">
    </div>
    <div class="mb-3">
        <label for="dimensions" class="form-label">Dimensions (L x W x H cm)</label>
        <input type="text" class="form-control" id="dimensions" name="dimensions" value="<?php echo $dimensions; ?>">
    </div>
    <div class="mb-3">
        <label for="sku" class="form-label">SKU</label>
        <input type="text" class="form-control" id="sku" name="sku" value="<?php echo $sku; ?>">
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Product</button>
    </div>
</form>
