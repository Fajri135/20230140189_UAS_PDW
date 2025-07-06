<?php
// 1. Mulai session dan panggil file konfigurasi
session_start();
require_once '../config.php';

// 2. Lakukan pengecekan keamanan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

// 3. Cek apakah ID pengguna ada di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_pengguna_hapus = (int)$_GET['id'];
    $id_asisten_login = $_SESSION['user_id'];

    // 4. PENTING: Cek agar asisten tidak bisa menghapus akunnya sendiri
    if ($id_pengguna_hapus == $id_asisten_login) {
        // Redirect dengan status gagal jika mencoba menghapus diri sendiri
        header("Location: manage_pengguna.php?status=gagal_hapus_diri");
        exit();
    }

    // 5. Siapkan dan eksekusi query DELETE
    // Catatan: Sebaiknya database Anda diatur dengan ON DELETE CASCADE
    // untuk menghapus data terkait secara otomatis (laporan, nilai, dll).
    // Jika tidak, data tersebut akan menjadi data yatim (orphaned data).
    $sql = "DELETE FROM users WHERE id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id_pengguna_hapus);

        if ($stmt->execute()) {
            // Jika berhasil, redirect ke halaman utama dengan status sukses
            header("Location: manage_pengguna.php?status=sukses");
            exit();
        }
    }
}

// Jika terjadi error atau proses gagal, redirect kembali
header("Location: manage_pengguna.php?status=gagal");
exit();
?>