<?php
ini_set('session.use_cookies', 1);
session_start();

// Hapus hanya session admin
unset(
    $_SESSION["admin_id"],
    $_SESSION["admin_nama"],
    $_SESSION["admin_username"],
    $_SESSION["admin_role"]
);

// Hapus cookie admin
setcookie("cepa_admin", '', time() - 3600, '/');

// Jika tidak ada session pengguna tersisa, hancurkan semua
if (!isset($_SESSION["nama"])) {
    if (ini_get("session.use_cookies")) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $p["path"], $p["domain"], $p["secure"], $p["httponly"]
        );
    }
    session_destroy();
}

header("Location: loginpage.php");
exit();