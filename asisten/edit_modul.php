<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Edit Modul';
$activePage = 'modul'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// 3. Cek apakah ID modul ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_modul.php?status=gagal");
    exit();
}
$id_modul = (int)$_GET['id'];

// Variabel untuk menyimpan pesan
$message = '';

// Ambil data modul saat ini untuk ditampilkan di form
$stmt_current = $conn->prepare("SELECT * FROM modul WHERE id = ?");
$stmt_current->bind_param("i", $id_modul);
$stmt_current->execute();
$modul = $stmt_current->get_result()->fetch_assoc();
$stmt_current->close();

// 4. Logika untuk memproses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && $modul) { // Pastikan $modul ada sebelum proses
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi = trim($_POST['deskripsi']);
    $nama_file_db = $modul['file_materi']; // Default ke nama file yang sudah ada

    // Validasi
    if (empty($judul_modul)) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Judul modul wajib diisi!</div>';
    } else {
        // Cek jika ada file baru yang diunggah
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
            // Hapus file lama jika ada
            if (!empty($nama_file_db) && file_exists(__DIR__ . "/../uploads/materi/" . $nama_file_db)) {
                unlink(__DIR__ . "/../uploads/materi/" . $nama_file_db);
            }

            $target_dir = __DIR__ . "/../uploads/materi/";
            $nama_file_unik = time() . '_' . basename($_FILES["file_materi"]["name"]);
            $target_file = $target_dir . $nama_file_unik;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_types = ['pdf', 'docx', 'pptx', 'doc'];

            if (in_array($file_type, $allowed_types)) {
                if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
                    $nama_file_db = $nama_file_unik; 
                } else {
                    $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error saat mengunggah file baru.</div>';
                }
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Format file baru tidak diizinkan.</div>';
            }
        }
        // Cek jika pengguna ingin menghapus file yang ada
        elseif (isset($_POST['hapus_file']) && $_POST['hapus_file'] == '1') {
            if (!empty($nama_file_db) && file_exists(__DIR__ . "/../uploads/materi/" . $nama_file_db)) {
                unlink(__DIR__ . "/../uploads/materi/" . $nama_file_db);
            }
            $nama_file_db = null; 
        }

        // Jika tidak ada error, update database
        if (empty($message)) {
            $sql = "UPDATE modul SET judul_modul = ?, deskripsi = ?, file_materi = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssi", $judul_modul, $deskripsi, $nama_file_db, $id_modul);
                if ($stmt->execute()) {
                    header("Location: manage_modul.php?id_praktikum=" . $modul['id_mata_praktikum'] . "&status=sukses");
                    exit();
                } else {
                    $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error: Gagal memperbarui data modul.</div>';
                }
                $stmt->close();
            }
        }
    }
}

// --- PERBAIKAN DIMULAI DI SINI ---
if (!$modul) {
    // Jika data tidak ditemukan, tampilkan pesan error
    echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">Data modul tidak ditemukan. Mungkin sudah dihapus.</span>
            <br><a href="manage_modul.php" class="text-red-700 font-bold hover:underline mt-2 inline-block">Kembali ke Manajemen Modul</a>
          </div>';
} else {
    // Jika data ditemukan, tampilkan konten form
?>

    <!-- Tampilkan pesan jika ada -->
    <?php echo $message; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Edit Modul</h2>

        <form action="edit_modul.php?id=<?php echo $id_modul; ?>" method="POST" enctype="multipart/form-data">
            <!-- Judul Modul -->
            <div class="mb-4">
                <label for="judul_modul" class="block text-gray-700 text-sm font-bold mb-2">Judul Modul</label>
                <input type="text" id="judul_modul" name="judul_modul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($modul['judul_modul']); ?>" required>
            </div>

            <!-- Deskripsi -->
            <div class="mb-4">
                <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($modul['deskripsi']); ?></textarea>
            </div>

            <!-- File Materi -->
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">File Materi Saat Ini</label>
                <?php if (!empty($modul['file_materi'])): ?>
                    <div class="flex items-center justify-between p-2 border rounded-md">
                        <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" target="_blank" class="text-blue-500 hover:underline"><?php echo htmlspecialchars($modul['file_materi']); ?></a>
                        <div class="flex items-center">
                            <input type="checkbox" id="hapus_file" name="hapus_file" value="1" class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <label for="hapus_file" class="ml-2 block text-sm text-red-700">Hapus File</label>
                        </div>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic">Tidak ada file materi yang diunggah.</p>
                <?php endif; ?>
            </div>

            <div class="mb-6">
                <label for="file_materi" class="block text-gray-700 text-sm font-bold mb-2">Ganti/Unggah File Materi Baru (PDF, DOCX, PPTX)</label>
                <input type="file" id="file_materi" name="file_materi" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center justify-end">
                <a href="manage_modul.php?id_praktikum=<?php echo $modul['id_mata_praktikum']; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 mr-2">
                    Batal
                </a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                    Update Modul
                </button>
            </div>
        </form>
    </div>

<?php
} // --- AKHIR DARI BLOK KONTEN ---

$conn->close();
require_once 'templates/footer.php';
?>
