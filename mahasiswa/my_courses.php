<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Praktikum Saya';
$activePage = 'my_courses'; 

// 2. Panggil Header Mahasiswa dan Konfigurasi
require_once 'templates/header_mahasiswa.php';
require_once '../config.php';

// 3. Ambil ID mahasiswa yang sedang login
$id_mahasiswa = $_SESSION['user_id'];

// 4. Ambil semua data praktikum yang diikuti oleh mahasiswa ini
$sql = "SELECT mp.id, mp.kode_matkul, mp.nama_matkul, u.nama as nama_asisten
        FROM pendaftaran_praktikum pp
        JOIN mata_praktikum mp ON pp.id_mata_praktikum = mp.id
        LEFT JOIN users u ON mp.id_asisten_pengampu = u.id
        WHERE pp.id_mahasiswa = ?
        ORDER BY mp.nama_matkul ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_mahasiswa);
$stmt->execute();
$result = $stmt->get_result();
?>

<!-- Konten Utama -->
<h1 class="text-3xl font-bold text-gray-800 mb-2">Praktikum Saya</h1>
<p class="text-gray-600 mb-8">Berikut adalah daftar semua mata praktikum yang Anda ikuti.</p>

<!-- Grid Kartu Praktikum -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
            <div class="p-6 flex-grow">
                <div class="text-sm text-gray-500 font-semibold mb-1"><?php echo htmlspecialchars($row['kode_matkul']); ?></div>
                <h3 class="text-xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($row['nama_matkul']); ?></h3>
                <p class="text-xs text-gray-500">Dosen Pengampu:</p>
                <p class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_asisten'] ?? 'Belum ditentukan'); ?></p>
            </div>
            <div class="p-4 bg-gray-50 border-t border-gray-200">
                <a href="detail_praktikum.php?id=<?php echo $row['id']; ?>" class="block w-full text-center bg-indigo-500 hover:bg-indigo-600 text-white font-bold py-3 px-4 rounded-lg transition-colors duration-300">
                    Lihat Detail & Tugas
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12 px-6 bg-white rounded-lg shadow-md">
            <h3 class="text-xl font-semibold text-gray-700">Anda Belum Terdaftar di Praktikum Apapun</h3>
            <p class="text-gray-500 mt-2 mb-4">Silakan cari dan daftar ke mata praktikum yang tersedia di katalog.</p>
            <a href="../katalog_praktikum.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">
                Lihat Katalog
            </a>
        </div>
    <?php endif; ?>
</div>

<?php
$stmt->close();
$conn->close();
require_once 'templates/footer_mahasiswa.php';
?>