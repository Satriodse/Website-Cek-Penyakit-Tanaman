<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';

// Hanya admin yang bisa akses
if (!cek_login_admin()) {
    header("Location: loginpage.php"); exit();
}

$role = $_SESSION["admin_role"];
$msg  = '';
$msgType = 'success';

// ── PROSES TAMBAH ──
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"])) {

    if ($_POST["action"] === "tambah") {
        $judul      = trim($_POST["judul"]);
        $url        = trim($_POST["url_artikel"]);
        $tanaman    = trim($_POST["jenis_tanaman"]);
        $kategori   = trim($_POST["kategori"]);
        $deskripsi  = trim($_POST["deskripsi"]);

        if (empty($judul) || empty($url) || empty($tanaman)) {
            $msg = "Judul, URL, dan Jenis Tanaman wajib diisi!";
            $msgType = "danger";
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO info_penyakit (judul, url_artikel, jenis_tanaman, kategori, deskripsi, is_active, created_by) VALUES (?, ?, ?, ?, ?, 1, ?)");
            mysqli_stmt_bind_param($stmt, "ssssss", $judul, $url, $tanaman, $kategori, $deskripsi, $_SESSION["admin_username"]);
            if (mysqli_stmt_execute($stmt)) {
                $msg = "Artikel berhasil ditambahkan!";
            } else {
                $msg = "Gagal menambahkan artikel.";
                $msgType = "danger";
            }
        }
    }

    if ($_POST["action"] === "hapus") {
        $id = (int) $_POST["id"];
        $stmt = mysqli_prepare($conn, "DELETE FROM info_penyakit WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Artikel berhasil dihapus!";
        } else {
            $msg = "Gagal menghapus artikel.";
            $msgType = "danger";
        }
    }

    if ($_POST["action"] === "edit") {
        $id         = (int) $_POST["id"];
        $judul      = trim($_POST["judul"]);
        $url        = trim($_POST["url_artikel"]);
        $tanaman    = trim($_POST["jenis_tanaman"]);
        $kategori   = trim($_POST["kategori"]);
        $deskripsi  = trim($_POST["deskripsi"]);
        $is_active  = isset($_POST["is_active"]) ? 1 : 0;

        $stmt = mysqli_prepare($conn, "UPDATE info_penyakit SET judul=?, url_artikel=?, jenis_tanaman=?, kategori=?, deskripsi=?, is_active=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssssii", $judul, $url, $tanaman, $kategori, $deskripsi, $is_active, $id);
        if (mysqli_stmt_execute($stmt)) {
            $msg = "Artikel berhasil diperbarui!";
        } else {
            $msg = "Gagal memperbarui artikel.";
            $msgType = "danger";
        }
    }
}

// Ambil semua artikel
$artikelList = [];
$result = mysqli_query($conn, "SELECT * FROM info_penyakit ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $artikelList[] = $row;
    }
}

// Data edit (jika ada ?edit=id)
$editData = null;
if (isset($_GET["edit"])) {
    $editId = (int) $_GET["edit"];
    $res = mysqli_query($conn, "SELECT * FROM info_penyakit WHERE id = $editId");
    if ($res) $editData = mysqli_fetch_assoc($res);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Info Penyakit Admin CePaT</title>
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

        /* Sidebar (reuse dari dashboard) */
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

        .brand-name {
            font-family: 'Playfair Display', serif;
            color: #fff;
            font-size: 1.3rem;
        }

        .brand-sub { color: rgba(255,255,255,0.35); font-size: 0.7rem; letter-spacing: 1.5px; text-transform: uppercase; }

        .sidebar nav { padding: 14px 0; flex: 1; }

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
        .nav-item.active { color: #a5d6a7; background: rgba(76,175,80,0.12); border-left-color: #4caf50; }

        .sidebar-footer {
            padding: 14px 18px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }

        .logout-link {
            color: #ef9a9a;
            text-decoration: none;
            font-size: 0.85rem;
            display: flex; align-items: center; gap: 8px;
        }

        /* Main */
        .main {
            margin-left: 220px;
            flex: 1;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.7rem;
            color: var(--text-dark);
        }

        .page-header p { color: var(--text-grey); font-size: 0.87rem; margin-top: 3px; }

        .btn-green {
            background: var(--green-mid);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.87rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.2s;
        }

        .btn-green:hover { background: var(--green); }

        .btn-red {
            background: var(--red);
            color: #fff;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s;
        }

        .btn-red:hover { background: #c62828; }

        .btn-edit {
            background: #e3f2fd;
            color: #1565c0;
            border: none;
            padding: 7px 14px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'DM Sans', sans-serif;
            text-decoration: none;
            display: inline-block;
            transition: background 0.2s;
        }

        .btn-edit:hover { background: #bbdefb; }

        .alert-msg {
            padding: 12px 18px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .alert-success { background: var(--green-light); color: var(--green); border: 1px solid #a5d6a7; }
        .alert-danger  { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }

        /* Form card */
        .form-card {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            padding: 28px;
            margin-bottom: 28px;
        }

        .form-card h2 {
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
            color: var(--text-dark);
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            font-size: 0.78rem;
            font-weight: 700;
            color: #37474f;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        input[type="text"],
        input[type="url"],
        textarea,
        select {
            border: 1.5px solid var(--border);
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 0.9rem;
            font-family: 'DM Sans', sans-serif;
            color: var(--text-dark);
            background: #fff;
            transition: border-color 0.2s, box-shadow 0.2s;
            outline: none;
        }

        input:focus, textarea:focus, select:focus {
            border-color: var(--green-mid);
            box-shadow: 0 0 0 3px rgba(76,175,80,0.1);
        }

        textarea { resize: vertical; min-height: 90px; }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        /* Table */
        .table-card {
            background: var(--card-bg);
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
        }

        .table-header {
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 { 
            font-size: 0.95rem; 
            font-weight: 700;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
        }

        th {
            padding: 11px 20px;
            text-align: left;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: var(--text-grey);
            background: #fafbfa;
            border-bottom: 1px solid var(--border);
        }

        td {
            padding: 13px 20px;
            font-size: 0.86rem;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        tr:last-child td { 
            border-bottom: none; 
        }

        tr:hover td { 
            background: #fafbfa; 
        }

        .td-title { 
            font-weight: 600; 
            color: var(--text-dark); 
            max-width: 220px; 
        }

        .td-title a { 
            color: var(--green); 
            text-decoration: none; 
        }

        .td-title a:hover { 
            text-decoration: underline; 
        }

        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 50px;
        }

        .status-badge.active   { 
            background: var(--green-light); 
            color: var(--green); 
        }

        .status-badge.inactive { 
            background: #ffebee; 
            color: var(--red); 
        }

        .td-actions { 
            display: flex; 
            gap: 8px; 
            align-items: center; 
            white-space: nowrap; 
        }

        .empty-row td {
            text-align: center;
            color: var(--text-grey);
            padding: 40px;
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
        <div class="brand-sub">Admin Panel</div>
    </div>
    <nav>
        <a href="admindashboard.php" class="nav-item">📊 Dashboard</a>
        <a href="admin_infopenyakit.php" class="nav-item active">📋 Info Penyakit</a>
        <?php if ($role === 'superadmin'): ?>
        <a href="admin_kelola_admin.php" class="nav-item">👥 Kelola Admin</a>
        <?php endif; ?>
        <a href="infopenyakit.php" class="nav-item" target="_blank">🌐 Lihat Publik</a>
    </nav>
    <div class="sidebar-footer">
        <a href="prosesadminlogout.php" class="logout-link">🚪 Logout</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <div>
            <h1>Kelola Info Penyakit</h1>
            <p>Tambah, edit, dan hapus artikel informasi penyakit tanaman.</p>
        </div>
        <a href="infopenyakit.php" target="_blank" class="btn-green">🌐 Lihat Halaman Publik</a>
    </div>

    <?php if ($msg): ?>
    <div class="alert-msg alert-<?= $msgType ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <!-- Form Tambah / Edit -->
    <div class="form-card">
        <h2><?= $editData ? '✏️ Edit Artikel' : '➕ Tambah Artikel Baru' ?></h2>
        <form method="POST" action="admin_infopenyakit.php">
            <input type="hidden" name="action" value="<?= $editData ? 'edit' : 'tambah' ?>">
            <?php if ($editData): ?>
            <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <div class="form-grid">
                <div class="form-group full">
                    <label>Judul Artikel *</label>
                    <input type="text" name="judul" placeholder="Contoh: Penyakit Blas pada Padi: Gejala dan Penanganan"
                           value="<?= $editData ? htmlspecialchars($editData['judul']) : '' ?>" required>
                </div>
                <div class="form-group full">
                    <label>URL Artikel (Link ke Website) *</label>
                    <input type="url" name="url_artikel" placeholder="https://contoh.com/artikel-penyakit"
                           value="<?= $editData ? htmlspecialchars($editData['url_artikel']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Jenis Tanaman *</label>
                    <input type="text" name="jenis_tanaman" placeholder="Contoh: Padi, Cabai, Tomat"
                           value="<?= $editData ? htmlspecialchars($editData['jenis_tanaman']) : '' ?>" required>
                </div>
                <div class="form-group">
                    <label>Kategori Penyakit</label>
                    <input type="text" name="kategori" placeholder="Contoh: Jamur, Bakteri, Virus"
                           value="<?= $editData ? htmlspecialchars($editData['kategori']) : '' ?>">
                </div>
                <div class="form-group full">
                    <label>Deskripsi Singkat</label>
                    <textarea name="deskripsi" placeholder="Deskripsi singkat tentang penyakit ini..."><?= $editData ? htmlspecialchars($editData['deskripsi']) : '' ?></textarea>
                </div>
                <?php if ($editData): ?>
                <div class="form-group">
                    <label>Status</label>
                    <label style="flex-direction:row; align-items:center; gap:8px; text-transform:none; font-size:0.9rem; font-weight:500; cursor:pointer;">
                        <input type="checkbox" name="is_active" <?= $editData['is_active'] ? 'checked' : '' ?>> Aktif (tampil di halaman publik)
                    </label>
                </div>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-green">
                    <?= $editData ? '💾 Simpan Perubahan' : '➕ Tambah Artikel' ?>
                </button>
                <?php if ($editData): ?>
                <a href="admin_infopenyakit.php" class="btn-edit">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Tabel artikel -->
    <div class="table-card">
        <div class="table-header">
            <h2>Daftar Artikel (<?= count($artikelList) ?>)</h2>
        </div>
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Judul Artikel</th>
                    <th>Tanaman</th>
                    <th>Kategori</th>
                    <th>Ditambahkan Oleh</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($artikelList)): ?>
                <tr class="empty-row">
                    <td colspan="7">Belum ada artikel. Tambahkan artikel di atas.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($artikelList as $i => $a): ?>
                <tr>
                    <td style="color:var(--text-grey);"><?= $i + 1 ?></td>
                    <td class="td-title">
                        <a href="<?= htmlspecialchars($a['url_artikel']) ?>" target="_blank"><?= htmlspecialchars($a['judul']) ?></a>
                    </td>
                    <td><?= htmlspecialchars($a['jenis_tanaman']) ?></td>
                    <td><?= htmlspecialchars($a['kategori'] ?: '–') ?></td>
                    <td style="color:var(--text-grey);"><?= htmlspecialchars($a['created_by'] ?: '–') ?></td>
                    <td>
                        <span class="status-badge <?= $a['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $a['is_active'] ? '✅ Aktif' : '⛔ Nonaktif' ?>
                        </span>
                    </td>
                    <td class="td-actions">
                        <a href="admin_infopenyakit.php?edit=<?= $a['id'] ?>" class="btn-edit">✏️ Edit</a>
                        <form method="POST" action="admin_infopenyakit.php" onsubmit="return confirm('Hapus artikel ini?');">
                            <input type="hidden" name="action" value="hapus">
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="submit" class="btn-red">🗑️ Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>
</main>

</body>
</html>