<?php
ini_set('session.use_cookies', 1);
session_start();

// Hapus session
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $p["path"], $p["domain"], $p["secure"], $p["httponly"]
    );
}
session_destroy();

// Hapus cookie tambahan
setcookie("cepa_user",  '', time() - 3600, '/');
setcookie("cepa_admin", '', time() - 3600, '/');

header("Location: loginpage.php");
exit();