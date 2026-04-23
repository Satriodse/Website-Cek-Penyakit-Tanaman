<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: loginpage.php"); exit();
}

$email    = trim($_POST["email"]    ?? '');
$password = $_POST["password"] ?? '';

if (empty($email) || empty($password)) {
    header("Location: loginpage.php?error=Email+dan+password+harus+diisi!");
    exit();
}

// ── STEP 1: Cek tabel pengguna (login by email) ────────────
$stmt = mysqli_prepare($conn, "SELECT id, nama, username, password FROM pengguna WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$pengguna = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($pengguna && password_verify($password, $pengguna["password"])) {
    // ── Login sebagai PENGGUNA berhasil ──────────────────
    $_SESSION["id"]       = $pengguna["id"];
    $_SESSION["nama"]     = $pengguna["nama"];
    $_SESSION["username"] = $pengguna["username"];

    header("Location: tugasweb.php");
    exit();
}

// ── STEP 2: Cek tabel admin (login by email) ───────────────
$stmt2 = mysqli_prepare($conn, "SELECT id, nama, username, password, role FROM admin WHERE email = ? AND is_active = 1");
mysqli_stmt_bind_param($stmt2, "s", $email);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$admin = mysqli_fetch_assoc($result2);
mysqli_stmt_close($stmt2);

if ($admin && password_verify($password, $admin["password"])) {
    // ── Login sebagai ADMIN berhasil ─────────────────────
    $_SESSION["admin_id"]       = $admin["id"];
    $_SESSION["admin_nama"]     = $admin["nama"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_role"]     = $admin["role"];

    header("Location: admindashboard.php");
    exit();
}

// ── Tidak cocok di keduanya ────────────────────────────────
header("Location: loginpage.php?error=Email+atau+password+salah!");
exit();
?>