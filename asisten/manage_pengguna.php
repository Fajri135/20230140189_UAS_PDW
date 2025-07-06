<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Manajemen Pengguna';
$activePage = 'pengguna'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// 3. Logika untuk mengambil semua data pengguna
$result = $conn->query("SELECT id, nama, email, role, created_at FROM users ORDER BY created_at DESC");

?>

<!-- Notifikasi (jika ada dari aksi sebelumnya) -->
<?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
<div class="mb-4 p-4 rounded-md bg-green-500 text-white">
    Aksi berhasil dilakukan!
</div>
<?php endif; ?>
<?php if (isset($_GET['status']) && $_GET['status'] == 'gagal'): ?>
<div class="mb-4 p-4 rounded-md bg-red-500 text-white">
    Terjadi kesalahan! Aksi tidak dapat diselesaikan.
</div>
<?php endif; ?>


<div class="bg-white p-6 rounded-lg shadow-md">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Akun Pengguna</h2>
        <!-- Tombol Tambah Pengguna sekarang aktif -->
        <a href="tambah_pengguna.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">+ Tambah Pengguna</a>
    </div>

    <!-- Tabel untuk menampilkan data -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Nama Lengkap</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Email</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Peran (Role)</th>
                    <th class="text-left py-3 px-4 uppercase font-semibold text-sm">Tanggal Daftar</th>
                    <th class="text-center py-3 px-4 uppercase font-semibold text-sm">Aksi</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if ($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="border-b">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['nama']); ?></td>
                        <td class="py-3 px-4"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td class="py-3 px-4">
                            <span class="capitalize py-1 px-3 rounded-full text-xs <?php echo $row['role'] == 'asisten' ? 'bg-blue-200 text-blue-800' : 'bg-green-200 text-green-800'; ?>">
                                <?php echo htmlspecialchars($row['role']); ?>
                            </span>
                        </td>
                        <td class="py-3 px-4"><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                        <td class="text-center py-3 px-4">
                            <!-- Tombol Edit dan Hapus sekarang aktif -->
                            <a href="edit_pengguna.php?id=<?php echo $row['id']; ?>" class="text-blue-500 hover:text-blue-700 font-semibold mr-4">Edit</a>
                            <a href="hapus_pengguna.php?id=<?php echo $row['id']; ?>" class="text-red-500 hover:text-red-700 font-semibold" onclick="return confirm('PENTING: Menghapus pengguna ini akan menghapus semua data yang terkait (pendaftaran praktikum, laporan, nilai, dll). Apakah Anda yakin ingin melanjutkan?');">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-4">Belum ada pengguna yang terdaftar.</td>
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