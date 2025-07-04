<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Variabel dan Path (Tidak Diubah) ---
$pageTitle = 'Daftar Praktikum';
$activePage = 'katalog';
$header_path = __DIR__ . '/templates/header_mahasiswa.php';
$footer_path = __DIR__ . '/templates/footer_mahasiswa.php';

require_once __DIR__ . '/../config.php';

$message = '';
$message_type = ''; // 'success' atau 'error'

// --- Logika PHP untuk Pendaftaran (Tidak Diubah) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['daftar'])) {
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
        $message = "Anda harus login sebagai mahasiswa untuk mendaftar praktikum.";
        $message_type = 'error';
    } else {
        $mahasiswa_id = $_SESSION['user_id'];
        $praktikum_id = $_POST['praktikum_id'];

        $sql_check = "SELECT id FROM pendaftaran_praktikum WHERE mahasiswa_id = ? AND praktikum_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $mahasiswa_id, $praktikum_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $message = "Anda sudah terdaftar pada praktikum ini.";
            $message_type = 'error';
        } else {
            $sql_insert = "INSERT INTO pendaftaran_praktikum (mahasiswa_id, praktikum_id) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ii", $mahasiswa_id, $praktikum_id);
            if ($stmt_insert->execute() && $stmt_insert->affected_rows > 0) {
                $message = "Berhasil mendaftar praktikum!";
                $message_type = 'success';
            } else {
                $message = "Gagal mendaftar. Coba lagi.";
                $message_type = 'error';
            }
        }
    }
}

// --- Logika PHP untuk Mengambil Data (Tidak Diubah) ---
$sql = "SELECT id, nama_praktikum, deskripsi, created_at FROM mata_praktikum ORDER BY created_at DESC";
$result = $conn->query($sql);

$praktikum_diikuti = [];
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa') {
    $mahasiswa_id = $_SESSION['user_id'];
    $sql_diikuti = "SELECT praktikum_id FROM pendaftaran_praktikum WHERE mahasiswa_id = ?";
    $stmt_diikuti = $conn->prepare($sql_diikuti);
    $stmt_diikuti->bind_param("i", $mahasiswa_id);
    $stmt_diikuti->execute();
    $result_diikuti = $stmt_diikuti->get_result();

    while ($row = $result_diikuti->fetch_assoc()) {
        $praktikum_diikuti[] = $row['praktikum_id'];
    }
}

// --- Pengecekan Header (Tidak Diubah) ---
if (file_exists($header_path)) {
    include_once $header_path;
} else {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f0f4f8; border: 1px solid #dbeafe; color: #1e3a8a;'>
        <strong>Error:</strong> File <code>header_mahasiswa.php</code> tidak ditemukan.
    </div>");
}
?>

<div class="bg-gray-50 text-slate-800 min-h-screen p-4 sm:p-6 lg:p-8">

<div class="bg-white border border-gray-200 p-8 rounded-3xl shadow-sm mb-8">
    <div class="flex items-center">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-4 rounded-2xl mr-6 shadow-lg shadow-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </div>
        <div>
            <h2 class="text-4xl font-bold text-blue-900">
                Daftar Mata Praktikum
            </h2>
            <p class="text-gray-600 mt-2 text-lg">Pilih mata praktikum yang ingin Anda ikuti.</p>
        </div>
    </div>
</div>

<?php if (!empty($message)): ?>
    <?php
    $baseClasses = 'border px-4 py-3 rounded-xl mb-6 relative flex items-center shadow-md';
    if ($message_type === 'success') {
        $alertClasses = 'bg-green-100 border-green-300 text-green-800';
        $iconColor = 'text-green-500';
        $iconPath = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />';
    } else {
        $alertClasses = 'bg-red-100 border-red-300 text-red-800';
        $iconColor = 'text-red-500';
        $iconPath = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />';
    }
    ?>
    <div class="<?php echo $baseClasses; ?> <?php echo $alertClasses; ?>">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-3 <?php echo $iconColor; ?>" viewBox="0 0 20 20" fill="currentColor">
            <?php echo $iconPath; ?>
        </svg>
        <span class="font-medium"><?php echo $message; ?></span>
        <button class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-500 hover:text-gray-800" onclick="this.parentElement.style.display='none'">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
<?php endif; ?>

<?php if ($result->num_rows > 0): ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="bg-white border border-gray-200 p-6 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col">
                <div class="flex-grow">
                    <div class="flex items-start mb-4">
                        <div class="bg-blue-100 p-3 rounded-xl mr-4 border border-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-blue-800 mb-1"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                            <p class="text-sm text-gray-500">Dibuat: <?php echo date('d M Y', strtotime($row['created_at'])); ?></p>
                        </div>
                    </div>
                    <p class="text-gray-600 mb-6 pl-4 border-l-4 border-gray-200 text-base leading-relaxed"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                </div>
                
                <div class="mt-auto pt-4 border-t border-gray-100">
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'mahasiswa'): ?>
                        <?php if (in_array($row['id'], $praktikum_diikuti)): ?>
                            <button class="w-full bg-gray-100 text-green-700 font-semibold py-3 px-4 rounded-xl flex items-center justify-center cursor-not-allowed border border-gray-200" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                                Sudah Terdaftar
                            </button>
                        <?php else: ?>
                            <form method="POST">
                                <input type="hidden" name="praktikum_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="daftar" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-xl flex items-center justify-center transition-all duration-300 shadow-md hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-blue-300">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Daftar Praktikum
                                </button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                            <a href="../login.php" class="text-blue-600 hover:text-blue-800 font-semibold flex items-center justify-center transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                </svg>
                                Login untuk mendaftar
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="text-center bg-white border-2 border-dashed border-gray-200 p-12 rounded-2xl">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-2xl font-bold text-slate-700">Belum Ada Mata Praktikum</p>
        <p class="text-lg mt-2 text-slate-500">Silakan kembali lagi nanti untuk melihat daftar yang tersedia.</p>
    </div>
<?php endif; ?>

</div> <?php
// --- Logika Penutup (Tidak Diubah) ---
$conn->close();

if (file_exists($footer_path)) {
    include_once $footer_path;
} else {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f0f4f8; border: 1px solid #dbeafe; color: #1e3a8a;'>
        <strong>Error:</strong> File <code>footer_mahasiswa.php</code> tidak ditemukan.
    </div>");
}
?>