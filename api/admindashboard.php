<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';
if (!cek_login_admin()) {
    header("Location: loginpage.php"); exit();
}
$role     = $_SESSION["admin_role"];
$nama     = $_SESSION["admin_nama"];
$username = $_SESSION["admin_username"];

$statArtikel  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM info_penyakit"))['c'] ?? 0;
$statPengguna = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM pengguna"))['c'] ?? 0;
$statAdmin    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM admin"))['c'] ?? 0;
$statAktif    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM admin WHERE is_active = 1"))['c'] ?? 0;
$rBaru = mysqli_query($conn, "SELECT COUNT(*) as c FROM pengguna WHERE DATE(created_at) = CURDATE()");
$statBaru = $rBaru ? (mysqli_fetch_assoc($rBaru)['c'] ?? 0) : 0;

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
    <title>Dashboard Admin CePaT</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&display=swap" rel="stylesheet">
    <style>
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        :root{
            --green:#2e7d32;--green-mid:#4caf50;--green-light:#e8f5e9;--green-soft:#f1f8e9;
            --sidebar-bg:#0d1f0e;--body-bg:#f4f6f4;--card:#ffffff;
            --text:#1c2b1d;--text-2:#52706a;--text-3:#8fa89a;
            --border:#e2e8e2;--border-2:#edf2ed;
            --gold:#f59e0b;--gold-soft:#fef3c7;
            --red:#dc2626;--red-soft:#fef2f2;
            --blue:#2563eb;--blue-soft:#eff6ff;
            --purple:#7c3aed;--purple-soft:#f5f3ff;
            --sidebar-w:260px;--radius:14px;
        }
        html{-webkit-font-smoothing:antialiased}
        body{font-family:'DM Sans',sans-serif;background:var(--body-bg);color:var(--text);display:flex;min-height:100vh}

        /* SIDEBAR */
        .sidebar{
            width:var(--sidebar-w);
            background:var(--sidebar-bg);
            min-height:100vh;
            position:fixed;left:0;
            top:0;
            display:flex;
            flex-direction:column;
            z-index:200;
            overflow:hidden
        }

        .sidebar::before{
            content:''
            ;position:absolute;
            top:-60px;
            left:-60px;
            width:220px;
            height:220px;
            background:radial-gradient(circle,rgba(76,175,80,.1) 0%,transparent 70%);
            pointer-events:none
        }

        .sidebar-brand{
            padding:24px 20px 18px;
            border-bottom:1px solid rgba(255,255,255,.06);
            display:flex;
            align-items:center;
            gap:12px
        }

        .brand-icon{
            width:38px;
            height:38px;
            background:linear-gradient(135deg,#4caf50,#2e7d32);
            border-radius:10px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.1rem;
            flex-shrink:0;
            box-shadow:0 3px 10px rgba(76,175,80,.35)
        }

        .brand-name{
            font-size:1.15rem;
            font-weight:800;
            color:#fff
        }

        .brand-sub{
            font-size:.65rem;
            font-weight:600;
            color:rgba(255,255,255,.3);
            letter-spacing:2px;
            text-transform:uppercase
        }

        .admin-pill{
            margin:14px 14px 0;
            display:flex;
            align-items:center;
            gap:10px;
            background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);
            border-radius:10px;
            padding:10px 12px
        }

        .admin-ava{
            width:32px;
            height:32px;
            border-radius:8px;
            background:linear-gradient(135deg,#4caf50,#81c784);
            display:flex;align-items:center;
            justify-content:center;
            font-size:.8rem;
            font-weight:800;
            color:#fff;
            flex-shrink:0
        }

        .admin-info-name{
            font-size:.82rem;
            font-weight:700;
            color:#fff;
            line-height:1.2
        }

        .admin-info-role{
            font-size:.67rem;
            color:rgba(255,255,255,.35)
        }

        .sidebar-section{
            padding:18px 12px 6px
        }

        .section-label{
            font-size:.62rem;
            font-weight:700;
            letter-spacing:2px;
            text-transform:uppercase;
            color:rgba(255,255,255,.25);
            padding:0 8px;
            margin-bottom:4px
        }

        .nav-link{
            display:flex;
            align-items:center;
            gap:10px;padding:10px 12px;
            border-radius:9px;
            text-decoration:none;
            color:rgba(255,255,255,.55);font-size:.86rem;
            font-weight:500;
            transition:all .18s;
            border-left:2px solid transparent;
            margin-bottom:1px
        }

        .nav-link:hover{
            background:rgba(255,255,255,.06);
            color:rgba(255,255,255,.9)
        }
        
        .nav-link.active{
            background:rgba(76,175,80,.14);
            color:#81c784;
            border-left-color:#4caf50
        }

        .nav-link.locked{
            opacity:.3;
            pointer-events:none;
            cursor:not-allowed
        }

        .nav-icon{
            font-size:.9rem;
            width:18px;
            text-align:center;
            flex-shrink:0
        }

        .sidebar-footer{
            margin-top:auto;
            padding:14px 12px;
            border-top:1px solid rgba(255,255,255,.06)
        }

        .logout-btn{
            display:flex;
            align-items:center;
            gap:8px;
            padding:9px 12px;
            border-radius:8px;
            background:rgba(220,38,38,.1);
            border:1px solid rgba(220,38,38,.2);
            color:#fca5a5;
            text-decoration:none;
            font-size:.83rem;
            font-weight:600;
            transition:all .18s
        }

        .logout-btn:hover{
            background:rgba(220,38,38,.2);
            color:#fff
        }

        /* MAIN */
        .main{
            margin-left:var(--sidebar-w);
            flex:1;
            display:flex;
            flex-direction:column
        }

        .topbar{
            background:var(--card);border-bottom:1px solid var(--border);
            padding:0 32px;
            height:64px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            position:sticky;
            top:0;z-index:100
        }

        .topbar-title{
            font-size:1.05rem;
            font-weight:800
        }

        .topbar-sub{
            font-size:.75rem;
            color:var(--text-3);
            margin-top:1px
        }
        .topbar-date{
            font-size:.82rem;
            color:var(--text-3);
            background:var(--body-bg);
            border:1px solid var(--border);
            padding:6px 14px;
            border-radius:8px
        }
        .content{
            padding:28px 32px
        }

        /* WELCOME BANNER */
        .welcome-banner{
            background:linear-gradient(135deg,#1b5e20 0%,#2e7d32 55%,#388e3c 100%);
            border-radius:var(--radius);
            padding:26px 30px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:24px;
            position:relative;
            overflow:hidden;
            animation:fadeUp .4s ease both
        }
        .welcome-banner::before{
            content:'';
            position:absolute;
            right:-40px;
            top:-40px;
            width:180px;
            height:180px;
            border-radius:50%;
            background:rgba(255,255,255,.06)
        }
        .welcome-banner::after{
            content:'';
            position:absolute;
            right:80px;
            bottom:-60px;
            width:140px;
            height:140px;
            border-radius:50%;
            background:rgba(255,255,255,.04)
        }
        .welcome-text{
            position:relative;
            z-index:1
        }
        .welcome-text h2{
            font-size:1.3rem;
            font-weight:800;
            color:#fff;
            margin-bottom:4px
        }
        .welcome-text p{
            font-size:.87rem;
            color:rgba(255,255,255,.7)
        }
        .welcome-role{
            position:relative;
            z-index:1;
            background:rgba(255,255,255,.12);
            border:1px solid rgba(255,255,255,.2);
            border-radius:10px;
            padding:12px 18px;
            text-align:center
        }
        .welcome-role-icon{
            font-size:1.6rem;
            margin-bottom:4px
        }
        .welcome-role-label{
            font-size:.75rem;
            font-weight:700;
            color:rgba(255,255,255,.8)
        }

        /* STATS */
        .stats-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(155px,1fr));
            gap:16px;
            margin-bottom:26px
        }
        .stat-card{
            background:var(--card);
            border:1px solid var(--border);
            border-radius:var(--radius);
            padding:20px 22px;
            display:flex;
            align-items:center;
            gap:14px;
            box-shadow:0 1px 3px rgba(0,0,0,.05);
            transition:transform .2s,box-shadow .2s;
            animation:fadeUp .4s ease both
        }

        .stat-card:hover{
            transform:translateY(-2px);
            box-shadow:0 6px 20px rgba(0,0,0,.08)
        }

        .stat-card:nth-child(1){
            animation-delay:.05s
        }

        .stat-card:nth-child(2){
            animation-delay:.10s
        }

        .stat-card:nth-child(3){
            animation-delay:.15s
        }

        .stat-card:nth-child(4){
            animation-delay:.20s
        }

        .stat-card:nth-child(5){
            animation-delay:.25s
        }

        @keyframes fadeUp{
            from{opacity:0;
            transform:translateY(12px)}to{opacity:1;
            transform:translateY(0)}
        }

        .stat-ico{
            width:46px;
            height:46px;
            border-radius:11px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:1.2rem;
            flex-shrink:0
        }

        .ico-green{
            background:var(--green-light)
        }

        .ico-blue{
            background:var(--blue-soft)
        }

        .ico-gold{
            background:var(--gold-soft)
        }

        .ico-red{
            background:var(--red-soft)
        }

        .ico-purple{
            background:var(--purple-soft)
        }

        .stat-num{
            font-size:1.85rem;
            font-weight:800;
            line-height:1;
            color:var(--text)
        }

        .stat-label{
            font-size:.74rem;
            color:var(--text-3);
            margin-top:3px;
            font-weight:500
        }

        /* ACTION CARDS */
        .sec-title{
            font-size:.9rem;
            font-weight:800;
            color:var(--text);
            margin-bottom:14px
        }

        .action-grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(210px,1fr));
            gap:16px;
            margin-bottom:28px
        }

        .action-card{
            background:var(--card);
            border:1px solid var(--border);
            border-radius:var(--radius);
            padding:24px;
            text-decoration:none;
            display:block;
            position:relative;
            overflow:hidden;
            transition:all .22s;
            box-shadow:0 1px 3px rgba(0,0,0,.05);
            animation:fadeUp .4s ease .3s both
        }

        .action-card::before{
            content:'';
            position:absolute;
            top:0;
            left:0;
            right:0;
            height:3px
        }

        .ac-green::before{
            background:linear-gradient(90deg,#4caf50,#81c784)
        }

        .ac-gold::before{
            background:linear-gradient(90deg,#f59e0b,#fcd34d)
        }

        .ac-blue::before{
            background:linear-gradient(90deg,#2563eb,#60a5fa)
        }

        .ac-purple::before{
            background:linear-gradient(90deg,#7c3aed,#a78bfa)
        }

        .action-card:hover{
            transform:translateY(-4px);
            box-shadow:0 10px 28px rgba(0,0,0,.09)
        }

        .action-card.locked{
            opacity:.4;pointer-events:none
        }

        .lock-tag{
            position:absolute;
            top:14px;
            right:14px;
            font-size:.75rem;
            opacity:.5
        }

        .ac-icon{
            font-size:1.8rem;
            margin-bottom:12px
        }

        .action-card h3{
            font-size:.92rem;
            font-weight:800;
            color:var(--text);
            margin-bottom:5px
        }

        .action-card p{
            font-size:.8rem;
            color:var(--text-2);
            line-height:1.55
        }

        .ac-badge{
            display:inline-block;
            font-size:.65rem;
            font-weight:700;
            letter-spacing:.8px;
            text-transform:uppercase;
            padding:3px 10px;
            border-radius:99px;
            margin-top:12px
        }

        .badge-all{
            background:var(--purple-soft);
            color:var(--purple)
        }

        .badge-super{
            background:var(--gold-soft);
            color:#92400e
        }

        .badge-konten{
            background:var(--green-light);
            color:var(--green)
        }

        .badge-peng{
            background:var(--blue-soft);
            color:var(--blue)
        }

        /* INFO TABLE */
        .info-table-card{
            background:var(--card);
            border:1px solid var(--border);
            border-radius:var(--radius);
            overflow:hidden;
            box-shadow:0 1px 3px rgba(0,0,0,.05);
            animation:fadeUp .4s ease .4s both
        }
        
        .itc-head{
            padding:16px 22px;
            border-bottom:1px solid var(--border);
            display:flex;
            align-items:center;
            justify-content:space-between
        }

        .itc-head h3{
            font-size:.92rem;
            font-weight:800
        }

        .itc-head a{
            font-size:.8rem;
            color:var(--green-mid);
            text-decoration:none;
            font-weight:600
        }

        .itc-head a:hover{
            text-decoration:underline
        }

        table{
            width:100%;
            border-collapse:collapse
        }

        thead{
            background:var(--green-soft)
        }

        th{
            padding:11px 20px;
            text-align:left;
            font-size:.68rem;
            font-weight:800;
            letter-spacing:1px;
            text-transform:uppercase;
            color:var(--green);
            border-bottom:1px solid var(--border)
        }

        td{
            padding:13px 20px;
            font-size:.87rem;
            border-bottom:1px solid var(--border-2);
            color:var(--text-2);
            vertical-align:middle
        }

        tbody tr{
            transition:background .12s
        }

        tbody tr:hover td{
            background:#f8fdf8
        }

        tbody tr:last-child td{
            border-bottom:none
        }

        .badge-role{
            display:inline-flex;
            align-items:center;
            gap:4px;padding:3px 10px;
            border-radius:99px;
            font-size:.7rem;
            font-weight:700;
            border:1px solid transparent
        }

        .r-super{
            background:var(--gold-soft);
            color:#92400e;
            border-color:#fde68a
        }

        .r-konten{
            background:var(--green-light);
            color:var(--green);
            border-color:#c8e6c9
        }

        .r-peng{
            background:var(--blue-soft);
            color:var(--blue);
            border-color:#bfdbfe
        }

        .status-dot{
            display:inline-flex;
            align-items:center;
            gap:5px;
            font-size:.78rem
        }

        .dot{
            width:7px;
            height:7px;
            border-radius:50%
        }

        .dot-on{
            background:#4caf50
        }

        .dot-off{background:#dc2626
        }

        .empty-td{
            text-align:center;
            color:var(--text-3);
            padding:30px!important;
            font-size:.85rem
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

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🌿</div>
        <div>
            <div class="brand-name">CePaT</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>

    <div class="admin-pill">
        <div class="admin-ava"><?= strtoupper(substr($nama, 0, 1)) ?></div>
        <div>
            <div class="admin-info-name"><?= htmlspecialchars($nama) ?></div>
            <div class="admin-info-role"><?= $roleBadge ?></div>
        </div>
    </div>

    <div class="sidebar-section">
        <div class="section-label">Utama</div>
        <a href="admindashboard.php" class="nav-link active">
            <span class="nav-icon">📊</span> Dashboard
        </a>
        <a href="tugasweb.php" class="nav-link" target="_blank">
            <span class="nav-icon">🏠</span> Portal Pengguna
        </a>
    </div>

    <div class="sidebar-section">
        <div class="section-label">Manajemen</div>

        <?php if ($role === 'superadmin'): ?>
        <a href="admin_kelola_admin.php" class="nav-link">
            <span class="nav-icon">👥</span> Kelola Admin
        </a>
        <?php else: ?>
        <a class="nav-link locked"><span class="nav-icon">👥</span> Kelola Admin</a>
        <?php endif; ?>

        <?php if (in_array($role, ['superadmin', 'pengguna'])): ?>
        <a href="admin_kelola_pengguna.php" class="nav-link">
            <span class="nav-icon">👤</span> Kelola Pengguna
        </a>
        <?php else: ?>
        <a class="nav-link locked"><span class="nav-icon">👤</span> Kelola Pengguna</a>
        <?php endif; ?>

        <?php if (in_array($role, ['superadmin', 'konten'])): ?>
        <a href="admin_infopenyakit.php" class="nav-link">
            <span class="nav-icon">📋</span> Info Penyakit
        </a>
        <?php else: ?>
        <a class="nav-link locked"><span class="nav-icon">📋</span> Info Penyakit</a>
        <?php endif; ?>

        <a href="infopenyakit.php" class="nav-link" target="_blank">
            <span class="nav-icon">🌐</span> Halaman Info Publik
        </a>
    </div>

    <div class="sidebar-footer">
        <a href="prosesadminlogout.php" class="logout-btn">🚪 Logout Admin</a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div>
            <div class="topbar-title">Dashboard Admin</div>
            <div class="topbar-sub">Selamat datang, <?= htmlspecialchars($nama) ?>!</div>
        </div>
        <div class="topbar-date">📅 <?= date('d F Y') ?></div>
    </div>

    <div class="content">

        <div class="welcome-banner">
            <div class="welcome-text">
                <h2>Halo, <?= htmlspecialchars($nama) ?>! 👋</h2>
                <p>
                    <?php if ($role === 'superadmin'): ?>
                        Anda memiliki akses penuh. Kelola semua aspek sistem CePaT dari sini.
                    <?php elseif ($role === 'konten'): ?>
                        Anda dapat mengelola artikel dan informasi penyakit tanaman.
                    <?php elseif ($role === 'pengguna'): ?>
                        Anda dapat mengelola dan memantau akun pengguna yang terdaftar.
                    <?php endif; ?>
                </p>
            </div>
            <div class="welcome-role">
                <div class="welcome-role-icon">
                    <?= $role === 'superadmin' ? '👑' : ($role === 'konten' ? '📋' : '👤') ?>
                </div>
                <div class="welcome-role-label"><?= $roleBadge ?></div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-ico ico-green">🌿</div>
                <div>
                    <div class="stat-num"><?= $statArtikel ?></div>
                    <div class="stat-label">Artikel Penyakit</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico ico-blue">👤</div>
                <div>
                    <div class="stat-num"><?= $statPengguna ?></div>
                    <div class="stat-label">Total Pengguna</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico ico-purple">📅</div>
                <div>
                    <div class="stat-num"><?= $statBaru ?></div>
                    <div class="stat-label">Daftar Hari Ini</div>
                </div>
            </div>
            <?php if ($role === 'superadmin'): ?>
            <div class="stat-card">
                <div class="stat-ico ico-gold">👑</div>
                <div>
                    <div class="stat-num"><?= $statAdmin ?></div>
                    <div class="stat-label">Total Admin</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-ico ico-green">✅</div>
                <div>
                    <div class="stat-num"><?= $statAktif ?></div>
                    <div class="stat-label">Admin Aktif</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="sec-title">⚡ Aksi Cepat</div>
        <div class="action-grid">

            <?php if (in_array($role, ['superadmin', 'konten'])): ?>
            <a href="admin_infopenyakit.php" class="action-card ac-green">
                <div class="ac-icon">📋</div>
                <h3>Kelola Info Penyakit</h3>
                <p>Tambah, edit, atau hapus artikel informasi penyakit tanaman.</p>
                <span class="ac-badge badge-konten">Admin Konten</span>
            </a>
            <?php else: ?>
            <div class="action-card ac-green locked">
                <span class="lock-tag">🔒</span>
                <div class="ac-icon">📋</div>
                <h3>Kelola Info Penyakit</h3>
                <p>Khusus Admin Konten & Super Admin.</p>
                <span class="ac-badge badge-konten">Admin Konten</span>
            </div>
            <?php endif; ?>

            <?php if (in_array($role, ['superadmin', 'pengguna'])): ?>
            <a href="admin_kelola_pengguna.php" class="action-card ac-blue">
                <div class="ac-icon">👤</div>
                <h3>Kelola Pengguna</h3>
                <p>Lihat dan hapus akun pengguna yang terdaftar di platform CePaT.</p>
                <span class="ac-badge badge-peng">Admin Pengguna</span>
            </a>
            <?php else: ?>
            <div class="action-card ac-blue locked">
                <span class="lock-tag">🔒</span>
                <div class="ac-icon">👤</div>
                <h3>Kelola Pengguna</h3>
                <p>Khusus Admin Pengguna & Super Admin.</p>
                <span class="ac-badge badge-peng">Admin Pengguna</span>
            </div>
            <?php endif; ?>

            <?php if ($role === 'superadmin'): ?>
            <a href="admin_kelola_admin.php" class="action-card ac-gold">
                <div class="ac-icon">👥</div>
                <h3>Kelola Admin</h3>
                <p>Tambah, hapus, dan atur role administrator sistem CePaT.</p>
                <span class="ac-badge badge-super">Super Admin</span>
            </a>
            <?php else: ?>
            <div class="action-card ac-gold locked">
                <span class="lock-tag">🔒</span>
                <div class="ac-icon">👥</div>
                <h3>Kelola Admin</h3>
                <p>Khusus Super Administrator.</p>
                <span class="ac-badge badge-super">Super Admin</span>
            </div>
            <?php endif; ?>

            <a href="infopenyakit.php" class="action-card ac-purple" target="_blank">
                <div class="ac-icon">🌐</div>
                <h3>Lihat Halaman Publik</h3>
                <p>Buka halaman Info Penyakit seperti yang dilihat pengguna umum.</p>
                <span class="ac-badge badge-all">Semua Admin</span>
            </a>

        </div>

        <?php if ($role === 'superadmin'): ?>
        <div class="sec-title">👥 Daftar Admin Sistem</div>
        <div class="info-table-card">
            <div class="itc-head">
                <h3>Admin Terdaftar (<?= $statAdmin ?>)</h3>
                <a href="admin_kelola_admin.php">Kelola semua →</a>
            </div>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Nama</th><th>Username</th><th>Role</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php
                    $admins = mysqli_query($conn, "SELECT * FROM admin ORDER BY role DESC, nama ASC");
                    if (!$admins || mysqli_num_rows($admins) === 0):
                    ?>
                    <tr><td colspan="4" class="empty-td">Belum ada data admin.</td></tr>
                    <?php else: while ($a = mysqli_fetch_assoc($admins)): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--text)"><?= htmlspecialchars($a['nama']) ?></td>
                        <td style="color:var(--text-3)">@<?= htmlspecialchars($a['username']) ?></td>
                        <td>
                            <span class="badge-role <?= $a['role']==='superadmin'?'r-super':($a['role']==='pengguna'?'r-peng':'r-konten') ?>">
                                <?= $a['role']==='superadmin'?'👑 Super Admin':($a['role']==='pengguna'?'👤 Admin Pengguna':'📋 Admin Konten') ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-dot">
                                <span class="dot <?= $a['is_active']?'dot-on':'dot-off' ?>"></span>
                                <?= $a['is_active']?'Aktif':'Nonaktif' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>

        <?php elseif ($role === 'pengguna'): ?>
        <div class="sec-title">👤 Pengguna Terbaru</div>
        <div class="info-table-card">
            <div class="itc-head">
                <h3>Pengguna Terdaftar (<?= $statPengguna ?>)</h3>
                <a href="admin_kelola_pengguna.php">Kelola semua →</a>
            </div>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Nama</th><th>Username</th><th>Email</th><th>Terdaftar</th></tr>
                </thead>
                <tbody>
                    <?php
                    $users = mysqli_query($conn, "SELECT * FROM pengguna ORDER BY id DESC LIMIT 8");
                    if (!$users || mysqli_num_rows($users) === 0):
                    ?>
                    <tr><td colspan="4" class="empty-td">Belum ada pengguna.</td></tr>
                    <?php else: while ($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--text)"><?= htmlspecialchars($u['nama']) ?></td>
                        <td style="color:var(--text-3)">@<?= htmlspecialchars($u['username']) ?></td>
                        <td style="color:var(--text-3)"><?= htmlspecialchars($u['email']) ?></td>
                        <td style="color:var(--text-3);font-size:.8rem"><?= isset($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '–' ?></td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>

        <?php else: ?>
        <div class="sec-title">📋 Artikel Terbaru</div>
        <div class="info-table-card">
            <div class="itc-head">
                <h3>Info Penyakit (<?= $statArtikel ?>)</h3>
                <a href="admin_infopenyakit.php">Kelola semua →</a>
            </div>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr><th>Judul</th><th>Tanaman</th><th>Status</th></tr>
                </thead>
                <tbody>
                    <?php
                    $arts = mysqli_query($conn, "SELECT * FROM info_penyakit ORDER BY created_at DESC LIMIT 8");
                    if (!$arts || mysqli_num_rows($arts) === 0):
                    ?>
                    <tr><td colspan="3" class="empty-td">Belum ada artikel.</td></tr>
                    <?php else: while ($ar = mysqli_fetch_assoc($arts)): ?>
                    <tr>
                        <td style="font-weight:600;color:var(--text)"><?= htmlspecialchars($ar['judul']) ?></td>
                        <td style="color:var(--text-3)"><?= htmlspecialchars($ar['jenis_tanaman']) ?></td>
                        <td>
                            <span class="status-dot">
                                <span class="dot <?= $ar['is_active']?'dot-on':'dot-off' ?>"></span>
                                <?= $ar['is_active']?'Aktif':'Nonaktif' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; endif; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

    </div>
</div>
</body>
</html>