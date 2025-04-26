<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$db = "user_db";

$conn = new mysqli("localhost", "root", "", "user_db");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Debugging
// echo "Cek koneksi...";

// echo "Koneksi sukses!";
?>
