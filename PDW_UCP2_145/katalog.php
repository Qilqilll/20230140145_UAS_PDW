<?php
// filepath: c:\xampp\htdocs\tugas\tugas\katalog.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';

// --- LOGIKA PHP (Tidak Diubah) ---
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$sql_practicums = "SELECT id, nama_praktikum, deskripsi, created_at FROM mata_praktikum";
$params = [];
$types = '';

if (!empty($search_query)) {
    $sql_practicums .= " WHERE nama_praktikum LIKE ?";
    $params[] = "%" . $search_query . "%";
    $types .= 's';
}
$sql_practicums .= " ORDER BY created_at DESC";

$stmt_practicums = $conn->prepare($sql_practicums);
if (!empty($params)) {
    $stmt_practicums->bind_param($types, ...$params);
}
$stmt_practicums->execute();
$all_practicums_result = $stmt_practicums->get_result();

$registered_practicum_ids = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa') {
    $mahasiswa_id = $_SESSION['user_id'];
    $sql_registered = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
    $stmt_registered = $conn->prepare($sql_registered);
    $stmt_registered->bind_param("i", $mahasiswa_id);
    $stmt_registered->execute();
    $registered_result = $stmt_registered->get_result();
    while ($row = $registered_result->fetch_assoc()) {
        $registered_practicum_ids[] = $row['praktikum_id'];
    }
    $stmt_registered->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Praktikum - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-md sticky top-0 z-10">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="katalog.php" class="text-xl font-bold text-blue-800">SIMPRAK</a>
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo $_SESSION['role']; ?>/dashboard.php" class="text-gray-600 hover:text-blue-700">Dashboard Saya</a>
                    <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-blue-700">Login</a>
                    <a href="register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <div class="text-center bg-white p-8 rounded-xl shadow-lg mb-8">
            <h1 class="text-4xl font-extrabold text-blue-900">Katalog Mata Praktikum</h1>
            <p class="text-gray-600 mt-2 max-w-2xl mx-auto">Temukan dan daftar untuk praktikum yang Anda minati. Mulai perjalanan belajar Anda bersama kami hari ini!</p>
            <form method="GET" action="katalog.php" class="mt-6 max-w-lg mx-auto">
                <div class="flex">
                    <input type="text" name="search" class="w-full px-4 py-3 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-blue-600" placeholder="Cari berdasarkan nama praktikum..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-5 rounded-r-md transition-colors">Cari</button>
                </div>
            </form>
        </div>

        <div id="ajax-alert" class="mb-4 p-4 rounded-md text-center hidden"></div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php if ($all_practicums_result->num_rows > 0): ?>
                <?php while ($praktikum = $all_practicums_result->fetch_assoc()): ?>
                    <div class="bg-white rounded-xl shadow-md p-6 flex flex-col justify-between transform hover:-translate-y-2 transition-transform duration-300">
                        <div>
                            <h3 class="text-xl font-bold text-blue-800 mb-2"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h3>
                            <p class="text-gray-600 text-sm h-20 overflow-hidden"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
                        </div>
                        <div class="mt-6">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa'): ?>
                                <?php if (in_array($praktikum['id'], $registered_practicum_ids)): ?>
                                    <span id="status-<?php echo $praktikum['id']; ?>" class="w-full block text-center bg-green-500 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed flex items-center justify-center">
                                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                                        Terdaftar
                                    </span>
                                <?php else: ?>
                                    <button type="button"
                                        onclick="showModal('<?php echo $praktikum['id']; ?>')"
                                        id="btn-daftar-<?php echo $praktikum['id']; ?>"
                                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                                        Daftar Sekarang
                                    </button>
                                    <div id="modal-<?php echo $praktikum['id']; ?>" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4">
                                        <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md">
                                            <h3 class="text-lg font-bold mb-4 text-blue-700">Konfirmasi Pendaftaran</h3>
                                            <p class="mb-6 text-gray-700">Apakah Anda yakin ingin mendaftar praktikum <b><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></b>?</p>
                                            <form id="form-daftar-<?php echo $praktikum['id']; ?>" onsubmit="return daftarPraktikumAjax(event, '<?php echo $praktikum['id']; ?>');">
                                                <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                                                <div class="flex justify-end gap-3">
                                                    <button type="button" onclick="closeModal('<?php echo $praktikum['id']; ?>')" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold">Tidak</button>
                                                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold">Iya, Daftar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="w-full block text-center bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded-lg transition-colors duration-300">
                                    Login untuk Daftar
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-12 bg-white rounded-lg shadow-md">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900">Tidak Ditemukan</h3>
                    <p class="mt-1 text-sm text-gray-500">Tidak ada mata praktikum yang sesuai dengan pencarian Anda.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function showModal(id) {
            document.getElementById('modal-' + id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById('modal-' + id).classList.add('hidden');
        }

        // AJAX daftar praktikum
        function daftarPraktikumAjax(e, id) {
            e.preventDefault();
            const form = document.getElementById('form-daftar-' + id);
            const formData = new FormData(form);
            const alertBox = document.getElementById('ajax-alert');
            const buttonContainer = document.getElementById('btn-daftar-' + id).parentElement;

            fetch('mahasiswa/daftar_praktikum.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                closeModal(id);
                alertBox.classList.remove('hidden');
                
                let alertClass = 'bg-red-100 text-red-800';
                let alertMessage = 'Terjadi kesalahan. Silakan coba lagi.';

                if (data.status === 'success') {
                    alertClass = 'bg-green-100 text-green-800';
                    alertMessage = "Pendaftaran praktikum berhasil!";
                    // Ganti tombol dengan status 'Terdaftar'
                    buttonContainer.innerHTML = `
                        <span id="status-${id}" class="w-full block text-center bg-green-500 text-white font-bold py-2 px-4 rounded-lg cursor-not-allowed flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" /></svg>
                            Terdaftar
                        </span>`;
                } else if (data.status === 'already_registered') {
                     alertMessage = "Anda sudah terdaftar pada praktikum ini.";
                } else if (data.status === 'not_mahasiswa') {
                    alertMessage = "Hanya mahasiswa yang dapat mendaftar.";
                } else if (data.status === 'not_logged_in') {
                    alertMessage = "Anda harus login untuk mendaftar.";
                }

                alertBox.className = `mb-4 p-4 rounded-md text-center ${alertClass}`;
                alertBox.innerText = alertMessage;

                setTimeout(() => {
                    alertBox.style.transition = 'opacity 0.5s';
                    alertBox.style.opacity = '0';
                    setTimeout(() => { alertBox.classList.add('hidden'); alertBox.style.opacity = '1'; }, 500);
                }, 4000);

            })
            .catch(() => {
                closeModal(id);
                alertBox.className = 'mb-4 p-4 rounded-md text-center bg-red-100 text-red-800';
                alertBox.innerText = 'Gagal terhubung ke server.';
                alertBox.classList.remove('hidden');
                setTimeout(() => {
                    alertBox.style.transition = 'opacity 0.5s';
                    alertBox.style.opacity = '0';
                    setTimeout(() => { alertBox.classList.add('hidden'); alertBox.style.opacity = '1'; }, 500);
                }, 4000);
            });
            return false;
        }
    </script>
</body>
</html>
<?php
$stmt_practicums->close();
$conn->close();
?>