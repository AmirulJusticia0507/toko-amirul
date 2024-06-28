<?php
$server = "localhost";
// $username = "root";
$username = "root";
$password = "";
$database = "db_toko_amirul";

// Buat koneksi ke server
$koneklocalhost = new mysqli($server, $username, $password, $database);

// Periksa koneksi ke server
if ($koneklocalhost->connect_error) {
    $hasil['STATUS'] = "000199";
    die(json_encode($hasil));
}
?>