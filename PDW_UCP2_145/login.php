<?php
session_start();
require_once 'config.php';

// --- LOGIKA PHP (Tidak Diubah) ---
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: mahasiswa/dashboard.php");
    }
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $message = "Email dan password harus diisi!";
    } else {
        $sql = "SELECT id, nama, email, password, role FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['nama'] = $user['nama'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] == 'asisten') {
                    header("Location: asisten/dashboard.php");
                    exit();
                } elseif ($user['role'] == 'mahasiswa') {
                    header("Location: mahasiswa/dashboard.php");
                    exit();
                } else {
                    $message = "Peran pengguna tidak valid.";
                }
            } else {
                $message = "Password yang Anda masukkan salah.";
            }
        } else {
            $message = "Akun dengan email tersebut tidak ditemukan.";
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
    <title>Login - SIMPRAK</title>
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

        .login-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
            transform: translateY(0);
            transition: all 0.4s ease;
            border: 1px solid #e5e7eb; /* border-gray-200 */
        }

        .login-container:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px rgba(0, 0, 0, 0.1);
        }

        .logo-section {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-icon {
            display: inline-flex;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8); /* blue-500 to blue-700 */
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(59, 130, 246, 0.2);
            transition: all 0.3s ease;
        }

        .logo-icon:hover {
            transform: scale(1.1);
        }

        .logo-icon i {
            font-size: 35px;
            color: white;
        }

        .welcome-text {
            text-align: center;
            margin-bottom: 30px;
        }

        .welcome-text h2 {
            color: #1e3a8a; /* text-blue-900 */
            font-size: 2.5em;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .welcome-text p {
            color: #4b5563; /* text-gray-600 */
            font-size: 1em;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: #1e40af; /* text-blue-800 */
            font-weight: 600;
        }

        .input-container {
            position: relative;
        }

        .form-group input {
            width: 100%;
            padding: 16px 20px 16px 50px;
            border: 1px solid #d1d5db; /* border-gray-300 */
            border-radius: 10px;
            font-size: 16px;
            background: #f9fafb; /* bg-gray-50 */
            transition: all 0.3s ease;
            outline: none;
            color: #1f2937;
        }

        .form-group input::placeholder {
            color: #9ca3af; /* text-gray-400 */
        }

        .form-group input:focus {
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
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .form-group input:focus + .input-icon {
            color: #3b82f6; /* blue-500 */
        }

        .login-btn {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8); /* blue-500 to blue-700 */
            color: #ffffff;
            padding: 18px 24px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 15px rgba(59, 130, 246, 0.2);
        }
        
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 25px rgba(59, 130, 246, 0.3);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn i {
            margin-right: 8px;
        }

        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            animation: slideInDown 0.5s ease;
            border: 1px solid transparent;
        }

        .message.error {
            background-color: #fee2e2; /* bg-red-100 */
            color: #991b1b; /* text-red-800 */
            border-color: #fca5a5; /* border-red-300 */
        }

        .message.success {
            background-color: #dcfce7; /* bg-green-100 */
            color: #15803d; /* text-green-800 */
            border-color: #86efac; /* border-green-300 */
        }

        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-link {
            text-align: center;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e5e7eb; /* border-gray-200 */
        }

        .register-link p {
            color: #4b5563; /* text-gray-600 */
            font-size: 14px;
            margin-bottom: 12px;
        }

        .register-link a {
            color: #2563eb; /* text-blue-600 */
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            text-decoration: underline;
            color: #1e40af; /* text-blue-800 */
        }

        .loading .login-btn {
            opacity: 0.7;
            pointer-events: none;
        }

        @media (max-width: 480px) {
            .login-container { padding: 40px 25px; margin: 10px; }
            .welcome-text h2 { font-size: 2em; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-section">
            <div class="logo-icon">
                <i class="fas fa-book-open"></i>
            </div>
        </div>
        
        <div class="welcome-text">
            <h2>Selamat Datang</h2>
            <p>Masuk ke akun SIMPRAK Anda</p>
        </div>
        
        <?php 
            if (isset($_GET['status']) && $_GET['status'] == 'registered') {
                echo '<div class="message success"><i class="fas fa-check-circle"></i> Registrasi berhasil! Silakan login.</div>';
            }
            if (!empty($message)) {
                echo '<div class="message error"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($message) . '</div>';
            }
        ?>
        
        <form action="login.php" method="post" id="loginForm">
            <div class="form-group">
                <label for="email">Alamat Email</label>
                <div class="input-container">
                    <input type="email" id="email" name="email" required placeholder="contoh@email.com">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-container">
                    <input type="password" id="password" name="password" required placeholder="Masukkan password Anda">
                    <i class="fas fa-lock input-icon"></i>
                </div>
            </div>
            
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Masuk ke Akun
            </button>
        </form>
        
        <div class="register-link">
            <p>Belum memiliki akun?</p>
            <a href="register.php">
                <i class="fas fa-user-plus"></i> Daftar Sekarang
            </a>
        </div>
    </div>

    <script>
        // Javascript untuk fungsionalitas (tidak perlu diubah)
        document.getElementById('loginForm').addEventListener('submit', function() {
            this.classList.add('loading');
        });

        const messages = document.querySelectorAll('.message');
        messages.forEach(message => {
            setTimeout(() => {
                message.style.transition = 'opacity 0.5s, transform 0.5s';
                message.style.opacity = '0';
                message.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    message.style.display = 'none';
                }, 500);
            }, 5000);
        });
    </script>
</body>
</html>