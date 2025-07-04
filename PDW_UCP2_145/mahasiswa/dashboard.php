<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Variabel dan Path (Tidak Diubah) ---
$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$header_path = __DIR__ . '/templates/header_mahasiswa.php';
$footer_path = __DIR__ . '/templates/footer_mahasiswa.php';

require_once __DIR__ . '/../config.php';

// --- Pengecekan Header (Tidak Diubah) ---
if (file_exists($header_path)) {
    include_once $header_path;
} else {
    die("<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f0f4f8; border: 1px solid #dbeafe; color: #1e3a8a;'>
             <strong>Error:</strong> File <code>header_mahasiswa.php</code> tidak ditemukan di folder <code>mahasiswa/templates/</code>.
         </div>");
}

// --- Koneksi Database (Tidak Diubah) ---
if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}

// --- Pengambilan Data dari Session (Tidak Diubah) ---
$mahasiswa_id = $_SESSION['user_id'];
$nama_mahasiswa = $_SESSION['nama'];

// --- Logika PHP untuk Statistik (Tidak Diubah) ---
$stmt_praktikum = $conn->prepare("SELECT COUNT(*) as total FROM pendaftaran_praktikum WHERE mahasiswa_id = ?");
$stmt_praktikum->bind_param("i", $mahasiswa_id);
$stmt_praktikum->execute();
$total_praktikum = $stmt_praktikum->get_result()->fetch_assoc()['total'];
$stmt_praktikum->close();

$stmt_selesai = $conn->prepare("SELECT COUNT(*) as total FROM laporan_praktikum WHERE mahasiswa_id = ? AND nilai IS NOT NULL");
$stmt_selesai->bind_param("i", $mahasiswa_id);
$stmt_selesai->execute();
$total_selesai = $stmt_selesai->get_result()->fetch_assoc()['total'];
$stmt_selesai->close();

$stmt_menunggu = $conn->prepare("SELECT COUNT(*) as total FROM laporan_praktikum WHERE mahasiswa_id = ? AND nilai IS NULL");
$stmt_menunggu->bind_param("i", $mahasiswa_id);
$stmt_menunggu->execute();
$total_menunggu = $stmt_menunggu->get_result()->fetch_assoc()['total'];
$stmt_menunggu->close();

// --- Logika PHP untuk Notifikasi (Tidak Diubah) ---
$sql_notif = "SELECT lp.praktikum_id, m.nama_modul, lp.nilai, lp.submitted_at
              FROM laporan_praktikum lp
              JOIN modul_praktikum m ON lp.modul_id = m.id
              WHERE lp.mahasiswa_id = ? AND lp.nilai IS NOT NULL
              ORDER BY lp.submitted_at DESC
              LIMIT 3";
$stmt_notif = $conn->prepare($sql_notif);
$stmt_notif->bind_param("i", $mahasiswa_id);
$stmt_notif->execute();
$notifikasi_list = $stmt_notif->get_result();
$stmt_notif->close();
?>

<div class="bg-gray-50 text-slate-800 min-h-screen p-4 sm:p-6 lg:p-8">

<div class="bg-white border border-gray-200 p-8 rounded-3xl shadow-sm mb-10">
    <div class="flex items-center">
        <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-4 rounded-2xl mr-6 shadow-lg shadow-blue-200">
            <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-4xl md:text-5xl font-bold text-blue-900">
                Halo, <?php echo htmlspecialchars(strtok($nama_mahasiswa, ' ')); ?> 👋
            </h1>
            <p class="text-gray-600 text-lg mt-2">
                Selamat datang kembali di <span class="font-bold text-blue-700">SIMPRAK</span>. Semangat terus menyelesaikan tugasmu!
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
    <?php
    // [PERUBAHAN] Fungsi untuk membuat kartu statistik disederhanakan dan didesain ulang
    function createStatCard($iconPath, $title, $value, $description, $colors) {
        $html = <<<HTML
        <div class="bg-white border border-gray-200 p-6 rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:shadow-{$colors['shadow']}-200">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider">{$title}</p>
                    <p class="text-4xl font-bold text-slate-800 mt-2">{$value}</p>
                    <p class="text-gray-500 mt-1">{$description}</p>
                </div>
                <div class="bg-gradient-to-br {$colors['gradient_icon_bg']} text-white w-16 h-16 rounded-xl flex items-center justify-center shadow-lg shadow-{$colors['shadow']}-300">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        {$iconPath}
                    </svg>
                </div>
            </div>
        </div>
HTML;
        echo $html;
    }

    // [PERUBAHAN] Array warna disesuaikan untuk tema cerah
    $praktikumColors = [
        'gradient_icon_bg' => 'from-blue-500 to-blue-700',
        'shadow' => 'blue',
    ];

    $selesaiColors = [
        'gradient_icon_bg' => 'from-green-500 to-green-700',
        'shadow' => 'green',
    ];

    $menungguColors = [
        'gradient_icon_bg' => 'from-amber-400 to-amber-600',
        'shadow' => 'amber',
    ];
    
    // Pemanggilan fungsi (Tidak Diubah)
    createStatCard('<path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>', 'Praktikum Diikuti', $total_praktikum, 'Total pembelajaran', $praktikumColors);
    createStatCard('<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>', 'Tugas Selesai', $total_selesai, 'Pencapaian', $selesaiColors);
    createStatCard('<path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm-1-5h2v2h-2zm0-8h2v6h-2z"/>', 'Tugas Menunggu', $total_menunggu, 'Perlu diselesaikan', $menungguColors);
    ?>
</div>

<div class="bg-white border border-gray-200 p-8 rounded-3xl shadow-sm">
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-blue-900">Notifikasi Terbaru</h2>
        <p class="text-gray-500 mt-1">Informasi terkini untuk Anda.</p>
    </div>

    <div class="space-y-4">
        <?php if ($notifikasi_list && $notifikasi_list->num_rows > 0): ?>
            <?php while($notif = $notifikasi_list->fetch_assoc()): ?>
                <div class="flex items-center p-4 bg-gray-50 rounded-2xl border border-gray-200 transition-all duration-300 hover:border-blue-300 hover:bg-blue-50">
                    <div class="flex-shrink-0 w-10 h-10 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-5">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    <div class="flex-grow text-gray-700">
                        Nilai untuk 
                        <a href="detail_praktikum.php?id=<?php echo $notif['praktikum_id']; ?>" class="font-semibold text-blue-700 hover:underline">
                            <?php echo htmlspecialchars($notif['nama_modul']); ?>
                        </a> 
                        telah diberikan.
                    </div>
                    <div class="text-sm text-gray-500 ml-4 text-right whitespace-nowrap">
                        <?php echo date('d M Y, H:i', strtotime($notif['submitted_at'])); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-12 px-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                <div class="bg-gray-200 text-gray-400 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <p class="text-slate-700 text-xl font-semibold mb-2">Tidak ada notifikasi terbaru</p>
                <p class="text-slate-500">Pembaruan nilai atau informasi lainnya akan muncul di sini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

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