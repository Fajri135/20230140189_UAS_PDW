<?php
// Mulai session untuk memeriksa status login
session_start();
require_once 'config.php';

// Ambil semua data mata praktikum beserta nama asisten pengampu
$sql = "SELECT mp.id, mp.kode_matkul, mp.nama_matkul, mp.deskripsi, u.nama as nama_asisten 
        FROM mata_praktikum mp 
        LEFT JOIN users u ON mp.id_asisten_pengampu = u.id 
        ORDER BY mp.nama_matkul ASC";
$result = $conn->query($sql);

// Cek apakah pengguna yang login adalah mahasiswa
$is_mahasiswa = isset($_SESSION['user_id']) && $_SESSION['role'] == 'mahasiswa';
$id_mahasiswa = $is_mahasiswa ? $_SESSION['user_id'] : 0;

// Ambil daftar praktikum yang sudah diikuti oleh mahasiswa (jika login)
$praktikum_diikuti = [];
if ($is_mahasiswa) {
    $stmt_diikuti = $conn->prepare("SELECT id_mata_praktikum FROM pendaftaran_praktikum WHERE id_mahasiswa = ?");
    $stmt_diikuti->bind_param("i", $id_mahasiswa);
    $stmt_diikuti->execute();
    $result_diikuti = $stmt_diikuti->get_result();
    while ($row = $result_diikuti->fetch_assoc()) {
        $praktikum_diikuti[] = $row['id_mata_praktikum'];
    }
    $stmt_diikuti->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Katalog Mata Praktikum - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">

    <!-- Navbar Publik -->
    <nav class="bg-white shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex-shrink-0">
                    <span class="text-2xl font-bold text-blue-600">SIMPRAK</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="katalog_praktikum.php" class="text-gray-700 font-semibold hover:text-blue-600">Katalog Praktikum</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="<?php echo $_SESSION['role'] == 'asisten' ? 'asisten/dashboard.php' : 'mahasiswa/dashboard.php'; ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded-lg">Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Konten Utama -->
    <div class="container mx-auto p-6 lg:p-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Katalog Mata Praktikum</h1>
        <p class="text-gray-600 mb-8">Temukan dan daftar ke mata praktikum yang tersedia di bawah ini.</p>

        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses_daftar'): ?>
            <div class="mb-4 p-4 rounded-md bg-green-500 text-white">
                Anda berhasil mendaftar ke praktikum!
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'gagal_daftar'): ?>
            <div class="mb-4 p-4 rounded-md bg-red-500 text-white">
                Gagal mendaftar ke praktikum. Silakan coba lagi.
            </div>
        <?php endif; ?>

        <!-- Grid Kartu Praktikum -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden flex flex-col">
                    <div class="p-6 flex-grow">
                        <div class="text-sm text-gray-500 font-semibold mb-1"><?php echo htmlspecialchars($row['kode_matkul']); ?></div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($row['nama_matkul']); ?></h3>
                        <p class="text-gray-700 text-sm mb-4"><?php echo nl2br(htmlspecialchars($row['deskripsi'])); ?></p>
                    </div>
                    <div class="p-6 bg-gray-50 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-2">Dosen Pengampu:</p>
                        <p class="text-sm font-semibold text-gray-800 mb-4"><?php echo htmlspecialchars($row['nama_asisten'] ?? 'Belum ditentukan'); ?></p>
                        
                        <?php if ($is_mahasiswa): ?>
                            <?php if (in_array($row['id'], $praktikum_diikuti)): ?>
                                <button class="w-full bg-green-500 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed" disabled>
                                    Sudah Terdaftar
                                </button>
                            <?php else: ?>
                                <a href="daftar_proses.php?id_praktikum=<?php echo $row['id']; ?>" class="block w-full text-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                                    Daftar Praktikum
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-gray-500 col-span-3 text-center">Saat ini belum ada mata praktikum yang tersedia.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
<?php $conn->close(); ?>