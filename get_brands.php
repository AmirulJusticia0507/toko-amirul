<?php
include 'konekke_local.php';

$query = "SELECT * FROM brands";
$result = $koneklocalhost->query($query);

while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['brand_id']}'>{$row['brand_name']}</option>";
}
?>
