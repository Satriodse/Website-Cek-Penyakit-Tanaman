<?php
session_start();
require_once 'koneksi.php';

// Hanya super admin yang bisa akses
if (!isset($_SESSION["admin_nama"]) || $_SESSION["admin_role"] !== 'superadmin') {
    header("Location: admindashboard.php?error=Akses+ditolak.+Hanya+Super+Admin.");
    exit();
}

$msg     = '';
$msgType = 'success';

// ── PROSES ──
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

    // TAMBAH ADMIN
    if ($_POST["action"] === "tambah") {
        $nama     = trim($_POST["nama"]);
        $username = trim($_POST["username"]);
        $password = $_POST["password"];
        $role     = $_POST["role"];

        if (empty($nama) || empty($username) || empty($password)) {
            $msg = "Semua field wajib diisi!";
            $msgType = "danger";
        } else {
            // Cek username sudah ada?
            $cek = mysqli_prepare($conn, "SELECT id FROM admin WHERE username = ?");
            mysqli_stmt_bind_param($cek, "s", $username);
            mysqli_stmt_execute($cek);
            mysqli_stmt_store_result($cek);

            if (mysqli_stmt_num_rows($cek) > 0) {
                $msg = "Username sudah digunakan!";
                $msgType = "danger";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO admin (nama, username, password, role, is_active) VALUES (?, ?, ?, ?, 1)");
                mysqli_stmt_bind_param($stmt, "ssss", $nama, $username, $hash, $role);
                if (mysqli_stmt_execute($stmt)) {
                    $msg = "Admin baru berhasil ditambahkan!";
                } else {
                    $msg = "Gagal menambahkan admin.";
                    $msgType = "danger";
                }
            }
            mysqli_stmt_close($cek);
        }
    }

    // HAPUS ADMIN
    if ($_POST["action"] === "hapus") {
        $id = (int) $_POST["id"];
        if ($id === (int) $_SESSION["admin_id"]) {
            $msg = "Tidak dapat menghapus akun Anda sendiri!";
            $msgType = "danger";
        } else {
            $stmt = mysqli_prepare($conn, "DELETE FROM admin WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Admin berhasil dihapus!";
            } else {
                $msg = "Gagal menghapus admin.";
                $msgType = "danger";
            }
        }
    }

    // TOGGLE AKTIF
    if ($_POST["action"] === "toggle") {
        $id        = (int) $_POST["id"];
        $is_active = (int) $_POST["is_active"];
        $newStatus = $is_active ? 0 : 1;

        if ($id === (int) $_SESSION["admin_id"]) {
            $msg = "Tidak dapat menonaktifkan akun Anda sendiri!";
            $msgType = "danger";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE admin SET is_active = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "ii", $newStatus, $id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Status admin berhasil diubah!";
            } else {
                $msg = "Gagal mengubah status.";
                $msgType = "danger";
            }
        }
    }

    // UBAH ROLE
    if ($_POST["action"] === "ubah_role") {
        $id      = (int) $_POST["id"];
        $newRole = $_POST["new_role"];

        if ($id === (int) $_SESSION["admin_id"]) {
            $msg = "Tidak dapat mengubah role akun Anda sendiri!";
            $msgType = "danger";
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE admin SET role = ? WHERE id = ?");
            mysqli_stmt_bind_param($stmt, "si", $newRole, $id);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Role admin berhasil diubah!";
            } else {
                $msg = "Gagal mengubah role.";
                $msgType = "danger";
            }
        }
    }
}

// Ambil semua admin
$adminList = [];
$res = mysqli_query($conn, "SELECT * FROM admin ORDER BY role DESC, nama ASC");
if ($res) {
    while ($row = mysqli_fetch_assoc($res)) {
        $adminList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin CePaT</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #2e7d32;
            --green-mid: #4caf50;
            --green-light: #e8f5e9;
            --sidebar-bg: #0d1f0e;
            --body-bg: #f5f7f5;
            --card-bg: #ffffff;
            --text-dark: #1a2e1b;
            --text-grey: #607d8b;
            --border: #e8ece8;
            --gold: #f9a825;
            --red: #e53935;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--body-bg);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 220px;
            background: var(--sidebar-bg);
            min-height: 100vh;
            position: fixed;
            display: flex;
            flex-direction: column;
        }

        .sidebar-top {
            padding: 22px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }

        .brand-name { font-family: 'Playfair Display', serif; color: #fff; font-size: 1.3rem; }
        .brand-sub  { color: rgba(255,255,255,0.35); font-size: 0.7rem; letter-spacing: 1.5px; text-transform: uppercase; }

        .sidebar nav { padding: 14px 0; }

        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 18px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }

        .nav-item:hover { color: #fff; background: rgba(255,255,255,0.05); }
        .nav-item.active { color: #f9a825; background: rgba(249,168,37,0.1); border-left-color: #f9a825; }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,0.07);
            margin-top: auto;
        }

        .logout-link { color: #ef9a9a; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; }

        .main {
            margin-left: 220px;
            flex: 1;
            padding: 30px;
        }

        .page-header { margin-bottom: 24px; }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            color: var(--text-dark);
        }

        .page-header p { color: var(--text-grey); font-size: 0.87rem; margin-top: 3px; }

        .alert-msg {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .alert-success { background: var(--green-light); color: var(--green); border: 1px solid #a5d6a7; }
        .alert-danger  { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }

        .two-col { display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start; }

        .form-card {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 24px;
        }

        .form-card h2 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .form-group { margin-bottom: 14px; }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 700;
            color: #37474f;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus, select:focus { border-color: var(--green-mid); }

        .btn-green {
            background: var(--green-mid);
            color: #fff;
            border: none;
            padding: 11px 20px;
            border-radius: 8px;
            font-size: 0.87rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            width: 100%;
            transition: background 0.2s;
        }

        .btn-green:hover { background: var(--green); }

        .table-card {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table-header {
            padding: 16px 22px;
            border-bottom: 1px solid var(--border);
        }

        .table-header h2 { font-size: 0.95rem; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; }

        th {
            padding: 11px 18px;
            text-align: left;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-grey);
            background: #fafbfa;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 13px 18px;
            font-size: 0.86rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbfa; }

        .role-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.7rem; font-weight: 700;
            padding: 3px 10px; border-radius: 50px;
        }

        .role-badge.superadmin { background: rgba(249,168,37,0.15); color: #e65100; border: 1px solid rgba(249,168,37,0.3); }
        .role-badge.konten     { background: var(--green-light); color: var(--green); border: 1px solid #a5d6a7; }

        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.7rem; font-weight: 700;
            padding: 3px 10px; border-radius: 50px;
        }

        .status-badge.active   { background: var(--green-light); color: var(--green); }
        .status-badge.inactive { background: #ffebee; color: var(--red); }

        .td-actions { display: flex; gap: 7px; align-items: center; flex-wrap: wrap; }

        .btn-sm {
            border: none;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.76rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: opacity 0.2s;
        }

        .btn-sm:hover { opacity: 0.8; }
        .btn-sm.danger  { background: #ffebee; color: var(--red); }
        .btn-sm.warning { background: #fff8e1; color: #e65100; }
        .btn-sm.info    { background: var(--green-light); color: var(--green); }

        .self-tag {
            font-size: 0.68rem;
            color: var(--text-grey);
            font-style: italic;
        }

        .count-info {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .count-box {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px 22px;
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .count-num { 
            font-size: 1.6rem; 
            font-weight: 700; 
        }

        .count-label { 
            color: var(--text-grey); 
            font-size: 0.8rem; 
        }

        .table-responsive {
            display: block; /* Sangat penting agar div mematuhi batas lebarnya */
            width: 100%;
            max-width: 100%; /* Mencegah div ikut melebar melebihi layar */
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 20px;
            /* Opsional: Tambahkan border agar batas scroll tabel terlihat jelas */
            border: 1px solid #e0e0e0; 
        }

        .table-responsive table {
            width: 100%;
            min-width: 700px; /* Paksa tabel memiliki lebar minimum agar tidak berdempetan, dan memicu scroll */
            border-collapse: collapse;
        }

          html, body {
            max-width: 100vw;
            overflow-x: hidden;
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

        /* Form menyesuaikan layar */
        .form-group {
            width: 100%;
        }
        input[type="text"], 
        input[type="password"], 
        select, 
        textarea {
            width: 100%;
            box-sizing: border-box; /* Agar padding tidak menambah lebar elemen */
        }
    
        /* Tombol simpan/batal menjadi sejajar ke bawah atau melebar */
        .btn-action {
            width: 100%;
            margin-bottom: 10px;
            text-align: center;
        }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-top">
        <div class="brand-name">CePaT</div>
        <div class="brand-sub">Super Admin</div>
    </div>
    <nav>
        <a href="admindashboard.php" class="nav-item">📊 Dashboard</a>
        <a href="admin_kelola_admin.php" class="nav-item active">👥 Kelola Admin</a>
        <a href="admin_infopenyakit.php" class="nav-item">📋 Info Penyakit</a>
        <a href="infopenyakit.php" class="nav-item" target="_blank">🌐 Lihat Publik</a>
    </nav>
    <div class="sidebar-footer">
        <a href="prosesadminlogout.php" class="logout-link">🚪 Logout</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h1>👥 Kelola Administrator</h1>
        <p>Tambah, hapus, dan atur peran administrator sistem CePaT. Hanya Super Admin yang dapat mengakses halaman ini.</p>
    </div>

    <?php if ($msg): ?>
    <div class="alert-msg alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Count boxes -->
    <?php
    $totalAdmin    = count($adminList);
    $superCount    = count(array_filter($adminList, fn($a) => $a['role'] === 'superadmin'));
    $kontenCount   = count(array_filter($adminList, fn($a) => $a['role'] === 'konten'));
    $activeCount   = count(array_filter($adminList, fn($a) => $a['is_active'] == 1));
    ?>
    <div class="count-info">
        <div class="count-box">
            <div>
                <div class="count-num"><?= $totalAdmin ?></div>
                <div class="count-label">Total Admin</div>
            </div>
        </div>
        <div class="count-box">
            <div>
                <div class="count-num" style="color:#e65100;"><?= $superCount ?></div>
                <div class="count-label">👑 Super Admin</div>
            </div>
        </div>
        <div class="count-box">
            <div>
                <div class="count-num" style="color:var(--green);"><?= $kontenCount ?></div>
                <div class="count-label">📋 Admin Konten</div>
            </div>
        </div>
        <div class="count-box">
            <div>
                <div class="count-num" style="color:#1565c0;"><?= $activeCount ?></div>
                <div class="count-label">✅ Admin Aktif</div>
            </div>
        </div>
    </div>

    <div class="two-col">
        <!-- Form tambah admin -->
        <div class="form-card">
            <h2>➕ Tambah Admin Baru</h2>
            <form method="POST" action="admin_kelola_admin.php">
                <input type="hidden" name="action" value="tambah">
                <div class="form-group">
                    <label>Nama Lengkap *</label>
                    <input type="text" name="nama" placeholder="Nama lengkap admin" required>
                </div>
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" placeholder="Username untuk login" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" placeholder="Password yang kuat" required>
                </div>
                <div class="form-group">
                    <label>Role / Peran *</label>
                    <select name="role">
                        <option value="konten">📋 Admin Konten – Kelola Info Penyakit</option>
                        <option value="superadmin">👑 Super Admin – Akses Penuh</option>
                    </select>
                </div>
                <button type="submit" class="btn-green">➕ Tambah Admin</button>
            </form>
        </div>

        <!-- Tabel admin -->
        <div class="table-card">
            <div class="table-header">
                <h2>Daftar Administrator (<?= $totalAdmin ?>)</h2>
            </div>
            <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adminList as $a): ?>
                    <?php $isSelf = ($a['id'] == $_SESSION["admin_id"]); ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($a['nama']) ?></strong>
                            <?php if ($isSelf): ?>
                            <span class="self-tag">(Anda)</span>
                            <?php endif; ?>
                        </td>
                        <td style="color:var(--text-grey);">@<?= htmlspecialchars($a['username']) ?></td>
                        <td>
                            <span class="role-badge <?= $a['role'] ?>">
                                <?= $a['role'] === 'superadmin' ? '👑 Super Admin' : '📋 Admin Konten' ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-badge <?= $a['is_active'] ? 'active' : 'inactive' ?>">
                                <?= $a['is_active'] ? '✅ Aktif' : '⛔ Nonaktif' ?>
                            </span>
                        </td>
                        <td class="td-actions">
                            <?php if (!$isSelf): ?>
                            <!-- Toggle aktif/nonaktif -->
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="is_active" value="<?= $a['is_active'] ?>">
                                <button type="submit" class="btn-sm <?= $a['is_active'] ? 'warning' : 'info' ?>">
                                    <?= $a['is_active'] ? '⏸ Nonaktifkan' : '▶ Aktifkan' ?>
                                </button>
                            </form>
                            <!-- Ubah role -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Ubah role admin ini?');">
                                <input type="hidden" name="action" value="ubah_role">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <input type="hidden" name="new_role" value="<?= $a['role'] === 'superadmin' ? 'konten' : 'superadmin' ?>">
                                <button type="submit" class="btn-sm info">
                                    🔄 Ubah ke <?= $a['role'] === 'superadmin' ? 'Konten' : 'SuperAdmin' ?>
                                </button>
                            </form>
                            <!-- Hapus -->
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus admin ini permanen?');">
                                <input type="hidden" name="action" value="hapus">
                                <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                <button type="submit" class="btn-sm danger">🗑️ Hapus</button>
                            </form>
                            <?php else: ?>
                            <span style="color:var(--text-grey); font-size:0.78rem;">Tidak dapat mengubah akun sendiri</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</main>

</body>
</html>