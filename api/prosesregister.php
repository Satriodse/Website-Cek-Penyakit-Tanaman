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

// Simpan ke database
$passwordHash = password_hash($password, PASSWORD_DEFAULT);
$stmt = mysqli_prepare($conn,
    "INSERT INTO pengguna (id, nama, username, email, password, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $email, $passwordHash);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    redirect("loginpage.php?success=Registrasi+berhasil!+Silakan+login.");
} else {
    mysqli_stmt_close($stmt);
    redirect("registerpage.php?error=Terjadi+kesalahan.+Coba+lagi.");
}