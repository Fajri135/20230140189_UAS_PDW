<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Dashboard';
$activePage = 'dashboard';

// 2. Panggil Header (yang akan memulai session dan melakukan cek login)
require_once 'templates/header.php';
// Panggil file konfigurasi untuk mendapatkan koneksi $conn
require_once '../config.php';

// =================================================================
// BAGIAN BARU: LOGIKA UNTUK MENGAMBIL DATA DINAMIS DARI DATABASE
// =================================================================

// Ambil ID asisten yang sedang login dari session
$id_asisten = $_SESSION['user_id'];

// --- Query untuk Kartu Statistik ---

// Menghitung Total Modul yang diajarkan oleh asisten ini
$stmt_modul = $conn->prepare("SELECT COUNT(m.id) as total_modul FROM modul m JOIN mata_praktikum mp ON m.id_mata_praktikum = mp.id WHERE mp.id_asisten_pengampu = ?");
$stmt_modul->bind_param("i", $id_asisten);
$stmt_modul->execute();
$total_modul = $stmt_modul->get_result()->fetch_assoc()['total_modul'] ?? 0;
$stmt_modul->close();

// Menghitung Total Laporan Masuk untuk praktikum yang diampu asisten ini
$stmt_laporan = $conn->prepare("SELECT COUNT(l.id) as total_laporan FROM laporan_mahasiswa l JOIN modul m ON l.id_modul = m.id JOIN mata_praktikum mp ON m.id_mata_praktikum = mp.id WHERE mp.id_asisten_pengampu = ?");
$stmt_laporan->bind_param("i", $id_asisten);
$stmt_laporan->execute();
$total_laporan = $stmt_laporan->get_result()->fetch_assoc()['total_laporan'] ?? 0;
$stmt_laporan->close();

// Menghitung Total Laporan yang Belum Dinilai
$stmt_belum_dinilai = $conn->prepare("SELECT COUNT(l.id) as total_belum_dinilai FROM laporan_mahasiswa l JOIN modul m ON l.id_modul = m.id JOIN mata_praktikum mp ON m.id_mata_praktikum = mp.id WHERE mp.id_asisten_pengampu = ? AND l.status = 'dikumpulkan'");
$stmt_belum_dinilai->bind_param("i", $id_asisten);
$stmt_belum_dinilai->execute();
$total_belum_dinilai = $stmt_belum_dinilai->get_result()->fetch_assoc()['total_belum_dinilai'] ?? 0;
$stmt_belum_dinilai->close();

// --- Query untuk Aktivitas Terbaru ---
$stmt_aktivitas = $conn->prepare(
    "SELECT u.nama, m.judul_modul, l.tanggal_kumpul 
     FROM laporan_mahasiswa l 
     JOIN users u ON l.id_mahasiswa = u.id 
     JOIN modul m ON l.id_modul = m.id 
     JOIN mata_praktikum mp ON m.id_mata_praktikum = mp.id 
     WHERE mp.id_asisten_pengampu = ? 
     ORDER BY l.tanggal_kumpul DESC 
     LIMIT 5"
);
$stmt_aktivitas->bind_param("i", $id_asisten);
$stmt_aktivitas->execute();
$aktivitas_terbaru = $stmt_aktivitas->get_result();
$stmt_aktivitas->close();

$conn->close();
?>

<!-- Bagian HTML untuk menampilkan konten -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    
    <!-- Kartu 1: Total Modul Diajarkan -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <!-- DIUBAH: Angka statis diganti dengan variabel PHP -->
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_modul; ?></p>
        </div>
    </div>

    <!-- Kartu 2: Total Laporan Masuk -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <!-- DIUBAH: Angka statis diganti dengan variabel PHP -->
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>

    <!-- Kartu 3: Laporan Belum Dinilai -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <!-- DIUBAH: Angka statis diganti dengan variabel PHP -->
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_belum_dinilai; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    
    <!-- DIUBAH: Daftar aktivitas statis diganti dengan perulangan PHP -->
    <div class="space-y-4">
        <?php if ($aktivitas_terbaru->num_rows > 0): ?>
            <?php while($aktivitas = $aktivitas_terbaru->fetch_assoc()): ?>
            <div class="flex items-center">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                    <span class="font-bold text-gray-500"><?php echo strtoupper(substr($aktivitas['nama'], 0, 2)); ?></span>
                </div>
                <div>
                    <p class="text-gray-800"><strong><?php echo htmlspecialchars($aktivitas['nama']); ?></strong> mengumpulkan laporan untuk <strong><?php echo htmlspecialchars($aktivitas['judul_modul']); ?></strong></p>
                    <p class="text-sm text-gray-500"><?php echo date('d M Y, H:i', strtotime($aktivitas['tanggal_kumpul'])); ?> WIB</p>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-gray-500">Belum ada aktivitas laporan terbaru.</p>
        <?php endif; ?>
    </div>
</div>

<?php
// 3. Panggil Footer
require_once 'templates/footer.php';
?>