<?php
include 'konekke_local.php';

$query = "SELECT * FROM categories";
$result = $koneklocalhost->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
}
?>
