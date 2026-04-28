<?php
$host     = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com";
$port     = 4000;
$user     = "DLDDRzUzmUhedCS.root";
$password = "5Bh4vn4GMldKXjsP";
$database = "cepat";

// Inisialisasi koneksi mysqli dengan SSL (diperlukan oleh TiDB Cloud)
$koneksi = mysqli_init();

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

if (!$real_connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Alias $conn → $koneksi agar semua file yang memakai $conn tetap berjalan
$conn = $koneksi;
?>