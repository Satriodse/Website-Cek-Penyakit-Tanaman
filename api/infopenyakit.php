<?php
session_start();
require_once 'koneksi.php';
require_once 'auth_helper.php';

// Wajib login — redirect jika belum
if (!cek_login_pengguna() && !cek_login_admin()) {
    header("Location: loginpage.php"); exit();
}

// Ambil semua artikel dari database
$artikelList = [];
$result = mysqli_query($conn, "SELECT * FROM info_penyakit WHERE is_active = 1 ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $artikelList[] = $row;
    }
}

$isAdmin = isset($_SESSION["admin_nama"]);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Info Penyakit Tanaman CePaT</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── RESET & ROOT ───────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        :root {
            --fresh-green:    #4caf50;
            --dark-green:     #2e7d32;
            --light-green-bg: #f1f8e9;
            --green-light:    #e8f5e9;
            --pure-white:     #ffffff;
            --text-dark:      #333333;
            --text-grey:      #555555;
            --border-color:   #e0e0e0;
            --body-bg:        #f5f7f5;
        }
        body { background: var(--body-bg); color: var(--text-dark); line-height: 1.6; }

        /* ── HEADER / NAVBAR (sama persis dgn tugasweb.php) ── */
        .main-header {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border-bottom: 1px solid rgba(224, 224, 224, 0.5);
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .main-header nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .logo-area { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .logo-area img { height: 40px; width: 40px; object-fit: contain; }
        .logo-text { font-size: 24px; font-weight: 700; color: var(--fresh-green); }
        .nav-links { list-style: none; display: flex; gap: 25px; }
        .nav-links li a {
            text-decoration: none;
            color: var(--text-grey);
            font-weight: 500;
            transition: color .3s;
            font-size: 14px;
        }
        .nav-links li a:hover,
        .nav-links li a.active { color: var(--fresh-green); font-weight: 600; }
        .auth-buttons { display: flex; gap: 10px; align-items: center; }
        .greeting { font-weight: 600; color: #555; font-size: 14px; }
        .logout-btn {
            background: #e53935;
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background .3s;
        }
        .logout-btn:hover { background: #c62828; }
        .login-btn {
            background: var(--fresh-green);
            color: #fff;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: background .3s;
        }
        .login-btn:hover { background: var(--dark-green); }

        /* ── HAMBURGER ───────────────────────────────────── */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            background: none;
            border: none;
            padding: 4px;
        }
        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: var(--text-dark);
            border-radius: 2px;
            transition: all .3s;
        }
        .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }

        /* ── MOBILE DRAWER ───────────────────────────────── */
        .mobile-menu {
            display: none;
            flex-direction: column;
            background: rgba(255, 255, 255, 0.98);
            border-top: 1px solid var(--border-color);
            padding: 16px 20px 20px;
        }
        .mobile-menu.open { display: flex; }
        .mobile-menu a {
            text-decoration: none;
            color: var(--text-grey);
            font-weight: 500;
            font-size: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            transition: color .2s;
        }
        .mobile-menu a:hover,
        .mobile-menu a.active { color: var(--fresh-green); }
        .mobile-menu .mobile-auth { margin-top: 14px; display: flex; flex-direction: column; gap: 10px; }
        .mobile-menu .mobile-auth .greeting { font-weight: 600; color: #555; font-size: 14px; }
        .mobile-menu .mobile-auth .logout-btn,
        .mobile-menu .mobile-auth .login-btn { text-align: center; }

        /* ── ADMIN BAR ───────────────────────────────────── */
        .admin-bar {
            background: #1a2e1b;
            padding: 10px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 72px;
            flex-wrap: wrap;
            gap: 8px;
        }
        .admin-bar span { color: #a5d6a7; font-size: 0.82rem; }
        .admin-bar a {
            background: #4caf50;
            color: #fff;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: background .2s;
        }
        .admin-bar a:hover { background: #2e7d32; }

        /* ── HERO ────────────────────────────────────────── */
        .hero {
            background: linear-gradient(135deg, #1fa928 0%, #4caf50 60%, #388e3c 100%);
            padding: 100px 24px 70px;
            text-align: center;
            position: relative;
            overflow: hidden;
            margin-top: 72px;
        }
        .hero.has-admin-bar { margin-top: 0; }
        .hero::before {
            content: '';
            position: absolute;
            top: -60px; right: -60px;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
        }
        .hero-tag {
            display: inline-block;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 6px 18px;
            border-radius: 50px;
            margin-bottom: 18px;
        }
        .hero h1 {
            font-size: 2.6rem;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 14px;
            font-weight: 800;
        }
        .hero p {
            color: rgba(255, 255, 255, 0.85);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        .search-btn {
            position: absolute;
            right: 8px; top: 50%;
            transform: translateY(-50%);
            background: var(--fresh-green);
            border: none;
            color: #fff;
            width: 36px; height: 36px;
            border-radius: 7px;
            cursor: pointer;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center;
        }

        /* ── CONTENT ─────────────────────────────────────── */
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
        .filter-label { font-size: 0.82rem; font-weight: 600; color: var(--text-grey); }
        .filter-btn {
            padding: 6px 16px;
            border-radius: 50px;
            border: 1.5px solid var(--border-color);
            background: #fff;
            color: var(--text-grey);
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .2s;
            font-family: 'Poppins', sans-serif;
        }
        .filter-btn:hover { border-color: var(--fresh-green); color: var(--fresh-green); }
        .filter-btn.active {
            background: var(--fresh-green);
            border-color: var(--fresh-green);
            color: #fff;
        }
        .article-count { font-size: 0.8rem; color: var(--text-grey); margin-left: auto; }

        /* ── GRID ARTIKEL ────────────────────────────────── */
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 22px;
        }
        .article-card {
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            transition: transform .2s, box-shadow .2s;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.07);
        }
        .article-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.11);
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
        .card-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 12px; }
        .tag {
            background: var(--green-light);
            color: var(--dark-green);
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
            color: var(--text-dark);
            line-height: 1.4;
            margin-bottom: 10px;
            flex: 1;
        }
        .card-desc {
            font-size: 0.83rem;
            color: var(--text-grey);
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--border-color);
            padding-top: 14px;
            margin-top: auto;
        }
        .card-source {
            font-size: 0.75rem;
            color: var(--text-grey);
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .card-source-dot {
            width: 6px; height: 6px;
            border-radius: 50%;
            background: var(--fresh-green);
        }
        .card-arrow { color: var(--fresh-green); font-size: 1.1rem; transition: transform .2s; }
        .article-card:hover .card-arrow { transform: translateX(4px); }

        /* ── EMPTY STATE ─────────────────────────────────── */
        .empty-state { text-align: center; padding: 70px 20px; color: var(--text-grey); }
        .empty-state .icon { font-size: 3rem; margin-bottom: 14px; }
        .empty-state h3 { font-size: 1.1rem; font-weight: 700; color: var(--text-dark); margin-bottom: 8px; }
        .empty-state p { font-size: 0.88rem; }

        /* ── FOOTER ──────────────────────────────────────── */
        footer {
            text-align: center;
            padding: 30px;
            color: var(--text-grey);
            font-size: 0.82rem;
            border-top: 1px solid var(--border-color);
            margin-top: 20px;
        }

        /* ── RESPONSIVE ──────────────────────────────────── */
        @media (max-width: 1024px) {
            .articles-grid { grid-template-columns: repeat(2, 1fr); }
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .auth-buttons { display: none; }
            .hamburger { display: flex; }

            .hero { margin-top: 68px; padding: 70px 16px 50px; }
            .admin-bar { margin-top: 68px; }
            .hero h1 { font-size: 1.9rem; }
            .hero p { font-size: 0.9rem; }

            .articles-grid { grid-template-columns: 1fr; }
            .content { padding: 28px 16px; }
            .filter-bar { gap: 8px; }
            .article-count { margin-left: 0; width: 100%; }
            .search-wrap { max-width: 100%; }
        }

        @media (max-width: 480px) {
            .hero h1 { font-size: 1.6rem; }
            .hero-tag { font-size: 0.65rem; }
        }
    </style>
</head>
<body>

<!-- ── HEADER ──────────────────────────────────────────── -->
<header class="main-header">
    <nav>
        <a href="tugasweb.php" class="logo-area">
            <img src="../logocepat.png" alt="Logo CePaT">
            <span class="logo-text">CePaT</span>
        </a>

        <ul class="nav-links">
            <li><a href="tugasweb.php">BERANDA</a></li>
            <li><a href="Analisispage.php">IDENTIFIKASI PENYAKIT</a></li>
            <li><a href="infopenyakit.php" class="active">INFO PENYAKIT</a></li>
            <li><a href="hasil_diagnosa.php">HASIL DIAGNOSA</a></li>
        </ul>

        <div class="auth-buttons">
            <?php if ($isAdmin): ?>
                <span style="font-weight:600;color:#555;align-self:center;">Halo, <?= htmlspecialchars($_SESSION["admin_nama"]) ?>!</span>
            <?php else: ?>
                <span style="font-weight:600;color:#555;align-self:center;">Halo, <?= htmlspecialchars($_SESSION["username"]) ?>!</span>
            <?php endif; ?>
            <a href="proseslogout.php" class="logout-btn">LOGOUT</a>
        </div>

        <button class="hamburger" id="hamburger" aria-label="Menu" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- Mobile drawer -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="tugasweb.php">BERANDA</a>
        <a href="Analisispage.php">IDENTIFIKASI PENYAKIT</a>
        <a href="infopenyakit.php" class="active">INFO PENYAKIT</a>
        <a href="hasil_diagnosa.php">HASIL DIAGNOSA</a>
        <div class="mobile-auth">
            <?php if ($isAdmin): ?>
                <span style="font-weight:600;color:#555;font-size:14px;">Halo, <?= htmlspecialchars($_SESSION["admin_nama"]) ?>!</span>
            <?php else: ?>
                <span style="font-weight:600;color:#555;font-size:14px;">Halo, <?= htmlspecialchars($_SESSION["username"]) ?>!</span>
            <?php endif; ?>
            <a href="proseslogout.php" class="logout-btn">LOGOUT</a>
        </div>
    </div>
</header>

<?php if ($isAdmin): ?>
<div class="admin-bar">
    <span>✏️ Anda login sebagai Admin: <strong><?= htmlspecialchars($_SESSION["admin_nama"]) ?></strong></span>
    <a href="admin_infopenyakit.php">Kelola Konten Ini</a>
</div>
<?php endif; ?>

<!-- ── HERO ────────────────────────────────────────────── -->
<section class="hero <?= $isAdmin ? 'has-admin-bar' : '' ?>">
    <div class="hero-tag">Ensiklopedia Penyakit Tanaman</div>
    <h1>Info Penyakit<br>Tanaman Terlengkap</h1>
    <p>Kumpulan artikel dan sumber terpercaya tentang penyakit tanaman. Klik judul untuk membaca artikel lengkap.</p>
    <div class="search-wrap">
        <input type="text" id="searchInput" placeholder="Cari penyakit, tanaman, atau gejala..." oninput="filterCards()">
        <button class="search-btn">🔍</button>
    </div>
</section>

<!-- ── KONTEN ARTIKEL ───────────────────────────────────── -->
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
        <div class="icon">📋</div>
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
                <div class="card-desc"><?= htmlspecialchars(substr($artikel['deskripsi'], 0, 110)) ?>...</div>
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

<!-- ── FOOTER ───────────────────────────────────────────── -->
<footer>
    &copy; 2026 CePaT — Portal Cek Penyakit Tanaman. Semua Hak Dilindungi.
</footer>

<script>
    /* ── Hamburger toggle ── */
    function toggleMenu() {
        const btn  = document.getElementById('hamburger');
        const menu = document.getElementById('mobileMenu');
        btn.classList.toggle('open');
        menu.classList.toggle('open');
    }

    /* ── Filter & Search ── */
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