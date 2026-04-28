<?php
$host     = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com";
$port     = 4000;
$user     = "DLDDRzUzmUhedCS.root";       // Ganti dengan username MySQL Anda
$password = "5Bh4vn4GMldKXjsP";           // Ganti dengan password MySQL Anda
$database = "cepat";

// Inisialisasi mysqli
$koneksi = mysqli_init();

$conn = mysqli_connect($host, $port, $user, $password, $database);

// Melakukan koneksi
$real_connect = mysqli_real_connect(
    $koneksi, 
    $host, 
    $user, 
    $password, 
    $database, 
    $port, 
    NULL, 
    MYSQLI_CLIENT_SSL
);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>