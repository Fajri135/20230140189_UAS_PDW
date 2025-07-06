<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Beri Nilai Laporan';
$activePage = 'laporan'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// 3. Cek apakah ID laporan ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: laporan_masuk.php?status=gagal");
    exit();
}
$id_laporan = (int)$_GET['id'];

$message = '';

// 4. Logika untuk memproses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nilai = trim($_POST['nilai']);
    $feedback = trim($_POST['feedback']);
    
    // Validasi nilai
    if (!is_numeric($nilai) || $nilai < 0 || $nilai > 100) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Nilai harus berupa angka antara 0 dan 100.</div>';
    } else {
        $sql = "UPDATE laporan_mahasiswa SET nilai = ?, feedback = ?, status = 'dinilai' WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isi", $nilai, $feedback, $id_laporan);
            if ($stmt->execute()) {
                header("Location: laporan_masuk.php?status=sukses");
                exit();
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error: Gagal menyimpan penilaian.</div>';
            }
            $stmt->close();
        }
    }
}


// 5. Ambil detail lengkap laporan dari database
$sql_detail = "SELECT l.*, u.nama as nama_mahasiswa, mp.nama_matkul, m.judul_modul 
               FROM laporan_mahasiswa l
               JOIN users u ON l.id_mahasiswa = u.id
               JOIN modul m ON l.id_modul = m.id
               JOIN mata_praktikum mp ON m.id_mata_praktikum = mp.id
               WHERE l.id = ?";
$stmt_detail = $conn->prepare($sql_detail);
$stmt_detail->bind_param("i", $id_laporan);
$stmt_detail->execute();
$laporan = $stmt_detail->get_result()->fetch_assoc();

// --- PERBAIKAN DIMULAI DI SINI ---
if (!$laporan) {
    // Jika data tidak ditemukan, tampilkan pesan error, jangan langsung exit
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Data laporan tidak ditemukan. Mungkin sudah dihapus.</span>
            <br><a href="laporan_masuk.php" class="text-red-700 font-bold hover:underline mt-2 inline-block">Kembali ke Laporan Masuk</a>
          </div>';
} else {
    // Jika data ditemukan, tampilkan konten seperti biasa
?>

    <!-- Tampilkan pesan jika ada -->
    <?php echo $message; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Informasi Laporan -->
        <div class="lg:col-span-1 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Detail Laporan</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-sm text-gray-500">Mahasiswa</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Mata Praktikum</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($laporan['nama_matkul']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Modul</p>
                    <p class="font-semibold text-gray-800"><?php echo htmlspecialchars($laporan['judul_modul']); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Tanggal Kumpul</p>
                    <p class="font-semibold text-gray-800"><?php echo date('d M Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">File Laporan</p>
                    <?php if (!empty($laporan['file_laporan'])): ?>
                        <a href="../uploads/laporan/<?php echo htmlspecialchars($laporan['file_laporan']); ?>" target="_blank" class="text-blue-500 hover:underline font-semibold flex items-center">
                            <svg class="w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                            Unduh Laporan
                        </a>
                    <?php else: ?>
                        <p class="text-red-500 italic">File tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Kolom Form Penilaian -->
        <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-bold text-gray-800 mb-4">Form Penilaian</h2>
            <form action="nilai_laporan.php?id=<?php echo $id_laporan; ?>" method="POST">
                <!-- Nilai -->
                <div class="mb-4">
                    <label for="nilai" class="block text-gray-700 text-sm font-bold mb-2">Nilai (0-100)</label>
                    <input type="number" id="nilai" name="nilai" min="0" max="100" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Masukkan nilai" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" required>
                </div>

                <!-- Feedback -->
                <div class="mb-6">
                    <label for="feedback" class="block text-gray-700 text-sm font-bold mb-2">Umpan Balik / Feedback</label>
                    <textarea id="feedback" name="feedback" rows="6" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Berikan umpan balik untuk laporan ini"><?php echo htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="flex items-center justify-end">
                    <a href="laporan_masuk.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 mr-2">
                        Kembali
                    </a>
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                        Simpan Penilaian
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php
// --- AKHIR DARI BLOK KONTEN ---
} 

$conn->close();
require_once 'templates/footer.php';
?>
