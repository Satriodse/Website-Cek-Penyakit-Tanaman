<?php
session_start();
require_once 'koneksi.php';

// Ambil semua artikel dari database
$artikelList = [];
$result = mysqli_query($conn, "SELECT * FROM info_penyakit WHERE is_active = 1 ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $artikelList[] = $row;
    }
}

$isLoggedIn = isset($_SESSION["nama"]);
$isAdmin    = isset($_SESSION["admin_nama"]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Penyakit Tanaman CePaT</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,700;1,400&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --green: #2e7d32;
            --green-mid: #4caf50;
            --green-light: #e8f5e9;
            --dark: #1a2e1b;
            --grey: #607d8b;
            --border: #e8ece8;
            --body-bg: #f5f7f5;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--body-bg);
            color: var(--dark);
        }

        /* =========================================
           HEADER & NAVBAR BARU (SESUAI DESAIN)
           ========================================= */
      header {
    /* Membuat background putih transparan sebesar 70% */
            background: rgba(255, 255, 255, 0.7); 
    
    /* Memberikan efek blur/buram pada background di bawahnya */
            backdrop-filter: blur(10px); 
            -webkit-backdrop-filter: blur(10px);
    
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        nav {
            max-width: 1200px;
            margin: 0 auto;
            padding: 15px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .brand img {
            height: 40px; /* Ukuran logo disesuaikan */
        }

       .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: #4caf50; 
        }

        /* Menu Navigasi di Tengah */
        .nav-links {
            display: flex;
            gap: 30px;
        }

        .nav-link {
            color: #333333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            transition: color 0.3s;
        }

        .nav-link:hover, .nav-link.active {
            color: #2e7d32;
        }

        /* Area Sapaan & Tombol Kanan */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

       .greeting {
            font-size: 0.9rem;
            font-weight: 700;
            color: #333333;
        }
        /* Tombol Logout Merah */
        .btn-logout {
            background-color: #e53935;
            color: #fff;
            padding: 8px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: background 0.3s;
        }

        .btn-logout:hover {
            background-color: #c62828;
        }

        /* Tombol Login Hijau */
        .btn-login {
            background-color: var(--green-mid);
            color: #fff;
            padding: 8px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            transition: background 0.3s;
        }

        .btn-login:hover {
            background-color: var(--green);
        }

        /* =========================================
           HERO & CONTENT (TETAP SAMA SEPERTI ASLI)
           ========================================= */
        .hero {
            background: linear-gradient(135deg, #1fa928 0%, #4caf50 60%, #388e3c 100%);
            padding: 120px 24px 80px; /* padding atas diubah dari 70px ke 120px */
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        .hero-tag {
            display: inline-block;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.2);
            color: #333333;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 50px;
            margin-bottom: 18px;
        }

        .hero h1 {
            font-family: 'Poppins', serif;
            font-size: 2.8rem;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 14px;
        }

        .hero p {
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
            max-width: 520px;
            margin: 0 auto 28px;
            line-height: 1.7;
        }

        .search-wrap {
            display: flex;
            max-width: 480px;
            margin: 0 auto;
            position: relative;
        }

        .search-wrap input {
            width: 100%;
            padding: 14px 52px 14px 20px;
            border-radius: 10px;
            border: none;
            font-size: 0.93rem;
            font-family: 'Poppins', sans-serif;
            outline: none;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .search-btn {
            position: absolute;
            right: 8px; top: 50%;
            transform: translateY(-50%);
            background: var(--green-mid);
            border: none;
            color: #fff;
            width: 36px; height: 36px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
        }

        .content {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 28px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-label {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--grey);
        }

        .filter-btn {
            padding: 6px 16px;
            border-radius: 50px;
            border: 1.5px solid var(--border);
            background: #fff;
            color: var(--grey);
            font-size: 0.82rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Poppins', sans-serif;
        }

        .filter-btn.active,
        .filter-btn:hover {
            background: var(--green-light);
            border-color: var(--green-mid);
            color: var(--green);
        }

        .article-count {
            margin-left: auto;
            color: var(--grey);
            font-size: 0.83rem;
        }

        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 22px;
        }

        .article-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid var(--border);
            overflow: hidden;
            text-decoration: none;
            color: inherit;
            transition: transform 0.25s, box-shadow 0.25s;
            display: flex;
            flex-direction: column;
        }

        .article-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.09);
        }

        .card-top {
            height: 8px;
            background: linear-gradient(90deg, #4caf50, #81c784);
        }

        .card-body {
            padding: 22px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .card-tags {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }

        .tag {
            background: var(--green-light);
            color: var(--green);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 3px 10px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1.4;
            margin-bottom: 10px;
            flex: 1;
        }

        .card-desc {
            font-size: 0.83rem;
            color: var(--grey);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border);
            padding-top: 14px;
            margin-top: auto;
        }

        .card-source {
            font-size: 0.75rem;
            color: var(--grey);
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .card-source-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--green-mid);
        }

        .card-arrow {
            color: var(--green-mid);
            font-size: 1.1rem;
            transition: transform 0.2s;
        }

        .article-card:hover .card-arrow { transform: translateX(4px); }

        .empty-state {
            text-align: center;
            padding: 70px 20px;
            color: var(--grey);
        }

        .empty-state .icon { font-size: 3rem; margin-bottom: 14px; }
        .empty-state h3 { font-size: 1.1rem; font-weight: 700; color: var(--dark); margin-bottom: 8px; }
        .empty-state p { font-size: 0.88rem; }

        .admin-bar {
            background: #1a2e1b;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .admin-bar span {
            color: #a5d6a7;
            font-size: 0.82rem;
        }

        .admin-bar a {
            background: #4caf50;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: background 0.2s;
        }

        .admin-bar a:hover { background: #2e7d32; }

        footer {
            text-align: center;
            padding: 30px;
            color: var(--grey);
            font-size: 0.82rem;
            border-top: 1px solid var(--border);
            margin-top: 20px;
        }
    </style>
</head>
<body>

<?php if ($isAdmin): ?>
<div class="admin-bar">
    <span> Anda sedang login sebagai Admin: <strong><?= htmlspecialchars($_SESSION["admin_nama"]) ?></strong></span>
    <a href="admin_infopenyakit.php">✏️ Kelola Konten Ini</a>
</div>
<?php endif; ?>

<header>
    <nav>
        <a href="tugasweb.php" class="brand">
            <img src="../logocepat.png" alt="Logo CePaT">
            <span class="brand-name">CePaT</span>
        </a>
        
        <div class="nav-links">
            <a href="tugasweb.php" class="nav-link">BERANDA</a>
            <a href="Analisispage.php" class="nav-link">IDENTIFIKASI PENYAKIT</a>
            <a href="#" class="nav-link active">INFO PENYAKIT</a>
            <a href="#" class="nav-link">HASIL IDENTIFIKASI</a>
        </div>

        <div class="nav-actions">
            <?php if ($isLoggedIn): ?>
                <span class="greeting">Halo, <?= htmlspecialchars($_SESSION["username"]) ?>!</span>
                <a href="proseslogout.php" class="btn-logout">LOGOUT</a>
            <?php else: ?>
                <a href="loginpage.php" class="btn-login">LOGIN / DAFTAR</a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<section class="hero">
    <div class="hero-tag">Ensiklopedia Penyakit Tanaman</div>
    <h1>Info Penyakit<br>Tanaman Terlengkap</h1>
    <p>Kumpulan artikel dan sumber terpercaya tentang penyakit tanaman. Klik judul untuk membaca artikel lengkap.</p>
    <div class="search-wrap">
        <input type="text" id="searchInput" placeholder="Cari penyakit, tanaman, atau gejala..." oninput="filterCards()">
        <button class="search-btn">🔍</button>
    </div>
</section>

<div class="content">
    <div class="filter-bar">
        <span class="filter-label">Filter:</span>
        <button class="filter-btn active" onclick="filterByTag('semua', this)">Semua</button>
        <button class="filter-btn" onclick="filterByTag('padi', this)">Padi</button>
        <button class="filter-btn" onclick="filterByTag('cabai', this)">Cabai</button>
        <button class="filter-btn" onclick="filterByTag('tomat', this)">Tomat</button>
        <button class="filter-btn" onclick="filterByTag('jagung', this)">Jagung</button>
        <span class="article-count" id="count-label"><?= count($artikelList) ?> artikel tersedia</span>
    </div>

    <?php if (empty($artikelList)): ?>
    <div class="empty-state">
        <div class="icon"></div>
        <h3>Belum ada artikel</h3>
        <p>Admin belum menambahkan artikel info penyakit. Silakan cek kembali nanti.</p>
    </div>
    <?php else: ?>
    <div class="articles-grid" id="articlesGrid">
        <?php foreach ($artikelList as $artikel): ?>
        <a href="<?= htmlspecialchars($artikel['url_artikel']) ?>" target="_blank" rel="noopener"
           class="article-card"
           data-title="<?= strtolower(htmlspecialchars($artikel['judul'])) ?>"
           data-tag="<?= strtolower(htmlspecialchars($artikel['jenis_tanaman'])) ?>"
           data-deskripsi="<?= strtolower(htmlspecialchars($artikel['deskripsi'])) ?>">
            <div class="card-top"></div>
            <div class="card-body">
                <div class="card-tags">
                    <span class="tag"><?= htmlspecialchars($artikel['jenis_tanaman']) ?></span>
                    <?php if (!empty($artikel['kategori'])): ?>
                    <span class="tag"><?= htmlspecialchars($artikel['kategori']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="card-title"><?= htmlspecialchars($artikel['judul']) ?></div>
                <div class="card-desc"><?= htmlspecialchars(substr($artikel['deskripsi'], 0, 100)) ?>...</div>
                <div class="card-footer">
                    <div class="card-source">
                        <div class="card-source-dot"></div>
                        <?= htmlspecialchars(parse_url($artikel['url_artikel'], PHP_URL_HOST) ?: 'Sumber Terpercaya') ?>
                    </div>
                    <span class="card-arrow">→</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<footer>
    &copy; 2026 CePaT Portal Cek Penyakit Tanaman. Semua Hak Dilindungi.
</footer>

<script>
let activeTag = 'semua';

function filterCards() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    const cards = document.querySelectorAll('.article-card');
    let visible = 0;

    cards.forEach(card => {
        const titleMatch = card.dataset.title.includes(q);
        const descMatch  = card.dataset.deskripsi.includes(q);
        const tagMatch   = activeTag === 'semua' || card.dataset.tag.includes(activeTag);

        if ((titleMatch || descMatch) && tagMatch) {
            card.style.display = 'flex';
            visible++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('count-label').textContent = visible + ' artikel tersedia';
}

function filterByTag(tag, btn) {
    activeTag = tag;
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    filterCards();
}
</script>
</body>
</html>