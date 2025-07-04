<?php
// Pastikan session sudah dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek jika pengguna belum login atau bukan mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Panel Mahasiswa - <?php echo $pageTitle ?? 'SIMPRAK'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

    <nav class="bg-gradient-to-r from-blue-900 via-blue-800 to-blue-900 shadow-xl border-b-4 border-blue-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="dashboard.php" class="text-blue-50 text-2xl font-bold tracking-wide hover:text-blue-100 transition-colors duration-300">SIMPRAK</a>
                    </div>
                    <div class="hidden md:block">
                        <div class="ml-10 flex items-baseline space-x-4">
                            <?php 
                                // Menyiapkan class untuk link aktif dan tidak aktif dengan tema biru tua
                                $activeClass = 'bg-blue-700 text-blue-50 shadow-lg border-b-2 border-blue-300';
                                $inactiveClass = 'text-blue-100 hover:bg-blue-700 hover:text-blue-50 hover:shadow-md';
                            ?>
                            <a href="dashboard.php" class="<?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-md text-sm font-medium transition-all duration-300">Dashboard</a>
                            <a href="my_courses.php" class="<?php echo ($activePage == 'my_courses') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-md text-sm font-medium transition-all duration-300">Praktikum Saya</a>
                            <a href="detail_praktikum.php?id=1" class="<?php echo ($activePage == 'lihat detail') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-md text-sm font-medium transition-all duration-300">Detail Praktikum</a>
                            <a href="../katalog.php" class="<?php echo ($activePage == 'katalog') ? $activeClass : $inactiveClass; ?> px-4 py-2 rounded-md text-sm font-medium transition-all duration-300">Cari Praktikum</a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:block">
                    <div class="ml-4 flex items-center md:ml-6">
                        <a href="../logout.php" class="bg-blue-600 hover:bg-blue-500 text-blue-50 font-bold py-2 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 border border-blue-500 hover:border-blue-400">
                            Logout
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6 lg:p-8">