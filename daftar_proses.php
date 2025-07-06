<?php
// 1. Mulai session dan panggil file konfigurasi
session_start();
require_once 'config.php';

// 2. Lakukan pengecekan keamanan
// Pastikan pengguna sudah login dan perannya adalah 'mahasiswa'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    // Jika tidak, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

// 3. Cek apakah ID praktikum ada di URL
if (!isset($_GET['id_praktikum']) || empty($_GET['id_praktikum'])) {
    // Redirect jika tidak ada ID
    header("Location: katalog_praktikum.php?status=gagal_daftar");
    exit();
}

$id_praktikum = (int)$_GET['id_praktikum'];
$id_mahasiswa = $_SESSION['user_id'];

// 4. Cek agar mahasiswa tidak mendaftar dua kali untuk praktikum yang sama
$stmt_check = $conn->prepare("SELECT id FROM pendaftaran_praktikum WHERE id_mahasiswa = ? AND id_mata_praktikum = ?");
$stmt_check->bind_param("ii", $id_mahasiswa, $id_praktikum);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    // Jika sudah terdaftar, redirect kembali
    header("Location: katalog_praktikum.php?status=sudah_terdaftar");
    exit();
}
$stmt_check->close();

// 5. Siapkan dan eksekusi query INSERT untuk mendaftarkan mahasiswa
$sql = "INSERT INTO pendaftaran_praktikum (id_mahasiswa, id_mata_praktikum, tanggal_daftar) VALUES (?, ?, NOW())";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $id_mahasiswa, $id_praktikum);

    if ($stmt->execute()) {
        // Jika berhasil, redirect ke halaman katalog dengan status sukses
        header("Location: katalog_praktikum.php?status=sukses_daftar");
        exit();
    }
}

// Jika terjadi error atau proses gagal, redirect kembali dengan status gagal
header("Location: katalog_praktikum.php?status=gagal_daftar");
exit();

?>