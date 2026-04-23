<?php
$host     = "localhost";
$user     = "root";       // Ganti dengan username MySQL Anda
$password = "";           // Ganti dengan password MySQL Anda
$database = "cepat";

$conn = mysqli_connect($host, $user, $password, $database);

if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>