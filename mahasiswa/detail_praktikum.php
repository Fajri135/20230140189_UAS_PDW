<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Detail Praktikum';
$activePage = 'my_courses'; 

// 2. Panggil Header Mahasiswa dan Konfigurasi
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// 3. Validasi ID Praktikum dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: my_courses.php");
    exit();
}
$id_praktikum = (int)$_GET['id'];
$id_mahasiswa = $_SESSION['user_id'];

// 4. Ambil informasi umum mata praktikum
$stmt_praktikum = $conn->prepare("SELECT nama_matkul, kode_matkul FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $id_praktikum);
$stmt_praktikum->execute();
$praktikum_info = $stmt_praktikum->get_result()->fetch_assoc();
if (!$praktikum_info) {
    header("Location: my_courses.php");
    exit();
}
$stmt_praktikum->close();

// 5. Ambil semua modul beserta status pengumpulan laporan untuk mahasiswa ini
$sql = "SELECT 
            m.id as id_modul, 
            m.judul_modul, 
            m.deskripsi, 
            m.file_materi,
            l.id as id_laporan,
            l.file_laporan,
            l.tanggal_kumpul,
            l.nilai,
            l.feedback,
            l.status
        FROM modul m
        LEFT JOIN laporan_mahasiswa l ON m.id = l.id_modul AND l.id_mahasiswa = ?
        WHERE m.id_mata_praktikum = ?
        ORDER BY m.id ASC";

$stmt_modul = $conn->prepare($sql);
$stmt_modul->bind_param("ii", $id_mahasiswa, $id_praktikum);
$stmt_modul->execute();
$result_modul = $stmt_modul->get_result();

?>

<!-- Konten Utama -->
<div class="mb-6">
    <p class="text-sm text-gray-500"><?php echo htmlspecialchars($praktikum_info['kode_matkul']); ?></p>
    <h1 class="text-3xl font-bold text-gray-800"><?php echo htmlspecialchars($praktikum_info['nama_matkul']); ?></h1>
</div>

<!-- Notifikasi -->
<?php if (isset($_GET['status'])): ?>
    <?php if ($_GET['status'] == 'sukses_upload'): ?>
        <div class="mb-4 p-4 rounded-md bg-green-500 text-white">Laporan berhasil diunggah!</div>
    <?php elseif ($_GET['status'] == 'gagal_upload'): ?>
        <div class="mb-4 p-4 rounded-md bg-red-500 text-white">Gagal mengunggah laporan. Pastikan format file benar.</div>
    <?php endif; ?>
<?php endif; ?>


<!-- Daftar Modul (Accordion) -->
<div class="space-y-4">
    <?php if ($result_modul->num_rows > 0): ?>
        <?php while($modul = $result_modul->fetch_assoc()): ?>
        <div class="bg-white rounded-lg shadow-md">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($modul['judul_modul']); ?></h3>
                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($modul['deskripsi']); ?></p>
                
                <!-- Tombol Unduh Materi -->
                <?php if (!empty($modul['file_materi'])): ?>
                <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="inline-block mt-4 bg-blue-100 text-blue-700 hover:bg-blue-200 font-semibold py-2 px-4 rounded-lg text-sm">
                    Unduh Materi
                </a>
                <?php endif; ?>
            </div>

            <!-- Bagian Pengumpulan dan Nilai -->
            <div class="bg-gray-50 p-6 border-t border-gray-200">
                <h4 class="font-bold text-gray-800 mb-4">Pengumpulan Laporan</h4>
                
                <?php if ($modul['id_laporan']): // Jika sudah pernah mengumpulkan ?>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Status Pengumpulan -->
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <?php if ($modul['status'] == 'dinilai'): ?>
                                <p class="font-bold text-green-600">Sudah Dinilai</p>
                            <?php else: ?>
                                <p class="font-bold text-yellow-600">Sudah Dikumpulkan</p>
                            <?php endif; ?>
                        </div>
                        <!-- Waktu Kumpul -->
                        <div>
                            <p class="text-sm text-gray-500">Waktu Kumpul</p>
                            <p class="font-semibold text-gray-800"><?php echo date('d M Y, H:i', strtotime($modul['tanggal_kumpul'])); ?></p>
                        </div>
                        <!-- File Terkumpul -->
                        <div>
                            <p class="text-sm text-gray-500">File Laporan</p>
                            <a href="../uploads/laporan/<?php echo htmlspecialchars($modul['file_laporan']); ?>" target="_blank" class="text-blue-500 hover:underline font-semibold">
                                <?php echo htmlspecialchars($modul['file_laporan']); ?>
                            </a>
                        </div>
                    </div>
                    
                    <?php if ($modul['status'] == 'dinilai'): ?>
                    <!-- Bagian Nilai -->
                    <div class="mt-6 border-t pt-4">
                        <h4 class="font-bold text-gray-800 mb-4">Penilaian</h4>
                        <div class="flex items-center space-x-8">
                            <div>
                                <p class="text-sm text-gray-500">Nilai</p>
                                <p class="text-3xl font-bold text-indigo-600"><?php echo htmlspecialchars($modul['nilai']); ?></p>
                            </div>
                            <div class="flex-grow">
                                <p class="text-sm text-gray-500">Feedback dari Asisten</p>
                                <p class="text-gray-700 italic bg-gray-100 p-3 rounded-md"><?php echo nl2br(htmlspecialchars($modul['feedback'])); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                <?php else: // Jika belum mengumpulkan ?>
                    <form action="kumpul_laporan_proses.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_modul" value="<?php echo $modul['id_modul']; ?>">
                        <input type="hidden" name="id_praktikum" value="<?php echo $id_praktikum; ?>">
                        
                        <p class="text-sm text-gray-500 mb-2">Anda belum mengumpulkan laporan untuk modul ini.</p>
                        <div class="flex items-center space-x-4">
                            <input type="file" name="file_laporan" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                            <button type="submit" class="bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-2 px-4 rounded-lg whitespace-nowrap">
                                Kumpulkan Laporan
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-12 px-6 bg-white rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-700">Belum Ada Modul</h3>
            <p class="text-gray-500 mt-2">Asisten belum menambahkan modul untuk mata praktikum ini.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt_modul->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>