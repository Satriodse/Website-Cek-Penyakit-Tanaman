<?php
session_start();
require_once 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        header("Location: adminloginpage.php?error=Username+dan+password+harus+diisi!");
        exit();
    }

    // Cari admin berdasarkan username
    $stmt = mysqli_prepare($conn, "SELECT id, nama, username, password, role FROM admin WHERE username = ? AND is_active = 1");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row["password"])) {
            $_SESSION["admin_id"]       = $row["id"];
            $_SESSION["admin_nama"]     = $row["nama"];
            $_SESSION["admin_username"] = $row["username"];
            $_SESSION["admin_role"]     = $row["role"]; 

            header("Location: admindashboard.php");
            exit();
        } else {
            header("Location: adminloginpage.php?error=Username+atau+password+salah!");
            exit();
        }
    } else {
        header("Location: adminloginpage.php?error=Username+atau+password+salah!+Atau+akun+tidak+aktif.");
        exit();
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

} else {
    header("Location: adminloginpage.php");
    exit();
}
?>