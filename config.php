<?php
// Pengaturan Database
define('DB_SERVER', '127.0.0.1');
define('DB_USERNAME', 'final_user');
define('DB_PASSWORD', '123456'); // Masukkan password Anda
define('DB_NAME', 'simprakt_db');

// Membuat koneksi ke database
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
?>