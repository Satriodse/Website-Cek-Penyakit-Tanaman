<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: loginpage.php"); 
    exit();
}

$email    = trim($_POST["email"]    ?? '');
$password = $_POST["password"] ?? '';
$remember = isset($_POST["remember"]) ? "true" : "";

if (empty($email) || empty($password)) {
    header("Location: loginpage.php?error=Email+dan+password+harus+diisi!&email=" . urlencode($email) . "&remember=" . urlencode($remember)); 
    exit();
}

// STEP 1: Cek tabel pengguna
$stmt = mysqli_prepare($conn, "SELECT id, nama, username, password FROM pengguna WHERE email = ?");
if (!$stmt) {
    header("Location: loginpage.php?error=Kesalahan+sistem+(prepare+gagal).&email=" . urlencode($email) . "&remember=" . urlencode($remember));
    exit();
}
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$pengguna = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if ($pengguna && password_verify($password, $pengguna["password"])) {
    $_SESSION["id"]       = $pengguna["id"];
    $_SESSION["nama"]     = $pengguna["nama"];
    $_SESSION["username"] = $pengguna["username"];
    $_SESSION["user_type"] = "pengguna";
    
    // Catat di tabel session_logs
    recordSessionLog($conn, $pengguna["id"], "pengguna", $_SERVER['REMOTE_ADDR'], "login", "success");
    
    // Remember me functionality
    if ($remember) {
        setcookie("remember_email", $email, time() + (30 * 24 * 60 * 60), "/");
    }
    
    header("Location: tugasweb.php");
    exit();
}

// STEP 2: Cek tabel admin
$stmt2 = mysqli_prepare($conn, "SELECT id, nama, username, password, role FROM admin WHERE email = ? AND is_active = 1");
if (!$stmt2) {
    header("Location: loginpage.php?error=Kesalahan+sistem+(prepare+gagal).&email=" . urlencode($email) . "&remember=" . urlencode($remember));
    exit();
}
mysqli_stmt_bind_param($stmt2, "s", $email);
mysqli_stmt_execute($stmt2);
$result2 = mysqli_stmt_get_result($stmt2);
$admin   = mysqli_fetch_assoc($result2);
mysqli_stmt_close($stmt2);

if ($admin && password_verify($password, $admin["password"])) {
    $_SESSION["admin_id"]       = $admin["id"];
    $_SESSION["admin_nama"]     = $admin["nama"];
    $_SESSION["admin_username"] = $admin["username"];
    $_SESSION["admin_role"]     = $admin["role"];
    $_SESSION["user_type"]      = "admin";
    
    // Catat di tabel session_logs
    recordSessionLog($conn, $admin["id"], "admin", $_SERVER['REMOTE_ADDR'], "login", "success");
    
    // Remember me functionality
    if ($remember) {
        setcookie("remember_email", $email, time() + (30 * 24 * 60 * 60), "/");
    }
    
    header("Location: admindashboard.php");
    exit();
}

// Login gagal - catat attempted login
recordSessionLog($conn, null, null, $_SERVER['REMOTE_ADDR'], "login", "failed", $email);

header("Location: loginpage.php?error=Email+atau+password+salah!&email=" . urlencode($email) . "&remember=" . urlencode($remember));
exit();

/**
 * Fungsi untuk mencatat session login
 */
function recordSessionLog($conn, $user_id, $user_type, $ip_address, $action, $status, $attempted_email = null) {
    // Cek apakah tabel session_logs ada
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'session_logs'");
    if (mysqli_num_rows($check_table) == 0) {
        // Tabel tidak ada, skip logging
        return;
    }
    
    $query = "INSERT INTO session_logs (user_id, user_type, ip_address, action, status, attempted_email, login_time, last_activity) 
              VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
    
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isssss", $user_id, $user_type, $ip_address, $action, $status, $attempted_email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}
?>
