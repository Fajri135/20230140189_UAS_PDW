<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Tambah Mata Praktikum';
$activePage = 'praktikum'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// Variabel untuk menyimpan pesan
$message = '';

// 3. Logika untuk memproses form saat disubmit (method POST)
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
        // Siapkan query INSERT dengan prepared statement untuk keamanan
        $sql = "INSERT INTO mata_praktikum (kode_matkul, nama_matkul, deskripsi, id_asisten_pengampu) VALUES (?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssi", $kode_matkul, $nama_matkul, $deskripsi, $id_asisten);
            
            // Eksekusi query
            if ($stmt->execute()) {
                // Jika berhasil, redirect ke halaman utama dengan status sukses
                header("Location: manage_praktikum.php?status=sukses");
                exit();
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error: Gagal menyimpan data. ' . $stmt->error . '</div>';
            }
            $stmt->close();
        } else {
            $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Error: Gagal menyiapkan query. ' . $conn->error . '</div>';
        }
    }
}

// 4. Logika untuk mengambil daftar asisten untuk dropdown
$asisten_result = $conn->query("SELECT id, nama FROM users WHERE role = 'asisten' ORDER BY nama ASC");

?>

<!-- Tampilkan pesan jika ada -->
<?php echo $message; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Tambah Mata Praktikum</h2>

    <form action="tambah_praktikum.php" method="POST">
        <!-- Kode Mata Kuliah -->
        <div class="mb-4">
            <label for="kode_matkul" class="block text-gray-700 text-sm font-bold mb-2">Kode Mata Kuliah</label>
            <input type="text" id="kode_matkul" name="kode_matkul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: IF101" required>
        </div>

        <!-- Nama Mata Kuliah -->
        <div class="mb-4">
            <label for="nama_matkul" class="block text-gray-700 text-sm font-bold mb-2">Nama Mata Praktikum</label>
            <input type="text" id="nama_matkul" name="nama_matkul" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Contoh: Praktikum Dasar Pemrograman" required>
        </div>

        <!-- Deskripsi -->
        <div class="mb-4">
            <label for="deskripsi" class="block text-gray-700 text-sm font-bold mb-2">Deskripsi (Opsional)</label>
            <textarea id="deskripsi" name="deskripsi" rows="4" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="Jelaskan secara singkat tentang mata praktikum ini"></textarea>
        </div>

        <!-- Asisten Pengampu -->
        <div class="mb-6">
            <label for="id_asisten" class="block text-gray-700 text-sm font-bold mb-2">Asisten Pengampu</label>
            <select id="id_asisten" name="id_asisten" class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                <option value="">-- Pilih Asisten --</option>
                <?php
                if ($asisten_result->num_rows > 0) {
                    while ($asisten = $asisten_result->fetch_assoc()) {
                        echo '<option value="' . $asisten['id'] . '">' . htmlspecialchars($asisten['nama']) . '</option>';
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
                Simpan Data
            </button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>
