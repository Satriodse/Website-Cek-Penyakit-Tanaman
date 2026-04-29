<?php
session_start();
require_once 'koneksi.php';

// ── Helper: tulis session dulu, baru redirect ─────────────────
function redirect($url) {
    session_write_close();
    if (!headers_sent()) {
        header("Location: " . $url);
        exit();
    }
    echo '<script>window.location.href=' . json_encode($url) . ';</script>';
    echo '<noscript><meta http-equiv="refresh" content="0;url=' . htmlspecialchars($url) . '"></noscript>';
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("registerpage.php");
}

$nama            = trim($_POST["nama"]            ?? '');
$username        = trim($_POST["username"]        ?? '');
$email           = trim($_POST["email"]           ?? '');
$password        = $_POST["password"]        ?? '';
$confirmPassword = $_POST["confirmPassword"] ?? '';

// Validasi field kosong
if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
    redirect("registerpage.php?error=Semua+field+harus+diisi!");
}

// Validasi konfirmasi password
if ($password !== $confirmPassword) {
    redirect("registerpage.php?error=Password+dan+konfirmasi+password+tidak+cocok!");
}

// Cek email sudah terdaftar
$cekEmail = mysqli_prepare($conn, "SELECT id FROM pengguna WHERE email = ?");
mysqli_stmt_bind_param($cekEmail, "s", $email);
mysqli_stmt_execute($cekEmail);
mysqli_stmt_store_result($cekEmail);
if (mysqli_stmt_num_rows($cekEmail) > 0) {
    redirect("registerpage.php?error=Email+sudah+terdaftar!");
}
mysqli_stmt_close($cekEmail);

// Cek username sudah dipakai
$cekUsername = mysqli_prepare($conn, "SELECT id FROM pengguna WHERE username = ?");
mysqli_stmt_bind_param($cekUsername, "s", $username);
mysqli_stmt_execute($cekUsername);
mysqli_stmt_store_result($cekUsername);
if (mysqli_stmt_num_rows($cekUsername) > 0) {
    redirect("registerpage.php?error=Username+sudah+dipakai!");
}
mysqli_stmt_close($cekUsername);

// ── SIMPAN KE DATABASE ────────────────────────────────────────

// 1. Generate ID Unik (Kombinasi tahun, bulan, hari, jam, detik + 3 angka random)
// Hasilnya berupa angka unik 15 digit (contoh: 240502153012789)
$id_pengguna = date('ymdHis') . mt_rand(100, 999);

// 2. Hash password sebelum disimpan ke database
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 3. Masukkan data ke tabel (termasuk ID yang baru saja dibuat)
$stmt = mysqli_prepare($conn,
    "INSERT INTO pengguna (id, nama, username, email, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");

// 4. Bind 5 parameter ("sssss" untuk 5 data: id, nama, username, email, password)
mysqli_stmt_bind_param($stmt, "sssss", $id_pengguna, $nama, $username, $email, $passwordHash);

// 5. Eksekusi dan redirect
if (mysqli_stmt_execute($stmt)) {
    redirect("loginpage.php?success=Registrasi+berhasil!+Silakan+login.");
} else {
    // Jika gagal, bisa jadi karena error database lain
    redirect("registerpage.php?error=Terjadi+kesalahan+saat+menyimpan+data!");
}

mysqli_stmt_close($stmt);