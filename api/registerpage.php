<?php
session_start();
if (isset($_SESSION["nama"])) {
    header("Location: tugasweb.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register CePaT</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .register-container {
            width: 100%;
            max-width: 450px;
            padding: 2.5rem;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.08);
        }

        .btn-custom-green {
            background-color: #4caf50;
            border-color: #4caf50;
            color: #ffffff;
            font-weight: 500;
        }

        .btn-custom-green:hover {
            background-color: #43a047;
            border-color: #43a047;
            color: #ffffff;
        }

        .text-custom-green {
            color: #4caf50;
        }
        
        .text-custom-green:hover {
            color: #43a047;
        }

        .logo-area {
            gap: 15px;
        }
        
        .logo-leaf-icon {
            height: 45px;
            width: auto;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <div class="text-center mb-4">
            <div class="logo-area d-flex align-items-center justify-content-center mb-2">
                <div class="text-start">
                    <img src="../logocepat.png" alt="Logo CePaT" class="logo-leaf-icon img-fluid mb-1">
                </div>
                <h1 class="text-custom-green fw-bold mb-0" style="font-size: 2.8rem;">CePaT</h1>
            </div>
            <p class="text-muted mt-2">Buat akun baru untuk mulai menggunakan layanan</p>
        </div>

        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ACTION diarahkan ke prosesregister.php, method POST -->
        <form action="prosesregister.php" method="POST">
            <div class="mb-3">
                <label for="namaInput" class="form-label">Nama Lengkap</label>
                <!-- name="nama" wajib ada agar bisa dibaca $_POST["nama"] -->
                <input type="text" class="form-control" id="namaInput" name="nama" placeholder="Masukkan nama lengkap Anda" required>
            </div>

            <div class="mb-3">
                <label for="usernameInput" class="form-label">Username</label>
                <!-- name="username" -->
                <input type="text" class="form-control" id="usernameInput" name="username" placeholder="Buat username Anda" required>
            </div>
            
            <div class="mb-3">
                <label for="emailInput" class="form-label">Email Address</label>
                <!-- name="email" -->
                <input type="email" class="form-control" id="emailInput" name="email" placeholder="Masukkan email valid Anda" required>
            </div>
            
            <div class="mb-3">
                <label for="passwordInput" class="form-label">Password</label>
                <!-- name="password" -->
                <input type="password" class="form-control" id="passwordInput" name="password" placeholder="Buat password yang kuat" required>
            </div>

            <div class="mb-4">
                <label for="confirmPasswordInput" class="form-label">Konfirmasi Password</label>
                <!-- name="confirmPassword" -->
                <input type="password" class="form-control" id="confirmPasswordInput" name="confirmPassword" placeholder="Ulangi password Anda" required>
            </div>
            
            <button type="submit" class="btn btn-custom-green w-100 py-2 mb-3">DAFTAR SEKARANG</button>
            
            <div class="text-center">
                <p class="mb-0" style="font-size: 0.95rem;">Sudah punya akun? 
                    <a href="loginpage.php" class="text-decoration-none text-custom-green fw-bold">Login di sini</a>
                </p>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>