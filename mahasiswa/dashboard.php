<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard Mahasiswa';
$activePage = 'dashboard'; 

// 2. Panggil Header Mahasiswa dan Konfigurasi
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// 3. Ambil ID dan nama mahasiswa yang sedang login
$id_mahasiswa = $_SESSION['user_id'];
$nama_mahasiswa = $_SESSION['nama'];

// --- Logika untuk mengambil data statistik ---

// Menghitung jumlah praktikum yang diikuti
$stmt_diikuti = $conn->prepare("SELECT COUNT(id) as total FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
$stmt_diikuti->bind_param("i", $id_mahasiswa);
$stmt_diikuti->execute();
$praktikum_diikuti = $stmt_diikuti->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_diikuti->close();

// Menghitung tugas yang sudah dinilai (selesai)
$stmt_selesai = $conn->prepare("SELECT COUNT(id) as total FROM laporan_mahasiswa WHERE id_mahasiswa = ? AND status = 'dinilai'");
$stmt_selesai->bind_param("i", $id_mahasiswa);
$stmt_selesai->execute();
$tugas_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_selesai->close();

// Menghitung tugas yang menunggu dinilai
$stmt_menunggu = $conn->prepare("SELECT COUNT(id) as total FROM laporan_mahasiswa WHERE id_mahasiswa = ? AND status = 'dikumpulkan'");
$stmt_menunggu->bind_param("i", $id_mahasiswa);
$stmt_menunggu->execute();
$tugas_menunggu = $stmt_menunggu->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_menunggu->close();

// --- Logika untuk Notifikasi Terbaru ---
// (Ini adalah contoh sederhana, bisa dikembangkan lebih lanjut)
$notifikasi = [];
// Notifikasi 1: Nilai terbaru yang diberikan
$stmt_notif1 = $conn->prepare("SELECT m.judul_modul FROM laporan_mahasiswa l JOIN modul m ON l.id_modul = m.id WHERE l.id_mahasiswa = ? AND l.status = 'dinilai' ORDER BY l.tanggal_kumpul DESC LIMIT 1");
$stmt_notif1->bind_param("i", $id_mahasiswa);
$stmt_notif1->execute();
$res1 = $stmt_notif1->get_result();
if ($res1->num_rows > 0) {
    $data = $res1->fetch_assoc();
    $notifikasi[] = ['tipe' => 'nilai', 'pesan' => 'Nilai untuk <strong>' . htmlspecialchars($data['judul_modul']) . '</strong> telah diberikan.'];
}
$stmt_notif1->close();

// Notifikasi 2: Pendaftaran praktikum terakhir
$stmt_notif2 = $conn->prepare("SELECT mp.nama_matkul FROM pendaftaran_praktikum pp JOIN mata_praktikum mp ON pp.id_mata_praktikum = mp.id WHERE pp.id_mahasiswa = ? ORDER BY pp.tanggal_daftar DESC LIMIT 1");
$stmt_notif2->bind_param("i", $id_mahasiswa);
$stmt_notif2->execute();
$res2 = $stmt_notif2->get_result();
if ($res2->num_rows > 0) {
    $data = $res2->fetch_assoc();
    $notifikasi[] = ['tipe' => 'sukses', 'pesan' => 'Anda berhasil mendaftar pada mata praktikum <strong>' . htmlspecialchars($data['nama_matkul']) . '</strong>.'];
}
$stmt_notif2->close();
?>

<!-- Konten Utama -->
<div class="bg-blue-500 text-white p-8 rounded-lg shadow-lg mb-8">
    <h1 class="text-3xl font-bold">Selamat Datang Kembali, <?php echo htmlspecialchars(strtok($nama_mahasiswa, " ")); ?>!</h1>
    <p class="mt-2">Terus semangat dalam menyelesaikan semua modul praktikummu.</p>
</div>

<!-- Kartu Statistik -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <p class="text-4xl font-bold text-blue-600"><?php echo $praktikum_diikuti; ?></p>
        <p class="text-gray-500 mt-2">Praktikum Diikuti</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <p class="text-4xl font-bold text-green-600"><?php echo $tugas_selesai; ?></p>
        <p class="text-gray-500 mt-2">Tugas Selesai</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md text-center">
        <p class="text-4xl font-bold text-yellow-600"><?php echo $tugas_menunggu; ?></p>
        <p class="text-gray-500 mt-2">Tugas Menunggu</p>
    </div>
</div>

<!-- Notifikasi Terbaru -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Notifikasi Terbaru</h3>
    <div class="space-y-4">
        <?php if (!empty($notifikasi)): ?>
            <?php foreach ($notifikasi as $notif): ?>
                <div class="flex items-start">
                    <?php if ($notif['tipe'] == 'nilai'): ?>
                        <span class="mr-3 mt-1 text-yellow-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        </span>
                    <?php elseif ($notif['tipe'] == 'sukses'): ?>
                        <span class="mr-3 mt-1 text-green-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </span>
                    <?php endif; ?>
                    <p class="text-gray-700"><?php echo $notif['pesan']; ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500">Tidak ada notifikasi baru untuk saat ini.</p>
        <?php endif; ?>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>
