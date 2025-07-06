<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Edit Mata Praktikum';
$activePage = 'praktikum'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// Variabel untuk menyimpan pesan dan data
$message = '';
$praktikum = null;

// 3. Cek apakah ID ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_praktikum.php");
    exit();
}
$id = $_GET['id'];

// 4. Logika untuk memproses form saat disubmit (method POST)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form dan bersihkan
    $kode_matkul = trim($_POST['kode_matkul']);
    $nama_matkul = trim($_POST['nama_matkul']);
    $deskripsi = trim($_POST['deskripsi']);
    $id_asisten = $_POST['id_asisten'];

    // Validasi sederhana
    if (empty($kode_matkul) || empty($nama_matkul) || empty($id_asisten)) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Kode, Nama, dan Asisten Pengampu wajib diisi!</div>';
    } else {
        // Siapkan query UPDATE dengan prepared statement
        $sql = "UPDATE mata_praktikum SET kode_matkul = ?, nama_matkul = ?, deskripsi = ?, id_asisten_pengampu = ? WHERE id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssii", $kode_matkul, $nama_matkul, $deskripsi, $id_asisten, $id);
            
            if ($stmt->execute()) {
                header("Location: manage_praktikum.php?status=sukses");
                exit();
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error: Gagal memperbarui data. ' . $stmt->error . '</div>';
            }
            $stmt->close();
        }
    }
}

// 5. Logika untuk mengambil data praktikum yang akan diedit
$stmt_select = $conn->prepare("SELECT * FROM mata_praktikum WHERE id = ?");
$stmt_select->bind_param("i", $id);
$stmt_select->execute();
$result = $stmt_select->get_result();
$praktikum = $result->fetch_assoc();
$stmt_select->close();

// 6. Logika untuk mengambil daftar asisten untuk dropdown
$asisten_result = $conn->query("SELECT id, nama FROM users WHERE role = 'asisten' ORDER BY nama ASC");

// --- PERBAIKAN DIMULAI DI SINI ---
if (!$praktikum) {
    // Jika data tidak ditemukan, tampilkan pesan error
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Data mata praktikum tidak ditemukan. Mungkin sudah dihapus.</span>
            <br><a href="manage_praktikum.php" class="text-red-700 font-bold hover:underline mt-2 inline-block">Kembali ke Manajemen Praktikum</a>
          </div>';
} else {
    // Jika data ditemukan, tampilkan konten form
?>

    <!-- Tampilkan pesan jika ada -->
    <?php echo $message; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Edit Mata Praktikum</h2>

        <form action="edit_praktikum.php?id=<?php echo $id; ?>" method="POST">
            <!-- Kode Mata Kuliah -->
            <div class="mb-4">
                <label for="kode_matkul" class="block text-gray-700 text-sm font-bold mb-2">Kode Mata Kuliah</label>
                <input type="text" id="kode_matkul" name="kode_matkul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($praktikum['kode_matkul']); ?>" required>
            </div>

            <!-- Nama Mata Kuliah -->
            <div class="mb-4">
                <label for="nama_matkul" class="block text-gray-700 text-sm font-bold mb-2">Nama Mata Praktikum</label>
                <input type="text" id="nama_matkul" name="nama_matkul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($praktikum['nama_matkul']); ?>" required>
            </div>

            <!-- Deskripsi -->
            <div class="mb-4">
                <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></textarea>
            </div>

            <!-- Asisten Pengampu -->
            <div class="mb-6">
                <label for="id_asisten" class="block text-gray-700 text-sm font-bold mb-2">Asisten Pengampu</label>
                <select id="id_asisten" name="id_asisten" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                    <option value="">-- Pilih Asisten --</option>
                    <?php
                    if ($asisten_result->num_rows > 0) {
                        while ($asisten = $asisten_result->fetch_assoc()) {
                            // Tandai asisten yang terpilih
                            $selected = ($asisten['id'] == $praktikum['id_asisten_pengampu']) ? 'selected' : '';
                            echo '<option value="' . $asisten['id'] . '" ' . $selected . '>' . htmlspecialchars($asisten['nama']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end">
                <a href="manage_praktikum.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 mr-2">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                    Update Data
                </button>
            </div>
        </form>
    </div>

<?php
} // --- AKHIR DARI BLOK KONTEN ---

$conn->close();
require_once 'templates/footer.php';
?>
