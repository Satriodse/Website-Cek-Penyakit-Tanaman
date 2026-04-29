<?php
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 7200);
session_start();

require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: loginpage.php"); exit();
}

$email    = trim($_POST["email"]    ?? '');
$password = $_POST["password"] ?? '';

if (empty($email) || empty($password)) {
    header("Location: loginpage.php?error=Email+dan+password+harus+diisi!"); exit();
}

// Cek pengguna
$stmt = mysqli_prepare($conn,
    "SELECT id, nama, username, password FROM pengguna WHERE email = ? LIMIT 1");
if (!$stmt) {
    header("Location: loginpage.php?error=" . urlencode("DB error: " . mysqli_error($conn))); exit();
}
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$pengguna = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($pengguna && password_verify($password, $pengguna["password"])) {
    session_regenerate_id(true);
    $_SESSION["id"]       = $pengguna["id"];
    $_SESSION["nama"]     = $pengguna["nama"];
    $_SESSION["username"] = $pengguna["username"];
    setcookie("cu_id",   (string)$pengguna["id"],       time()+7200, "/", "", false, true);
    setcookie("cu_nama", (string)$pengguna["nama"],      time()+7200, "/", "", false, true);
    setcookie("cu_user", (string)$pengguna["username"],  time()+7200, "/", "", false, true);
    header("Location: tugasweb.php"); exit();
}

// Cek admin
$stmt2 = mysqli_prepare($conn,
    "SELECT id, nama, username, password, role FROM admin WHERE email = ? AND is_active = 1 LIMIT 1");
if (!$stmt2) {
    header("Location: loginpage.php?error=" . urlencode("DB error admin: " . mysqli_error($conn))); exit();
}
mysqli_stmt_bind_param($stmt2, "s", $email);
mysqli_stmt_execute($stmt2);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
mysqli_stmt_close($stmt2);

if ($admin && password_verify($password, $admin["password"])) {
    session_regenerate_id(true);
    $_SESSION["admin_id"]       = $admin["id"];
    $_SESSION["admin_nama"]     = $admin["nama"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_role"]     = $admin["role"];
    setcookie("ca_id",   (string)$admin["id"],       time()+7200, "/", "", false, true);
    setcookie("ca_nama", (string)$admin["nama"],      time()+7200, "/", "", false, true);
    setcookie("ca_user", (string)$admin["username"],  time()+7200, "/", "", false, true);
    setcookie("ca_role", (string)$admin["role"],      time()+7200, "/", "", false, true);
    header("Location: admindashboard.php"); exit();
}

// Tidak cocok — bedakan pesan error
$ce = mysqli_query($conn, "SELECT COUNT(*) c FROM pengguna WHERE email='" . mysqli_real_escape_string($conn,$email) . "'");
$ca = mysqli_query($conn, "SELECT COUNT(*) c FROM admin WHERE email='" . mysqli_real_escape_string($conn,$email) . "'");
$jp = (int)(mysqli_fetch_assoc($ce)['c'] ?? 0);
$ja = (int)(mysqli_fetch_assoc($ca)['c'] ?? 0);
if ($jp == 0 && $ja == 0) {
    header("Location: loginpage.php?error=Email+tidak+terdaftar!");
} else {
    header("Location: loginpage.php?error=Password+salah!");
}
exit();