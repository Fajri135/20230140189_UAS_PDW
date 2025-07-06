<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Tambah Modul';
$activePage = 'modul'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// 3. Cek apakah ID praktikum ada di URL
if (!isset($_GET['id_praktikum']) || empty($_GET['id_praktikum'])) {
    header("Location: manage_modul.php?status=gagal");
    exit();
}
$id_praktikum = (int)$_GET['id_praktikum'];

// Variabel untuk menyimpan pesan
$message = '';

// 4. Logika untuk memproses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul_modul = trim($_POST['judul_modul']);
    $deskripsi = trim($_POST['deskripsi']);
    $nama_file_db = null;

    // Validasi judul modul
    if (empty($judul_modul)) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Judul modul wajib diisi!</div>';
    } else {
        // Proses upload file jika ada file yang diunggah
        if (isset($_FILES['file_materi']) && $_FILES['file_materi']['error'] == 0) {
            // PERBAIKAN: Pastikan path direktori benar
            $target_dir = __DIR__ . "/../uploads/materi/";
            
            // Buat nama file yang unik untuk menghindari tumpang tindih
            $nama_file_unik = time() . '_' . basename($_FILES["file_materi"]["name"]);
            $target_file = $target_dir . $nama_file_unik;
            $file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Izinkan format file tertentu (misal: pdf, docx, pptx)
            $allowed_types = ['pdf', 'docx', 'pptx', 'doc'];
            if (in_array($file_type, $allowed_types)) {
                // Pindahkan file ke direktori uploads
                if (move_uploaded_file($_FILES["file_materi"]["tmp_name"], $target_file)) {
                    $nama_file_db = $nama_file_unik;
                } else {
                    $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Maaf, terjadi error saat mengunggah file. Periksa izin folder.</div>';
                }
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Maaf, hanya file PDF, DOCX, DOC & PPTX yang diizinkan.</div>';
            }
        }

        // Jika tidak ada error pada proses upload (atau tidak ada file yang diupload)
        if (empty($message)) {
            $sql = "INSERT INTO modul (id_mata_praktikum, judul_modul, deskripsi, file_materi) VALUES (?, ?, ?, ?)";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("isss", $id_praktikum, $judul_modul, $deskripsi, $nama_file_db);
                if ($stmt->execute()) {
                    header("Location: manage_modul.php?id_praktikum=" . $id_praktikum . "&status=sukses");
                    exit();
                } else {
                    $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error: Gagal menyimpan data modul.</div>';
                }
                $stmt->close();
            }
        }
    }
}

// Ambil nama mata praktikum untuk ditampilkan di judul
$stmt_praktikum = $conn->prepare("SELECT nama_matkul FROM mata_praktikum WHERE id = ?");
$stmt_praktikum->bind_param("i", $id_praktikum);
$stmt_praktikum->execute();
$praktikum_info = $stmt_praktikum->get_result()->fetch_assoc();
$nama_praktikum = $praktikum_info['nama_matkul'] ?? 'Tidak Ditemukan';
?>

<!-- Tampilkan pesan jika ada -->
<?php echo $message; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-2">Tambah Modul Baru</h2>
    <p class="text-gray-600 mb-6">Untuk Mata Praktikum: <strong><?php echo htmlspecialchars($nama_praktikum); ?></strong></p>

    <form action="tambah_modul.php?id_praktikum=<?php echo $id_praktikum; ?>" method="POST" enctype="multipart/form-data">
        <!-- Judul Modul -->
        <div class="mb-4">
            <label for="judul_modul" class="block text-gray-700 text-sm font-bold mb-2">Judul Modul</label>
            <input type="text" id="judul_modul" name="judul_modul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: Modul 1 - Pengenalan HTML" required>
        </div>

        <!-- Deskripsi -->
        <div class="mb-4">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Jelaskan isi dari modul ini"></textarea>
        </div>

        <!-- File Materi -->
        <div class="mb-6">
            <label for="file_materi" class="block text-gray-700 text-sm font-bold mb-2">File Materi (PDF, DOCX, PPTX)</label>
            <input type="file" id="file_materi" name="file_materi" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end">
            <a href="manage_modul.php?id_praktikum=<?php echo $id_praktikum; ?>" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300 mr-2">
                Batal
            </a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                Simpan Modul
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>