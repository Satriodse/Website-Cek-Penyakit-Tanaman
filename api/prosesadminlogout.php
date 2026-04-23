<?php
session_start();

unset($_SESSION["admin_id"]);
unset($_SESSION["admin_nama"]);
unset($_SESSION["admin_username"]);
unset($_SESSION["admin_role"]);

if (!isset($_SESSION["nama"])) {
    session_destroy();
}

// Redirect ke login page tunggal (bukan adminloginpage)
header("Location: loginpage.php");
exit();
?>