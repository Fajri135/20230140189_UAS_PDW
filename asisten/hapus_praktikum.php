<?php
// 1. Mulai session secara manual di sini
session_start();

// 2. Panggil file konfigurasi untuk koneksi database
require_once '../config.php';

// 3. Lakukan pengecekan keamanan secara langsung
// Pastikan pengguna sudah login dan perannya adalah 'asisten'
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    // Jika tidak memenuhi, arahkan ke halaman login
    header("Location: ../login.php");
    exit();
}

// 4. Cek apakah ID ada di URL dan tidak kosong
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // 5. Siapkan query DELETE dengan prepared statement untuk keamanan
    // Catatan: Proses hapus bisa gagal jika ada data di tabel lain (misal: modul)
    // yang terhubung dengan mata praktikum ini (Foreign Key Constraint).
    $sql = "DELETE FROM mata_praktikum WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);

        // 6. Eksekusi query
        if ($stmt->execute()) {
            // Jika berhasil, redirect ke halaman utama dengan status sukses
            header("Location: manage_praktikum.php?status=sukses");
            exit();
        } else {
            // Jika gagal, redirect dengan status gagal
            header("Location: manage_praktikum.php?status=gagal");
            exit();
        }
    } else {
        // Jika query gagal disiapkan
        header("Location: manage_praktikum.php?status=gagal");
        exit();
    }
} else {
    // Jika tidak ada ID di URL, redirect ke halaman utama
    header("Location: manage_praktikum.php");
    exit();
}

// Baris $conn->close() dihapus karena tidak akan pernah tercapai
?>