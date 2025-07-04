<?php
require_once 'config.php';

$message = '';

// --- LOGIKA PHP (Tidak Diubah) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

    // Validasi sederhana
    if (empty($nama) || empty($email) || empty($password) || empty($role)) {
        $message = "Semua kolom harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Format email tidak valid!";
    } elseif (!in_array($role, ['mahasiswa', 'asisten'])) {
        $message = "Peran tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Email sudah terdaftar. Silakan gunakan email lain.";
        } else {
            // Hash password untuk keamanan
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke database
            $sql_insert = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $nama, $email, $hashed_password, $role);

            if ($stmt_insert->execute()) {
                header("Location: login.php?status=registered");
                exit();
            } else {
                $message = "Terjadi kesalahan. Silakan coba lagi.";
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi - SIMPRAK</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6; /* bg-gray-100 */
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            color: #1f2937; /* text-slate-800 */
        }

        .container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
            border: 1px solid #e5e7eb; /* border-gray-200 */
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            color: #1e3a8a; /* text-blue-900 */
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .header p {
            color: #4b5563; /* text-gray-600 */
            font-size: 1em;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #1e40af; /* text-blue-800 */
            font-weight: 600;
        }

        .input-wrapper {
            position: relative;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: 1px solid #d1d5db; /* border-gray-300 */
            border-radius: 10px;
            font-size: 16px;
            background: #f9fafb; /* bg-gray-50 */
            transition: all 0.3s ease;
            outline: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
            background: #ffffff;
        }
        
        .input-icon {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; /* text-gray-400 */
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .form-group input:focus + .input-icon,
        .form-group select:focus + .input-icon {
            color: #3b82f6; /* blue-500 */
        }
        
        /* Arrow for select */
        .select-wrapper::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
        }

        .btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8); /* blue-500 to blue-700 */
            color: white;
            padding: 16px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.3);
        }

        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            animation: slideInDown 0.5s ease;
            border: 1px solid #fca5a5; /* border-red-300 */
            background-color: #fee2e2; /* bg-red-100 */
            color: #991b1b; /* text-red-800 */
        }
        
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb; /* border-gray-200 */
        }

        .login-link p {
            color: #4b5563; /* text-gray-600 */
            font-size: 14px;
            margin-bottom: 10px;
        }

        .login-link a {
            color: #2563eb; /* text-blue-600 */
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            text-decoration: underline;
            color: #1e40af; /* text-blue-800 */
        }

        @media (max-width: 480px) {
            .container { padding: 30px 20px; }
            .header h2 { font-size: 2em; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Buat Akun Baru</h2>
            <p>Gabung bersama kami di SIMPRAK</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form action="register.php" method="post" id="registerForm">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <div class="input-wrapper">
                    <input type="text" id="nama" name="nama" required placeholder="Masukkan nama lengkap Anda">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <div class="input-wrapper">
                    <input type="email" id="email" name="email" required placeholder="contoh@email.com">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" required placeholder="Buat password yang kuat">
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="role">Daftar Sebagai</label>
                <div class="input-wrapper select-wrapper">
                    <select id="role" name="role" required>
                        <option value="" disabled selected>Pilih peran Anda</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="asisten">Asisten Praktikum</option>
                    </select>
                    <i class="fas fa-user-graduate input-icon"></i>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </button>
        </form>
        
        <div class="login-link">
            <p>Sudah punya akun?</p>
            <a href="login.php">
                <i class="fas fa-sign-in-alt"></i> Login di Sini
            </a>
        </div>
    </div>

    <script>
        // Javascript opsional untuk menambahkan kelas saat form disubmit (untuk loading state, dll.)
        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = this.querySelector('.btn');
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Mendaftar...';
            btn.disabled = true;
        });
    </script>
</body>
</html>