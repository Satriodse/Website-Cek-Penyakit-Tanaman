<?php
// Konfigurasi session berbasis cookie
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);   // 2 jam

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

// ── CEK TABEL PENGGUNA ─────────────────────────────────────
$stmt = mysqli_prepare($conn,
    "SELECT id, nama, username, password FROM pengguna WHERE email = ? LIMIT 1"
);
if (!$stmt) {
    header("Location: loginpage.php?error=Kesalahan+database+(pengguna)."); exit();
}
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$hasil    = mysqli_stmt_get_result($stmt);
$pengguna = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if ($pengguna && password_verify($password, $pengguna["password"])) {
    session_regenerate_id(true);
    $_SESSION["id"]       = $pengguna["id"];
    $_SESSION["nama"]     = $pengguna["nama"];
    $_SESSION["username"] = $pengguna["username"];

    // Cookie tambahan (opsional, untuk ketahanan)
    setcookie("cepat_user", $pengguna["username"], [
        'expires'  => time() + 7200,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    header("Location: tugasweb.php");
    exit();
}

// ── CEK TABEL ADMIN ────────────────────────────────────────
$stmt2 = mysqli_prepare($conn,
    "SELECT id, nama, username, password, role FROM admin WHERE email = ? AND is_active = 1 LIMIT 1"
);
if (!$stmt2) {
    header("Location: loginpage.php?error=Kesalahan+database+(admin)."); exit();
}
mysqli_stmt_bind_param($stmt2, "s", $email);
mysqli_stmt_execute($stmt2);
$hasil2 = mysqli_stmt_get_result($stmt2);
$admin  = mysqli_fetch_assoc($hasil2);
mysqli_stmt_close($stmt2);

if ($admin && password_verify($password, $admin["password"])) {
    session_regenerate_id(true);
    $_SESSION["admin_id"]       = $admin["id"];
    $_SESSION["admin_nama"]     = $admin["nama"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_role"]     = $admin["role"];

    setcookie("cepa_admin", $admin["username"], [
        'expires'  => time() + 7200,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    header("Location: admindashboard.php");
    exit();
}

header("Location: loginpage.php?error=Email+atau+password+salah!");
exit();