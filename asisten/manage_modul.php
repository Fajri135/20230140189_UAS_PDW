<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Manajemen Modul';
$activePage = 'modul'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

$id_asisten = $_SESSION['user_id'];
$selected_praktikum_id = isset($_GET['id_praktikum']) ? (int)$_GET['id_praktikum'] : 0;

// 3. Ambil daftar mata praktikum yang diampu oleh asisten ini
$praktikum_list_result = $conn->prepare("SELECT id, nama_matkul FROM mata_praktikum WHERE id_asisten_pengampu = ? ORDER BY nama_matkul ASC");
$praktikum_list_result->bind_param("i", $id_asisten);
$praktikum_list_result->execute();
$daftar_praktikum = $praktikum_list_result->get_result();

// 4. Jika ada praktikum yang dipilih, ambil daftar modulnya
$modul_result = null;
if ($selected_praktikum_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM modul WHERE id_mata_praktikum = ? ORDER BY id ASC");
    $stmt->bind_param("i", $selected_praktikum_id);
    $stmt->execute();
    $modul_result = $stmt->get_result();
}
?>

<div class="bg-white p-6 rounded-lg shadow-md mb-6">
    <h2 class="text-2xl font-bold text-gray-800 mb-4">Pilih Mata Praktikum</h2>
    <form action="manage_modul.php" method="GET">
        <div class="flex items-center space-x-4">
            <select name="id_praktikum" class="flex-grow shadow border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" onchange="this.form.submit()">
                <option value="">-- Silakan Pilih Mata Praktikum --</option>
                <?php while($p = $daftar_praktikum->fetch_assoc()): ?>
                    <option value="<?php echo $p['id']; ?>" <?php echo ($selected_praktikum_id == $p['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($p['nama_matkul']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </form>
</div>


<?php if ($selected_praktikum_id > 0): ?>
<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Modul</h2>
        <a href="tambah_modul.php?id_praktikum=<?php echo $selected_praktikum_id; ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
            + Tambah Modul Baru
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="w-1/12 text-left py-3 px-4 uppercase font-semibold text-sm">No</th>
                    <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">Judul Modul</th>
                    <th class="w-4/12 text-left py-3 px-4 uppercase font-semibold text-sm">File Materi</th>
                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($modul_result && $modul_result->num_rows > 0): ?>
                    <?php $no = 1; ?>
                    <?php while($row = $modul_result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="text-left py-3 px-4"><?php echo $no++; ?></td>
                        <td class="text-left py-3 px-4"><?php echo htmlspecialchars($row['judul_modul']); ?></td>
                        <td class="text-left py-3 px-4">
                            <?php if (!empty($row['file_materi'])): ?>
                                <a href="../uploads/materi/<?php echo htmlspecialchars($row['file_materi']); ?>" target="_blank" class="text-blue-500 hover:underline">
                                    <?php echo htmlspecialchars($row['file_materi']); ?>
                                </a>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td class="text-center py-3 px-4">
                            <a href="edit_modul.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 font-semibold mr-4">Edit</a>
                            <a href="hapus_modul.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus modul ini?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-3 px-4">Belum ada modul untuk mata praktikum ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php
$conn->close();
require_once 'templates/footer.php';
?>