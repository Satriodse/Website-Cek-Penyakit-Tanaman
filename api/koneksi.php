<?php
$host     = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com";
$port = 4000;
$user     = "DLDDRzUzmUhedCS.root";       // Ganti dengan username MySQL Anda
$password = "5Bh4vn4GMldKXjsP";           // Ganti dengan password MySQL Anda
$database = "cepat";

$conn = mysqli_connect($host, $port, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>