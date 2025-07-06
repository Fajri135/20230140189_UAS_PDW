<?php
// 1. Mulai session dan panggil file konfigurasi
session_start();
require_once '../config.php';

// 2. Lakukan pengecekan keamanan
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

// 3. Pastikan request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    if (!isset($_POST['id_modul']) || !isset($_POST['id_praktikum']) || !isset($_FILES['file_laporan'])) {
        header("Location: my_courses.php"); // Redirect jika data tidak lengkap
        exit();
    }

    $id_modul = (int)$_POST['id_modul'];
    $id_praktikum = (int)$_POST['id_praktikum']; // Untuk redirect kembali
    $id_mahasiswa = $_SESSION['user_id'];
    $file_laporan = $_FILES['file_laporan'];

    // Cek jika ada error pada file upload
    if ($file_laporan['error'] !== UPLOAD_ERR_OK) {
        header("Location: detail_praktikum.php?id=" . $id_praktikum . "&status=gagal_upload");
        exit();
    }

    // Proses upload file
    $target_dir = "../uploads/laporan/";
    // Buat nama file yang unik untuk menghindari tumpang tindih
    $nama_file_unik = time() . '_' . uniqid() . '_' . basename($file_laporan["name"]);
    $target_file = $target_dir . $nama_file_unik;
    $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Izinkan format file tertentu (misal: pdf, docx, zip)
    $allowed_types = ['pdf', 'docx', 'doc', 'zip', 'rar'];
    if (!in_array($file_type, $allowed_types)) {
        header("Location: detail_praktikum.php?id=" . $id_praktikum . "&status=gagal_upload_tipe");
        exit();
    }

    // Pindahkan file ke direktori uploads
    if (move_uploaded_file($file_laporan["tmp_name"], $target_file)) {
        // Jika upload berhasil, simpan data ke database
        $sql = "INSERT INTO laporan_mahasiswa (id_modul, id_mahasiswa, file_laporan, tanggal_kumpul, status) VALUES (?, ?, ?, NOW(), 'dikumpulkan')";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("iis", $id_modul, $id_mahasiswa, $nama_file_unik);
            
            if ($stmt->execute()) {
                // Berhasil, redirect ke halaman detail dengan status sukses
                header("Location: detail_praktikum.php?id=" . $id_praktikum . "&status=sukses_upload");
                exit();
            }
            $stmt->close();
        }
    }
}

// Jika terjadi error di mana pun, redirect dengan status gagal
$redirect_id = isset($id_praktikum) ? $id_praktikum : '';
header("Location: detail_praktikum.php?id=" . $redirect_id . "&status=gagal_upload");
exit();

?>