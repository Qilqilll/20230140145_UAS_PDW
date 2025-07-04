<?php
// filepath: c:\xampp\htdocs\tugas\tugas\mahasiswa\detail_praktikum.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';

// --- Variabel dan Path (Tidak Diubah) ---
$pageTitle = 'Detail Praktikum';
$activePage = 'lihat detail';
$header_path = __DIR__ . '/templates/header_mahasiswa.php';
$footer_path = __DIR__ . '/templates/footer_mahasiswa.php';

// --- Logika PHP (Tidak Diubah) ---
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header('Location: ../login.php');
    exit;
}

$mahasiswa_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT mp.id, mp.nama_praktikum, mp.deskripsi 
                        FROM mata_praktikum mp
                        JOIN pendaftaran_praktikum pp ON mp.id = pp.praktikum_id
                        WHERE pp.mahasiswa_id = ?");
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$praktikum_result = $stmt->get_result();
$stmt->close();

$praktikum_list = [];
while ($praktikum = $praktikum_result->fetch_assoc()) {
    $praktikum_list[] = $praktikum;
}

$modul_map = [];
foreach ($praktikum_list as $praktikum) {
    $stmt = $conn->prepare("SELECT id, nama_modul, file_materi FROM modul_praktikum WHERE praktikum_id = ?");
    $stmt->bind_param("i", $praktikum['id']);
    $stmt->execute();
    $modul_result = $stmt->get_result();
    while ($modul = $modul_result->fetch_assoc()) {
        $modul_map[$praktikum['id']][] = $modul;
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT modul_id, file_laporan, nilai FROM laporan_praktikum WHERE mahasiswa_id = ?");
$stmt->bind_param("i", $mahasiswa_id);
$stmt->execute();
$laporan_result = $stmt->get_result();

$laporan_data = [];
while ($row = $laporan_result->fetch_assoc()) {
    $laporan_data[$row['modul_id']] = $row;
}
$stmt->close();

if (file_exists($header_path)) {
    include_once $header_path;
}
?>

<div class="bg-gray-50 text-slate-800 min-h-screen p-4 sm:p-6 lg:p-8">

    <?php if (isset($_GET['status'])): ?>
        <div class="mb-6 p-4 rounded-xl flex items-center shadow-md <?php echo $_GET['status'] === 'laporan_deleted' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'; ?>">
            <svg class="h-6 w-6 mr-3 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">
                <?php 
                    if ($_GET['status'] === 'laporan_deleted') echo 'Pengumpulan laporan berhasil dibatalkan.';
                    if ($_GET['status'] === 'laporan_edited') echo 'Laporan berhasil diubah.';
                ?>
            </span>
        </div>
    <?php endif; ?>

    <?php if (count($praktikum_list) === 0): ?>
        <div class="text-center bg-white border-2 border-dashed border-gray-200 p-12 rounded-2xl">
            <svg class="h-16 w-16 mx-auto text-gray-400 mb-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
            <p class="text-2xl font-bold text-slate-700">Anda Belum Terdaftar</p>
            <p class="text-lg mt-2 text-slate-500">Silakan daftar ke salah satu mata praktikum untuk melihat detailnya di sini.</p>
        </div>
    <?php else: ?>
        <?php foreach ($praktikum_list as $praktikum): ?>
            <div class="bg-white border border-gray-200 rounded-3xl shadow-sm p-6 md:p-8 mb-10">
                <div class="mb-8 pb-6 border-b border-gray-200">
                    <h2 class="text-3xl font-bold text-blue-900 mb-2"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></h2>
                    <p class="text-gray-600 text-lg"><?php echo htmlspecialchars($praktikum['deskripsi']); ?></p>
                </div>

                <div class="space-y-8">
                    <h3 class="text-xl font-semibold text-slate-700">Daftar Modul</h3>
                    <?php if (!empty($modul_map[$praktikum['id']])): ?>
                        <?php foreach ($modul_map[$praktikum['id']] as $modul): ?>
                            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-200">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div class="md:col-span-1">
                                        <h4 class="text-lg font-bold text-blue-800 mb-2"><?php echo htmlspecialchars($modul['nama_modul']); ?></h4>
                                        <?php if (!empty($modul['file_materi'])): ?>
                                            <a href="../uploads/materi/<?php echo htmlspecialchars($modul['file_materi']); ?>" class="inline-flex items-center text-blue-600 hover:underline font-semibold" download>
                                                <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                Unduh Materi
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400 italic">Belum ada file materi</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="md:col-span-2">
                                        <?php if (isset($laporan_data[$modul['id']])): 
                                            $laporan = $laporan_data[$modul['id']];
                                        ?>
                                            <div class="bg-white p-4 rounded-xl border border-gray-200">
                                                <div class="flex flex-wrap items-center justify-between gap-4">
                                                    <div>
                                                        <p class="text-green-700 font-semibold flex items-center">
                                                            <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
                                                            Laporan Terkumpul
                                                        </p>
                                                        <p class="text-gray-600 mt-1">Nilai: <strong class="text-slate-800"><?php echo $laporan['nilai'] ?? 'Belum dinilai'; ?></strong></p>
                                                        <p class="text-sm text-gray-500 mt-1 truncate">File: <?php echo htmlspecialchars($laporan['file_laporan']); ?></p>
                                                    </div>
                                                    <div class="flex items-center gap-3 flex-shrink-0">
                                                        <button type="button" onclick="showEditForm('<?php echo $modul['id']; ?>')" class="px-4 py-2 rounded-lg font-semibold text-sm bg-amber-100 text-amber-800 hover:bg-amber-200 transition-colors">Edit</button>
                                                        <button type="button" onclick="showBatalModal('<?php echo $modul['id']; ?>')" class="px-4 py-2 rounded-lg font-semibold text-sm bg-red-100 text-red-800 hover:bg-red-200 transition-colors">Batal</button>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <form method="POST" action="upload_laporan.php" enctype="multipart/form-data" class="flex flex-wrap items-center gap-3">
                                                <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                                                <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                                                <input type="file" name="file_laporan" required class="flex-grow w-full sm:w-auto text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors cursor-pointer">
                                                <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg transition-colors shadow-sm">Upload</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-gray-500 p-6 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">Belum ada modul pada praktikum ini.</div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php foreach ($praktikum_list as $praktikum): ?>
    <?php if (!empty($modul_map[$praktikum['id']])): ?>
        <?php foreach ($modul_map[$praktikum['id']] as $modul): ?>
            <div id="modal-batal-<?php echo $modul['id']; ?>" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4">
                <div class="bg-white rounded-2xl p-6 shadow-xl w-full max-w-md transform transition-all">
                    <h3 class="text-lg font-bold mb-2 text-red-800">Konfirmasi Pembatalan</h3>
                    <p class="text-gray-600 mb-6">Apakah Anda yakin ingin membatalkan pengumpulan laporan untuk modul ini? Tindakan ini tidak dapat diurungkan.</p>
                    <form method="POST" action="batal_laporan.php">
                        <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeBatalModal('<?php echo $modul['id']; ?>')" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold">Tidak</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-bold">Iya, Batalkan</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="modal-edit-<?php echo $modul['id']; ?>" class="fixed inset-0 bg-black bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50 hidden p-4">
                <div class="bg-white rounded-2xl p-6 shadow-xl w-full max-w-md transform transition-all">
                    <h3 class="text-lg font-bold mb-4 text-blue-800">Edit Laporan</h3>
                    <form method="POST" action="edit_laporan.php" enctype="multipart/form-data">
                        <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                        <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                        <div class="mb-4">
                            <label class="block mb-2 font-semibold text-gray-700">File Laporan Baru</label>
                            <input type="file" name="file_laporan" required class="w-full border border-gray-300 px-3 py-2 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div class="flex justify-end gap-3">
                            <button type="button" onclick="closeEditModal('<?php echo $modul['id']; ?>')" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold">Batal</button>
                            <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endforeach; ?>

<script>
// --- Javascript (Tidak Diubah) ---
function showBatalModal(modulId) {
    document.getElementById('modal-batal-' + modulId).classList.remove('hidden');
}
function closeBatalModal(modulId) {
    document.getElementById('modal-batal-' + modulId).classList.add('hidden');
}
function showEditForm(modulId) {
    document.getElementById('modal-edit-' + modulId).classList.remove('hidden');
}
function closeEditModal(modulId) {
    document.getElementById('modal-edit-' + modulId).classList.add('hidden');
}
</script>

<?php
if (file_exists($footer_path)) {
    include_once $footer_path;
}
$conn->close();
?>