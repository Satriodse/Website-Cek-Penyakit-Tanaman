<?php
// ── proseslogin.php ───────────────────────────────────────────
// PERBAIKAN:
// 1. Hapus ob_start() — tidak diperlukan dan mengganggu pengiriman cookie
// 2. Hapus session_write_close() ganda — cukup sekali di dalam redirect()
// 3. Gunakan prepared statement untuk cek login gagal (lebih aman)

ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.gc_maxlifetime', 7200);
session_start();

require_once 'koneksi.php';

// ── Helper redirect ───────────────────────────────────────────
// session_write_close() dipanggil HANYA di sini, SATU KALI saja
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

// ── Hanya terima POST ─────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirect("loginpage.php");
}

$email    = trim($_POST["email"]    ?? '');
$password = $_POST["password"] ?? '';

if (empty($email) || empty($password)) {
    redirect("loginpage.php?error=Email+dan+password+harus+diisi!");
}

// ── Cek pengguna ──────────────────────────────────────────────
$stmt = mysqli_prepare($conn,
    "SELECT id, nama, username, password FROM pengguna WHERE email = ? LIMIT 1");
if (!$stmt) {
    redirect("loginpage.php?error=" . urlencode("DB error: " . mysqli_error($conn)));
}
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$pengguna = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if ($pengguna && password_verify($password, $pengguna["password"])) {
    $_SESSION["id"]       = (string)$pengguna["id"];
    $_SESSION["nama"]     = $pengguna["nama"];
    $_SESSION["username"] = $pengguna["username"];
    // Cookie sebagai fallback utama di Vercel (session tidak persisten)
    setcookie("cu_id",   (string)$pengguna["id"],      time()+7200, "/", "", false, true);
    setcookie("cu_nama", $pengguna["nama"],             time()+7200, "/", "", false, true);
    setcookie("cu_user", $pengguna["username"],         time()+7200, "/", "", false, true);
    redirect("tugasweb.php");
}

// ── Cek admin ─────────────────────────────────────────────────
$stmt2 = mysqli_prepare($conn,
    "SELECT id, nama, username, password, role FROM admin WHERE email = ? AND is_active = 1 LIMIT 1");
if (!$stmt2) {
    redirect("loginpage.php?error=" . urlencode("DB error admin: " . mysqli_error($conn)));
}
mysqli_stmt_bind_param($stmt2, "s", $email);
mysqli_stmt_execute($stmt2);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2));
mysqli_stmt_close($stmt2);

if ($admin && password_verify($password, $admin["password"])) {
    $_SESSION["admin_id"]       = (string)$admin["id"];
    $_SESSION["admin_nama"]     = $admin["nama"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_role"]     = $admin["role"];
    setcookie("ca_id",   (string)$admin["id"],   time()+7200, "/", "", false, true);
    setcookie("ca_nama", $admin["nama"],          time()+7200, "/", "", false, true);
    setcookie("ca_user", $admin["username"],      time()+7200, "/", "", false, true);
    setcookie("ca_role", $admin["role"],          time()+7200, "/", "", false, true);
    redirect("admindashboard.php");
}

// ── Login gagal — pakai prepared statement (lebih aman) ───────
$ce = mysqli_prepare($conn, "SELECT COUNT(*) c FROM pengguna WHERE email = ?");
mysqli_stmt_bind_param($ce, "s", $email);
mysqli_stmt_execute($ce);
$jp = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($ce))['c'] ?? 0);
mysqli_stmt_close($ce);

$ca = mysqli_prepare($conn, "SELECT COUNT(*) c FROM admin WHERE email = ?");
mysqli_stmt_bind_param($ca, "s", $email);
mysqli_stmt_execute($ca);
$ja = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($ca))['c'] ?? 0);
mysqli_stmt_close($ca);

if ($jp === 0 && $ja === 0) {
    redirect("loginpage.php?error=Email+tidak+terdaftar!");
} else {
    redirect("loginpage.php?error=Password+salah!");
}