<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nama             = trim($_POST["nama"]);
    $username         = trim($_POST["username"]);
    $email            = trim($_POST["email"]);
    $password         = $_POST["password"];
    $confirmPassword  = $_POST["confirmPassword"];

    // Validasi: pastikan semua field terisi
    if (empty($nama) || empty($username) || empty($email) || empty($password) || empty($confirmPassword)) {
        header("Location: registerpage.php?error=Semua+field+harus+diisi!");
        exit();
    }

    // Validasi: password dan konfirmasi password harus sama
    if ($password !== $confirmPassword) {
        header("Location: registerpage.php?error=Password+dan+konfirmasi+password+tidak+cocok!");
        exit();
    }

    // Validasi: cek apakah email sudah terdaftar
    $cekEmail = mysqli_prepare($conn, "SELECT id FROM pengguna WHERE email = ?");
    mysqli_stmt_bind_param($cekEmail, "s", $email);
    mysqli_stmt_execute($cekEmail);
    mysqli_stmt_store_result($cekEmail);

    if (mysqli_stmt_num_rows($cekEmail) > 0) {
        header("Location: registerpage.php?error=Email+sudah+terdaftar!");
        exit();
    }
    mysqli_stmt_close($cekEmail);

    // Validasi: cek apakah username sudah dipakai
    $cekUsername = mysqli_prepare($conn, "SELECT id FROM pengguna WHERE username = ?");
    mysqli_stmt_bind_param($cekUsername, "s", $username);
    mysqli_stmt_execute($cekUsername);
    mysqli_stmt_store_result($cekUsername);

    if (mysqli_stmt_num_rows($cekUsername) > 0) {
        header("Location: registerpage.php?error=Username+sudah+dipakai!");
        exit();
    }
    mysqli_stmt_close($cekUsername);

    // Hash password sebelum disimpan ke database
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Simpan data ke tabel pengguna (created_at diisi eksplisit agar tercatat meski kolom tidak punya DEFAULT)
    $stmt = mysqli_prepare($conn, "INSERT INTO pengguna (nama, username, email, password, created_at) VALUES (?, ?, ?, ?, NOW())");
    mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $email, $passwordHash);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: loginpage.php?success=Registrasi+berhasil!+Silakan+login.");
        exit();
    } else {
        header("Location: registerpage.php?error=Terjadi+kesalahan.+Coba+lagi.");
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {
    // Jika diakses langsung tanpa POST, kembalikan ke halaman register
    header("Location: registerpage.php");
    exit();
}
?>