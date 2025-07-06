<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Manajemen Mata Praktikum';
$activePage = 'praktikum'; 

// 2. Panggil Header
require_once 'templates/header.php';
require_once '../config.php';

// 3. Logika untuk mengambil semua data mata praktikum
$result = $conn->query("SELECT mp.*, u.nama as nama_asisten FROM mata_praktikum mp LEFT JOIN users u ON mp.id_asisten_pengampu = u.id ORDER BY mp.id DESC");

?>

<!-- PERBAIKAN: Blok untuk menampilkan notifikasi -->
<?php if (isset($_GET['status'])): ?>
<div class="mb-4 p-4 rounded-md text-white <?php echo $_GET['status'] == 'sukses' ? 'bg-green-500' : 'bg-red-500'; ?>">
    <?php
        if ($_GET['status'] == 'sukses') {
            echo "Aksi berhasil dilakukan!";
        } else {
            echo "Terjadi kesalahan!";
        }
    ?>
</div>
<?php endif; ?>


<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Mata Praktikum</h2>
        <a href="tambah_praktikum.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
            + Tambah Praktikum
        </a>
    </div>

    <!-- Tabel untuk menampilkan data -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">No</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Kode</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Mata Praktikum</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Asisten Pengampu</th>
                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($result->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="text-left py-3 px-4"><?php echo $no++; ?></td>
                        <td class="text-left py-3 px-4"><?php echo htmlspecialchars($row['kode_matkul']); ?></td>
                        <td class="text-left py-3 px-4"><?php echo htmlspecialchars($row['nama_matkul']); ?></td>
                        <td class="text-left py-3 px-4"><?php echo htmlspecialchars($row['nama_asisten'] ?? 'Belum Ditentukan'); ?></td>
                        <td class="text-center py-3 px-4">
                            <a href="edit_praktikum.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 font-semibold mr-4">Edit</a>
                            <a href="hapus_praktikum.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-3 px-4">Belum ada data mata praktikum.</td>
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
