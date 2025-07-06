<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Laporan Masuk';
$activePage = 'laporan'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

$id_asisten = $_SESSION['user_id'];

// --- Logika untuk Filter ---
$filter_praktikum = isset($_GET['praktikum']) ? (int)$_GET['praktikum'] : 0;
$filter_modul = isset($_GET['modul']) ? (int)$_GET['modul'] : 0;

// Ambil daftar praktikum yang diampu untuk filter
$praktikum_list_result = $conn->prepare("SELECT id, nama_matkul FROM mata_praktikum WHERE id_asisten_pengampu = ?");
$praktikum_list_result->bind_param("i", $id_asisten);
$praktikum_list_result->execute();
$daftar_praktikum = $praktikum_list_result->get_result();

// Ambil daftar modul berdasarkan praktikum yang dipilih untuk filter
$daftar_modul = null;
if ($filter_praktikum > 0) {
    $modul_list_result = $conn->prepare("SELECT id, judul_modul FROM modul WHERE id_mata_praktikum = ?");
    $modul_list_result->bind_param("i", $filter_praktikum);
    $modul_list_result->execute();
    $daftar_modul = $modul_list_result->get_result();
}

// --- Logika untuk Mengambil Data Laporan ---
$sql = "SELECT l.id, u.nama as nama_mahasiswa, mp.nama_matkul, m.judul_modul, l.tanggal_kumpul, l.status 
        FROM laporan_mahasiswa l
        JOIN users u ON l.id_mahasiswa = u.id
        JOIN modul m ON l.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_mata_praktikum = mp.id
        WHERE mp.id_asisten_pengampu = ?";

$params = [$id_asisten];
$types = "i";

if ($filter_praktikum > 0) {
    $sql .= " AND mp.id = ?";
    $params[] = $filter_praktikum;
    $types .= "i";
}
if ($filter_modul > 0) {
    $sql .= " AND m.id = ?";
    $params[] = $filter_modul;
    $types .= "i";
}

$sql .= " ORDER BY l.tanggal_kumpul DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$laporan_result = $stmt->get_result();

?>

<!-- Form Filter -->
<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Filter Laporan</h2>
    <form action="laporan_masuk.php" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label for="praktikum" class="block text-sm font-medium text-gray-700">Mata Praktikum</label>
            <select name="praktikum" id="praktikum" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="this.form.submit()">
                <option value="">Semua Praktikum</option>
                <?php mysqli_data_seek($daftar_praktikum, 0); // Reset pointer
                while($p = $daftar_praktikum->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nama_matkul']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="modul" class="block text-sm font-medium text-gray-700">Modul</label>
            <select name="modul" id="modul" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" <?php echo !$daftar_modul ? 'disabled' : ''; ?>>
                <option value="">Semua Modul</option>
                <?php if ($daftar_modul):
                while($m = $daftar_modul->fetch_assoc()): ?>
                    <option value="<?php echo $m['id']; ?>" <?php echo ($filter_modul == $m['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($m['judul_modul']); ?>
                    </option>
                <?php endwhile; endif; ?>
            </select>
        </div>
        <div class="self-end">
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">Filter</button>
        </div>
    </form>
</div>

<!-- Tabel Laporan Masuk -->
<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Daftar Laporan Masuk</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Mahasiswa</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Mata Praktikum</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Modul</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Tgl Kumpul</th>
                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Status</th>
                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($laporan_result->num_rows > 0): ?>
                    <?php while($row = $laporan_result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama_matkul']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['judul_modul']); ?></td>
                        <td class="py-3 px-4"><?php echo date('d M Y H:i', strtotime($row['tanggal_kumpul'])); ?></td>
                        <td class="text-center py-3 px-4">
                            <?php if ($row['status'] == 'dinilai'): ?>
                                <span class="bg-green-200 text-green-800 py-1 px-3 rounded-full text-xs">Dinilai</span>
                            <?php else: ?>
                                <span class="bg-yellow-200 text-yellow-800 py-1 px-3 rounded-full text-xs">Dikumpulkan</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center py-3 px-4">
                            <a href="nilai_laporan.php?id=<?php echo $row['id']; ?>" class="bg-indigo-500 hover:bg-indigo-600 text-white text-sm font-bold py-1 px-3 rounded-lg">
                                <?php echo ($row['status'] == 'dinilai') ? 'Lihat/Edit Nilai' : 'Beri Nilai'; ?>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">Tidak ada laporan yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>