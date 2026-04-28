<?php
session_start();
if (isset($_SESSION["nama"]))       
    { header("Location: tugasweb.php");    exit(); }
if (isset($_SESSION["admin_nama"])) 
    { header("Location: admindashboard.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login CePaT</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        :root {
            --green: #2e7d32;
            --green-mid: #4caf50;
            --text-dark: #333333;
            --text-muted: #666666;
            --border: #e0e0e0;
            --body-bg: #f5f7f5;
            --red: #dc2626;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--body-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 40px 30px;
        }

        .card-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo-row {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 10px;
        }

        .logo-row img {
            height: 45px; /* Sesuaikan ukuran logo */
        }

        .logo-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--green-mid);
        }

        .card-subtitle {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* Pesan Error / Success */
        .alert {
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-danger { background: #fef2f2; color: var(--red); border: 1px solid #fecaca; }
        .alert-success { background: #e8f5e9; color: var(--green); border: 1px solid #c8e6c9; }

        .fgroup {
            margin-bottom: 20px;
        }

        .flabel {
            display: block;
            font-size: 0.9rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .finput {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 6px;
            padding: 12px 15px;
            font-size: 0.9rem;
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.2s;
            background-color: #fff;
        }

        .finput::placeholder {
            color: #a0a0a0;
        }

        .finput:focus {
            border-color: var(--green-mid);
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            margin-bottom: 25px;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--text-dark);
            cursor: pointer;
        }

        .checkbox-label input[type="checkbox"] {
            accent-color: #1a73e8;
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-link {
            color: var(--green-mid);
            text-decoration: none;
        }

        .forgot-link:hover {
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: var(--green-mid);
            color: #fff;
            border: none;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 20px;
        }

        .btn-login:hover {
            background: var(--green);
        }

        .register-row {
            text-align: center;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .register-row a {
            color: var(--green-mid);
            font-weight: 600;
            text-decoration: none;
        }

        .register-row a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-header">
        <div class="logo-row">
            <img src="../logocepat.png" alt="Logo CePaT">
            <span class="logo-name">CePaT</span>
        </div>
        <p class="card-subtitle">Silakan login untuk melanjutkan</p>
    </div>

    <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
    <?php endif; ?>

    <form action="proseslogin.php" method="POST">
        <div class="fgroup">
            <label class="flabel">Email Address</label>
            <input type="email" class="finput" name="email" placeholder="Masukkan email Anda" value="<?= isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '' ?>" required autofocus>
        </div>
        
        <div class="fgroup">
            <label class="flabel">Password</label>
            <input type="password" class="finput" name="password" placeholder="Masukkan password Anda" required>
        </div>

        <div class="options-row">
            <label class="checkbox-label">
                <input type="checkbox" name="remember" <?= isset($_GET['remember']) ? 'checked' : '' ?>> Ingat Saya
            </label>
            <a href="#" class="forgot-link">Lupa Password?</a>
        </div>

        <button type="submit" class="btn-login">LOGIN</button>
    </form>

    <div class="register-row">
        Belum punya akun? <a href="registerpage.php">Daftar di sini</a>
    </div>
</div>

</body>
</html>
