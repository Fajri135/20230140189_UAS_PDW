<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Tambah Pengguna Baru';
$activePage = 'pengguna'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

$message = '';

// 3. Logika untuk memproses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Semua field wajib diisi!</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Format email tidak valid!</div>';
    } else {
        // Cek duplikasi email
        $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Email sudah terdaftar. Gunakan email lain.</div>';
        } else {
            // Hash password dan simpan ke DB
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: manage_pengguna.php?status=sukses");
                exit();
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Terjadi kesalahan saat menyimpan data.</div>';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>

<!-- Tampilkan pesan jika ada -->
<?php echo $message; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Tambah Pengguna</h2>

    <form action="tambah_pengguna.php" method="POST">
        <!-- Nama Lengkap -->
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" required>
        </div>

        <!-- Peran (Role) -->
        <div class="mb-6">
            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Peran (Role)</label>
            <select id="role" name="role" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                <option value="mahasiswa">Mahasiswa</option>
                <option value="asisten">Asisten</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end">
            <a href="manage_pengguna.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Batal</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Simpan Pengguna</button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>