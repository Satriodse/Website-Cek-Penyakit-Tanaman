<?php
ini_set('session.use_cookies', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);

session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: adminloginpage.php"); exit();
}

$username = trim($_POST["username"] ?? '');
$password = $_POST["password"] ?? '';

if (empty($username) || empty($password)) {
    header("Location: adminloginpage.php?error=Username+dan+password+harus+diisi!"); exit();
}

$stmt = mysqli_prepare($conn,
    "SELECT id, nama, username, password, role FROM admin WHERE username = ? AND is_active = 1 LIMIT 1"
);
if (!$stmt) {
    header("Location: adminloginpage.php?error=Kesalahan+database."); exit();
}
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);
$row   = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

if ($row && password_verify($password, $row["password"])) {
    session_regenerate_id(true);
    $_SESSION["admin_id"]       = $row["id"];
    $_SESSION["admin_nama"]     = $row["nama"];
    $_SESSION["admin_username"] = $row["username"];
    $_SESSION["admin_role"]     = $row["role"];

    setcookie("cepa_admin", $row["username"], [
        'expires'  => time() + 7200,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    header("Location: admindashboard.php");
    exit();
}

$pesan = $row ? "Username+atau+password+salah!" : "Akun+tidak+ditemukan+atau+tidak+aktif.";
header("Location: adminloginpage.php?error=" . $pesan);
exit();