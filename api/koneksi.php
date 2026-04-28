<?php
$host     = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com";
$port     = (int)4000;          // Pastikan integer dengan casting explicit
$user     = "DLDDRzUzmUhedCS.root";
$password = "5Bh4vn4GMldKXjsP";
$database = "cepat";

// ── Inisialisasi dengan SSL (wajib untuk TiDB Cloud) ───────
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init gagal.");
}

// Aktifkan SSL
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Sambungkan dengan flag SSL
$connected = mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    (int)$port,  // Cast ke int untuk memastikan tipe yang benar
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$connected) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Set charset
mysqli_set_charset($conn, "utf8mb4");

// Alias $koneksi agar kompatibel dengan kode lama yang pakai $koneksi
$koneksi = $conn;
?>
