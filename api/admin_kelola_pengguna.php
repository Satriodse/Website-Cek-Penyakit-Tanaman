<?php
session_start();
require_once 'koneksi.php';

// Hanya superadmin dan admin_pengguna yang bisa akses
if (!isset($_SESSION["admin_nama"])) {
    header("Location: adminloginpage.php"); exit();
}

$role = $_SESSION["admin_role"];
if (!in_array($role, ['superadmin', 'pengguna'])) {
    header("Location: admindashboard.php"); exit();
}

$msg     = '';
$msgType = 'success';

// ── HAPUS PENGGUNA ─────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {
    if ($_POST["action"] === "hapus") {
        $id = (int) $_POST["id"];

        $rNama = mysqli_prepare($conn, "SELECT nama FROM pengguna WHERE id = ?");
        mysqli_stmt_bind_param($rNama, "i", $id);
        mysqli_stmt_execute($rNama);
        $resNama = mysqli_stmt_get_result($rNama);
        $dataNama = mysqli_fetch_assoc($resNama);
        $namaHapus = $dataNama['nama'] ?? 'Pengguna';

        $stmt = mysqli_prepare($conn, "DELETE FROM pengguna WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Akun pengguna <strong>$namaHapus</strong> berhasil dihapus.";
        } else {
            $msg = "Gagal menghapus pengguna.";
            $msgType = "danger";
        }
    }
}

// ── AMBIL DATA PENGGUNA ────────────────────────────────────
$keyword = trim($_GET['cari'] ?? '');
$pengguna = [];

if ($keyword !== '') {
    $stmt = mysqli_prepare($conn, "SELECT * FROM pengguna WHERE nama LIKE ? OR username LIKE ? OR email LIKE ? ORDER BY id DESC");
    $like = "%$keyword%";
    mysqli_stmt_bind_param($stmt, "sss", $like, $like, $like);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $result = mysqli_query($conn, "SELECT * FROM pengguna ORDER BY id DESC");
}

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $pengguna[] = $row;
    }
}

$total = count($pengguna);
$adminNama = $_SESSION["admin_nama"];

// Label role untuk badge sidebar
$roleLabels = [
    'superadmin' => '👑 Super Admin',
    'konten'     => '📋 Admin Konten',
    'pengguna'   => '👤 Admin Pengguna',
];
$roleBadge = $roleLabels[$role] ?? $role;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna CePaT Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --green:        #2e7d32;
            --green-mid:    #4caf50;
            --green-light:  #e8f5e9;
            --green-soft:   #f1f8e9;
            --sidebar-bg:   #0d1f0e;
            --body-bg:      #f4f6f4;
            --card:         #ffffff;
            --text:         #1c2b1d;
            --text-2:       #52706a;
            --text-3:       #8fa89a;
            --border:       #e2e8e2;
            --border-2:     #edf2ed;
            --red:          #dc2626;
            --red-soft:     #fef2f2;
            --gold:         #f59e0b;
            --gold-soft:    #fef3c7;
            --blue:         #2563eb;
            --blue-soft:    #eff6ff;
            --sidebar-w:    260px;
            --radius:       14px;
        }

        html { -webkit-font-smoothing: antialiased; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--body-bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            left: 0; top: 0;
            display: flex;
            flex-direction: column;
            z-index: 200;
        }

        .sidebar::before {
            content: '';
            position: absolute;
            top: -60px; left: -60px;
            width: 220px; height: 220px;
            background: radial-gradient(circle, rgba(76,175,80,.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .sidebar-brand {
            padding: 24px 20px 18px;
            border-bottom: 1px solid rgba(255,255,255,.06);
            display: flex; align-items: center; gap: 12px;
        }

        .brand-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #4caf50, #2e7d32);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; flex-shrink: 0;
            box-shadow: 0 3px 10px rgba(76,175,80,.35);
        }

        .brand-name { 
            font-size: 1.15rem; 
            font-weight: 800; 
            color: #fff; 
            letter-spacing: -.3px; 
        }

        .brand-sub  { 
            font-size: .65rem; 
            font-weight: 600; 
            color: rgba(255,255,255,.3); 
            letter-spacing: 2px; 
            text-transform: uppercase; 
        }

        .admin-pill {
            margin: 14px 14px 0;
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: 10px;
            padding: 10px 12px;
        }

        .admin-ava {
            width: 32px; 
            height: 32px; 
            border-radius: 8px;
            background: linear-gradient(135deg, #4caf50, #81c784);
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: .8rem; 
            font-weight: 800; 
            color: #fff; 
            flex-shrink: 0;
        }

        .admin-info-name { 
            font-size: .82rem; 
            font-weight: 700; 
            color: #fff; 
            line-height: 1.2; 
        }

        .admin-info-role { 
            font-size: .67rem; 
            color: rgba(255,255,255,.35); 
        }

        .sidebar-section { 
            padding: 18px 12px 6px; 
        }

        .section-label {
            font-size: .62rem; 
            font-weight: 700; 
            letter-spacing: 2px;
            text-transform: uppercase; 
            color: rgba(255,255,255,.25);
            padding: 0 8px; 
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex; align-items: center; 
            gap: 10px;
            padding: 10px 12px; 
            border-radius: 9px;
            text-decoration: none;
            color: rgba(255,255,255,.55);
            font-size: .86rem; 
            font-weight: 500;
            transition: all .18s;
            border-left: 2px solid transparent;
            margin-bottom: 1px;
        }

        .nav-link:hover { 
            background: rgba(255,255,255,.06); 
            color: rgba(255,255,255,.9); 
        }

        .nav-link.active { 
            background: rgba(76,175,80,.14); 
            color: #81c784; 
            border-left-color: #4caf50; 
        }

        .nav-link.locked { 
            opacity: .3; 
            pointer-events: none; 
        }

        .nav-icon { 
            font-size: .9rem; 
            width: 18px; 
            text-align: center; 
            flex-shrink: 0; 
        }

        .sidebar-footer {
            margin-top: auto;
            padding: 14px 12px;
            border-top: 1px solid rgba(255,255,255,.06);
        }

        .logout-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 9px 12px; border-radius: 8px;
            background: rgba(220,38,38,.1);
            border: 1px solid rgba(220,38,38,.2);
            color: #fca5a5;
            text-decoration: none;
            font-size: .83rem; font-weight: 600;
            transition: all .18s;
        }

        .logout-btn:hover { background: rgba(220,38,38,.2); color: #fff; }

        /* MAIN */
        .main { 
            margin-left: var(--sidebar-w); 
            flex: 1; 
            display: flex; 
            flex-direction: column; 
        }

        .topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: 0 32px;
            height: 64px;
            display: flex; align-items: center; 
            justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
        }

        .topbar-title { 
            font-size: 1.05rem; 
            font-weight: 800; 
        }

        .topbar-sub { 
            font-size: .75rem; 
            color: var(--text-3);
            margin-top: 1px; 
        }

        .content { 
            padding: 28px 32px; 
            flex: 1; 
        }

        /* ALERT */
        .alert {
            display: flex; 
            align-items: center; 
            gap: 12px;
            padding: 14px 18px; 
            border-radius: 11px;
            font-size: .87rem; 
            font-weight: 500;
            margin-bottom: 22px;
            border: 1px solid transparent;
            animation: slideDown .3s ease both;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .alert-success { 
            background: var(--green-soft); 
            color: var(--green); 
            border-color: #c8e6c9; 
        }

        .alert-danger  { 
            background: var(--red-soft);   
            color: var(--red);   
            border-color: #fecaca; 
        }

        /* STAT CARDS */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px 22px;
            display: flex; align-items: center; 
            gap: 14px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            transition: transform .2s, box-shadow .2s;
            animation: fadeUp .4s ease both;
        }

        .stat-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 6px 20px rgba(0,0,0,.08); 
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(12px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .stat-card:nth-child(1) { 
            animation-delay: .05s;
         }

        .stat-card:nth-child(2) { 
            animation-delay: .1s;
         }

        .stat-card:nth-child(3) { 
            animation-delay: .15s; 
        }

        .stat-ico {
            width: 46px; 
            height: 46px; 
            border-radius: 11px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: 1.2rem; 
            flex-shrink: 0;
        }

        .ico-blue  { 
            background: var(--blue-soft); 
        }

        .ico-green { 
            background: var(--green-light); 
        }

        .ico-gold  { 
            background: var(--gold-soft); 
        }

        .stat-num   { 
            font-size: 1.9rem; 
            font-weight: 800; 
            line-height: 1; 
        }

        .stat-label { 
            font-size: .75rem; 
            color: var(--text-3); 
            margin-top: 3px; 
        }

        /* TABLE CARD */
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,.06);
            animation: fadeUp .4s ease .2s both;
        }

        .table-head {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; 
            justify-content: space-between; 
            gap: 14px;
            flex-wrap: wrap;
        }

        .table-head-left h2 { 
            font-size: 1rem;
            font-weight: 800; 
        }
        .table-head-left p  { 
            font-size: .76rem; 
            color: var(--text-3); 
            margin-top: 2px; 
        }

        /* search */
        .search-form { 
            display: flex; 
            gap: 8px; 
            align-items: center; 
        }

        .search-wrap { 
            position: relative; 
        }

        .search-wrap input {
            background: var(--body-bg);
            border: 1.5px solid var(--border);
            border-radius: 9px;
            padding: 9px 14px 9px 36px;
            font-size: .85rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            outline: none;
            width: 260px;
            transition: border-color .2s;
        }

        .search-wrap input:focus { 
            border-color: var(--green-mid); 
        }

        .search-wrap input::placeholder { 
            color: var(--text-3); 
        }
        
        .search-icon { 
            position: absolute; 
            left: 11px; top: 50%; 
            transform: translateY(-50%); 
            color: var(--text-3); 
            font-size: .85rem; 
            pointer-events: none; 
        }

        .btn-search {
            padding: 9px 16px;
            background: var(--green-mid);
            color: #fff; 
            border: none;
            border-radius: 9px;
            font-size: .84rem; 
            font-weight: 700;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background .18s;
        }

        .btn-search:hover { 
            background: var(--green); 
        }

        .btn-reset {
            padding: 9px 14px;
            background: var(--body-bg);
            color: var(--text-2);
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: .84rem; 
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            transition: all .18s;
        }

        .btn-reset:hover { 
            background: var(--border); 
        }

        /* table */
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        thead { 
            background: var(--green-soft); 
        }

        th {
            padding: 12px 20px;
            text-align: left;
            font-size: .68rem; 
            font-weight: 800;
            letter-spacing: 1.2px; 
            text-transform: uppercase;
            color: var(--green);
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 14px 20px;
            font-size: .87rem;
            border-bottom: 1px solid var(--border-2);
            vertical-align: middle;
        }

        tbody tr { 
            transition: background .12s; 
        }

        tbody tr:hover td { 
            background: #f8fdf8; 
        }

        tbody tr:last-child td { 
            border-bottom: none; 
        }

        /* user cell */
        .cell-user { 
            display: flex; 
            align-items: center; 
            gap: 12px; 
        }

        .user-ava {
            width: 36px; 
            height: 36px; 
            border-radius: 9px;
            display: flex; 
            align-items: center; 
            justify-content: center;
            font-size: .82rem; 
            font-weight: 800; 
            color: #fff;
            flex-shrink: 0;
            background: linear-gradient(135deg, #4caf50, #2e7d32);
        }

        .user-nama  { 
            font-weight: 700; 
            font-size: .9rem; 
            line-height: 1.2; 
        }

        .user-email { 
            font-size: .75rem; 
            color: var(--text-3); 
        }

        .badge-username {
            display: inline-block;
            background: var(--blue-soft);
            color: var(--blue);
            border: 1px solid #bfdbfe;
            padding: 3px 10px; 
            border-radius: 99px;
            font-size: .72rem; 
            font-weight: 700;
        }

        .no-row { 
            text-align: center; 
            color: var(--text-3); 
            padding: 50px 20px !important; 
        }

        .no-icon { 
            font-size: 2.5rem; 
            display: block; 
            margin-bottom: 10px; 
        }

        /* delete btn */
        .btn-hapus {
            display: inline-flex; align-items: center; 
            gap: 5px;
            padding: 7px 14px; 
            border-radius: 8px;
            background: var(--red-soft);
            color: var(--red);
            border: 1px solid #fecaca;
            font-size: .76rem; 
            font-weight: 700;
            cursor: pointer;
            font-family: 'DM Sans', poppins-serif;
            transition: all .15s;
        }

        .btn-hapus:hover { 
            background: var(--red); 
            color: #fff; 
        }

        /* MODAL */
        .overlay {
            position: fixed; inset: 0;
            background: rgba(0,0,0,.5);
            backdrop-filter: blur(4px);
            z-index: 500;
            display: none; align-items: center; 
            justify-content: center;
        }

        .overlay.show { 
            display: flex; 
        }

        .modal {
            background: var(--card);
            border-radius: 18px;
            padding: 32px 28px 26px;
            max-width: 380px; width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,.2);
            text-align: center;
            animation: popIn .25s cubic-bezier(.34,1.56,.64,1) both;
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(.88); }
            to   { opacity: 1; transform: scale(1); }
        }

        .modal-icon {
            width: 60px; height: 60px;
            background: var(--red-soft);
            border-radius: 50%;
            display: flex; align-items: center; 
            justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 16px;
        }

        .modal h3 { 
            font-size: 1.15rem; 
            font-weight: 800; 
            margin-bottom: 8px; 
        }

        .modal p  { 
            font-size: .87rem; 
            color: var(--text-2); 
            line-height: 1.6; 
            margin-bottom: 24px; 
        }

        .modal-target { 
            font-weight: 800; 
            color: var(--text); 
        }

        .modal-actions { 
            display: flex; 
            gap: 10px; 
        }

        .modal-cancel {
            flex: 1; padding: 11px;
            background: var(--body-bg);
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-size: .88rem; font-weight: 700;
            cursor: pointer; 
            font-family: 'DM Sans', sans-serif;
            color: var(--text-2); 
            transition: all .15s;
        }

        .modal-cancel:hover { 
            background: var(--border); 
        }

        .modal-confirm {
            flex: 1; padding: 11px;
            background: var(--red); color: #fff; 
            border: none;
            border-radius: 9px;
            font-size: .88rem; 
            font-weight: 700;
            cursor: pointer; 
            font-family: 'DM Sans', sans-serif;
            box-shadow: 0 3px 10px rgba(220,38,38,.3);
            transition: all .15s;
        }

        .modal-confirm:hover { 
            background: #b91c1c; 
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto; /* Memunculkan scroll bar horizontal jika tabel terlalu lebar */
            -webkit-overflow-scrolling: touch; /* Membuat scroll mulus di layar sentuh */
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        /* Pastikan tabel mengambil lebar penuh */
        .table-responsive table {
            width: 100%;
            min-width: 600px; /* Jika layar lebih kecil dari 600px, tabel bisa di-scroll */
            border-collapse: collapse;
        }

        /* ── RESPONSIVE PADA LAYAR KECIL (HP & TABLET) ── */
        @media screen and (max-width: 768px) {
    
            /* 1. Jika ada layout flexbox/grid yang berdampingan, ubah jadi bertumpuk */
        .dashboard-container, .main-layout {
            display: flex;
            flex-direction: column; /* Mengubah kiri-kanan menjadi atas-bawah */
        }

        /* 2. Sesuaikan Sidebar */
        .sidebar {
            width: 100%;
            height: auto;
            position: relative; /* Jangan jadikan fixed di HP jika menghalangi konten */
            padding: 15px;
        }

        /* 3. Sesuaikan Area Konten Utama */
        .main-content {
            margin-left: 0 !important; /* Hilangkan margin kiri yang biasanya disiapkan untuk sidebar */
            width: 100%;
            padding: 15px;
        }

        /* 4. Sesuaikan Kartu (Card) Info di Dashboard */
        .card-container {
            grid-template-columns: 1fr; /* Jika pakai grid, ubah jadi 1 kolom */
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🌿</div>
        <div>
            <div class="brand-name">CePaT</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>

    <div class="admin-pill">
        <div class="admin-ava"><?= strtoupper(substr($adminNama, 0, 1)) ?></div>
        <div>
            <div class="admin-info-name"><?= htmlspecialchars($adminNama) ?></div>
            <div class="admin-info-role"><?= $roleBadge ?></div>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="section-label">Menu</div>
        <a href="admindashboard.php" class="nav-link">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <?php if (in_array($role, ['superadmin', 'pengguna'])): ?>
        <a href="admin_kelola_pengguna.php" class="nav-link active">
            <span class="nav-icon">👤</span> Kelola Pengguna
        </a>
        <?php else: ?>
        <a class="nav-link locked">
            <span class="nav-icon">👤</span> Kelola Pengguna
        </a>
        <?php endif; ?>

        <?php if (in_array($role, ['superadmin', 'konten'])): ?>
        <a href="admin_infopenyakit.php" class="nav-link">
            <span class="nav-icon">📋</span> Info Penyakit
        </a>
        <?php endif; ?>

        <?php if ($role === 'superadmin'): ?>
        <a href="admin_kelola_admin.php" class="nav-link">
            <span class="nav-icon">👥</span> Kelola Admin
        </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-section">
        <div class="section-label">Publik</div>
        <a href="tugasweb.php" class="nav-link" target="_blank">
            <span class="nav-icon">🏠</span> Portal Pengguna
        </a>
        <a href="infopenyakit.php" class="nav-link" target="_blank">
            <span class="nav-icon">🌐</span> Halaman Info
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="prosesadminlogout.php" class="logout-btn">
            🚪 Logout Admin
        </a>
    </div>
</aside>

<div class="main">

    <div class="topbar">
        <div>
            <div class="topbar-title">👤 Kelola Pengguna</div>
            <div class="topbar-sub">Lihat dan hapus akun pengguna terdaftar</div>
        </div>
    </div>

    <div class="content">

        <?php if ($msg): ?>
        <div class="alert alert-<?= $msgType ?>">
            <?= $msgType === 'success' ? '✅' : '❌' ?> <?= $msg ?>
        </div>
        <?php endif; ?>

        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-ico ico-blue">👥</div>
                <div>
                    <?php
                    $rTotal = mysqli_query($conn, "SELECT COUNT(*) as c FROM pengguna");
                    $dTotal = mysqli_fetch_assoc($rTotal);
                    ?>
                    <div class="stat-num"><?= $dTotal['c'] ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico ico-green">🔍</div>
                <div>
                    <div class="stat-num"><?= $total ?></div>
                    <div class="stat-label">Hasil Pencarian</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico ico-gold">📅</div>
                <div>
                    <?php
                    $rBaru = mysqli_query($conn, "SELECT COUNT(*) as c FROM pengguna WHERE DATE(created_at) = CURDATE()");
                    $dBaru = mysqli_fetch_assoc($rBaru);
                    $jumlahBaru = $dBaru['c'] ?? 0;
                    ?>
                    <div class="stat-num"><?= $jumlahBaru ?></div>
                    <div class="stat-label">Daftar Hari Ini</div>
                </div>
            </div>
        </div>

        <div class="table-card">
            <div class="table-head">
                <div class="table-head-left">
                    <h2>Daftar Pengguna
                        <?php if ($keyword): ?>
                            <span style="font-size:.8rem;color:var(--text-3);font-weight:500"> — hasil: "<?= htmlspecialchars($keyword) ?>"</span>
                        <?php endif; ?>
                    </h2>
                    <p><?= $total ?> pengguna ditemukan</p>
                </div>

                <form method="GET" action="admin_kelola_pengguna.php" class="search-form">
                    <div class="search-wrap">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="cari" placeholder="Cari nama, username, email..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <button type="submit" class="btn-search">Cari</button>
                    <?php if ($keyword): ?>
                    <a href="admin_kelola_pengguna.php" class="btn-reset">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Pengguna</th>
                        <th>Username</th>
                        <th>Terdaftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pengguna)): ?>
                    <tr>
                        <td colspan="5" class="no-row">
                            <span class="no-icon">👤</span>
                            <?= $keyword ? "Tidak ada pengguna dengan kata kunci \"$keyword\"" : "Belum ada pengguna terdaftar." ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($pengguna as $i => $p): ?>
                    <tr>
                        <td style="color:var(--text-3);font-size:.8rem;"><?= $i + 1 ?></td>
                        <td>
                            <div class="cell-user">
                                <div class="user-ava"><?= strtoupper(substr($p['nama'], 0, 1)) ?></div>
                                <div>
                                    <div class="user-nama"><?= htmlspecialchars($p['nama']) ?></div>
                                    <div class="user-email"><?= htmlspecialchars($p['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-username">@<?= htmlspecialchars($p['username']) ?></span>
                        </td>
                        <td style="color:var(--text-3);font-size:.82rem;">
                            <?= isset($p['created_at']) ? date('d M Y', strtotime($p['created_at'])) : '–' ?>
                        </td>
                        <td>
                            <button class="btn-hapus"
                                onclick="bukaModal(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes($p['nama'])) ?>', '<?= htmlspecialchars(addslashes($p['email'])) ?>')">
                                🗑️ Hapus
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>

    </div>
</div>

<div class="overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-icon">🗑️</div>
        <h3>Hapus Akun Pengguna?</h3>
        <p>Anda akan menghapus akun:<br>
            <span class="modal-target" id="modal-nama"></span><br>
            <span style="font-size:.8rem;color:var(--text-3)" id="modal-email"></span>
        </p>
        <p style="font-size:.8rem;color:var(--red);margin-bottom:20px;">
            ⚠️ Tindakan ini <strong>tidak dapat dibatalkan</strong>. Data pengguna akan hilang permanen.
        </p>
        <div class="modal-actions">
            <button class="modal-cancel" onclick="tutupModal()">Batal</button>
            <form method="POST" action="admin_kelola_pengguna.php" id="formHapus">
                <input type="hidden" name="action" value="hapus">
                <input type="hidden" name="id" id="modal-id" value="">
                <button type="submit" class="modal-confirm">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModal(id, nama, email) {
    document.getElementById('modal-id').value    = id;
    document.getElementById('modal-nama').textContent  = nama;
    document.getElementById('modal-email').textContent = email;
    document.getElementById('modalOverlay').classList.add('show');
}

function tutupModal() {
    document.getElementById('modalOverlay').classList.remove('show');
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) tutupModal();
});

document.querySelector('.modal-actions').style.display = 'flex';
</script>
</body>
</html>