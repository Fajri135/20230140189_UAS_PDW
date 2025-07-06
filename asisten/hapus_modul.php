<?php
// 1. Mulai session dan panggil file konfigurasi
session_start();
require_once '../config.php';

// 2. Lakukan pengecekan keamanan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

// 3. Cek apakah ID modul ada di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_modul = (int)$_GET['id'];

    // 4. Ambil informasi modul (nama file dan id praktikum) sebelum menghapus
    $stmt_select = $conn->prepare("SELECT file_materi, id_mata_praktikum FROM modul WHERE id = ?");
    $stmt_select->bind_param("i", $id_modul);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($modul = $result->fetch_assoc()) {
        $file_materi = $modul['file_materi'];
        $id_praktikum = $modul['id_mata_praktikum'];

        // 5. Hapus file fisik dari server jika ada
        if (!empty($file_materi) && file_exists("../uploads/materi/" . $file_materi)) {
            unlink("../uploads/materi/" . $file_materi);
        }

        // 6. Siapkan dan eksekusi query DELETE
        $stmt_delete = $conn->prepare("DELETE FROM modul WHERE id = ?");
        $stmt_delete->bind_param("i", $id_modul);
        
        if ($stmt_delete->execute()) {
            // Jika berhasil, redirect ke halaman manage_modul dengan id praktikum yang benar
            header("Location: manage_modul.php?id_praktikum=" . $id_praktikum . "&status=sukses");
            exit();
        }
    }
}

// Jika terjadi error atau proses gagal, redirect kembali
header("Location: manage_modul.php?status=gagal");
exit();
?>