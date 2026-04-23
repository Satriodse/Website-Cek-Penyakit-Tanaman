<?php
session_start();

// Hapus semua data session yang tersimpan
$_SESSION = array();
session_destroy();

// Arahkan kembali ke halaman login
header("Location: loginpage.php");
exit();
?>