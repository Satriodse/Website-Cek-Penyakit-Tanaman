<?php
// ============================================================
// KONEKSI DATABASE – TiDB Cloud
// TIDAK menggunakan mysqli_connect() karena port harus int
// Menggunakan mysqli_real_connect() dengan SSL
// ============================================================

$host     = "gateway01.ap-southeast-1.prod.alicloud.tidbcloud.com";
$port     = 4000;                      // integer, BUKAN string
$user     = "39cVeHTYABy9NSP.root";
$password = "wO782LVSJvOSjtoB";
$database = "cepat";

// Step 1: Init object mysqli
$conn = mysqli_init();
if (!$conn) {
    die(json_encode(["error" => "mysqli_init() gagal."]));
}

// Step 2: Set opsi SSL sebelum connect
mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

// Step 3: Sambungkan dengan real_connect (support port integer + SSL)
$real_connect = mysqli_real_connect(
    $conn,
    $host,
    $user,
    $password,
    $database,
    $port,          // integer ✓
    NULL,
    MYSQLI_CLIENT_SSL
);

if (!$real_connect) {
    die("Koneksi ke TiDB Cloud gagal: " . mysqli_connect_error());
}

// Step 4: Set charset
mysqli_set_charset($conn, "utf8mb4");

// Alias untuk kompatibilitas kode lama yang pakai $koneksi
$koneksi = $conn;