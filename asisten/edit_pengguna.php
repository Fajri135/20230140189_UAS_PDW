<?php
// 1. Definisi Variabel untuk Template
$pageTitle = 'Edit Pengguna';
$activePage = 'pengguna'; 

// 2. Panggil Header dan Konfigurasi
require_once 'templates/header.php';
require_once '../config.php';

// 3. Cek ID pengguna di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage_pengguna.php?status=gagal");
    exit();
}
$id_pengguna = (int)$_GET['id'];

$message = '';

// Ambil data pengguna saat ini
$stmt_select = $conn->prepare("SELECT nama, email, role FROM users WHERE id = ?");
$stmt_select->bind_param("i", $id_pengguna);
$stmt_select->execute();
$pengguna = $stmt_select->get_result()->fetch_assoc();
$stmt_select->close();

if (!$pengguna) {
    header("Location: manage_pengguna.php?status=notfound");
    exit();
}

// 4. Logika untuk memproses form saat disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $password = trim($_POST['password']);

    // Validasi
    if (empty($nama) || empty($email) || empty($role)) {
        $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Nama, Email, dan Peran wajib diisi!</div>';
    } else {
        // Cek duplikasi email (jika email diubah)
        if ($email != $pengguna['email']) {
            $stmt_check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt_check->bind_param("s", $email);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Email sudah terdaftar.</div>';
            }
            $stmt_check->close();
        }

        if (empty($message)) {
            // Cek apakah password diisi untuk diubah
            if (!empty($password)) {
                // Jika password baru diisi, update semua termasuk password
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $sql_update = "UPDATE users SET nama = ?, email = ?, role = ?, password = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssssi", $nama, $email, $role, $hashed_password, $id_pengguna);
            } else {
                // Jika password kosong, update data selain password
                $sql_update = "UPDATE users SET nama = ?, email = ?, role = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("sssi", $nama, $email, $role, $id_pengguna);
            }

            if ($stmt_update->execute()) {
                header("Location: manage_pengguna.php?status=sukses");
                exit();
            } else {
                $message = '<div class="mb-4 p-4 rounded-md bg-red-500 text-white">Terjadi kesalahan saat memperbarui data.</div>';
            }
            $stmt_update->close();
        }
    }
}
?>

<!-- Tampilkan pesan jika ada -->
<?php echo $message; ?>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">Form Edit Pengguna</h2>

    <form action="edit_pengguna.php?id=<?php echo $id_pengguna; ?>" method="POST">
        <!-- Nama Lengkap -->
        <div class="mb-4">
            <label for="nama" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="<?php echo htmlspecialchars($pengguna['nama']); ?>" required>
        </div>

        <!-- Email -->
        <div class="mb-4">
            <label for="email" class="block text-gray-700 text-sm font-bold mb-2">Email</label>
            <input type="email" id="email" name="email" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="<?php echo htmlspecialchars($pengguna['email']); ?>" required>
        </div>

        <!-- Password -->
        <div class="mb-4">
            <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password Baru (Opsional)</label>
            <input type="password" id="password" name="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="Kosongkan jika tidak ingin mengubah password">
        </div>

        <!-- Peran (Role) -->
        <div class="mb-6">
            <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Peran (Role)</label>
            <select id="role" name="role" class="shadow border rounded w-full py-2 px-3 text-gray-700" required>
                <option value="mahasiswa" <?php echo ($pengguna['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                <option value="asisten" <?php echo ($pengguna['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
            </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex items-center justify-end">
            <a href="manage_pengguna.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg mr-2">Batal</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Update Pengguna</button>
        </div>
    </form>
</div>

<?php
$conn->close();
require_once 'templates/footer.php';
?>