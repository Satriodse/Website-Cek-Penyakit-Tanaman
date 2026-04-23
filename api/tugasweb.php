<?php
session_start();
// Jika belum login, redirect ke halaman login
if (!isset($_SESSION["nama"])) {
    header("Location: loginpage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Penyakit Tanaman (CePaT)</title>
    <link rel="stylesheet" href="../css/tugasweb.css">
</head>
<body>

<!--Bagian Navigasi-->
<header class="main-header">
    <nav class="container">
        <div class="logo-area">
            <img src="../logocepat.png" alt="Logo CePaT">
            <span class="logo-text">CePaT</span>
        </div>
        <ul class="nav-links">
            <li><a href="#" class="active">BERANDA</a></li>
            <li><a href="Analisispage.php">IDENTIFIKASI PENYAKIT</a></li>
            <li><a href="infopenyakit.php">INFO PENYAKIT</a></li>
            <li><a href="#">HASIL IDENTIFIKASI</a></li>
        </ul>
        <div class="auth-buttons">
            <!-- Tampilkan nama pengguna yang sedang login -->
            <span style="font-weight: 600; color: #555555; align-self: center;">
                Halo, <?= htmlspecialchars($_SESSION["username"]) ?>!
            </span>
            <!-- Tombol logout mengarah ke proseslogout.php -->
            <a href="proseslogout.php" class="logout-btn" style="background-color: #e53935; color: #ffffff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 14px; transition: background-color 0.3s;">LOGOUT</a>
        </div>
    </nav>
</header>

<!--Area Banner Utama-->
<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-text">
            <h1>DETEKSI DINI &<br> KENDALIKAN PENYAKIT<br> TANAMAN ANDA</h1>
            <p>Platform Digital Pintar untuk Diagnosis Akurat, Penanganan Efektif, dan Peningkatan Hasil Panen Petani Indonesia.</p>
           <a href="Analisispage.php" class="cta-btn green-btn">
                <img src="../logokamera.png" alt="kamera">
                MULAI IDENTIFIKASI SEKARANG
            </a>
        </div>
    </div>
</section>

<!--Featured Card-->
<section class="feature-cards section-padding">
    <div class="container feature-container">
        <div class="feature-card">
            <div class="icon-group">
                <img src="../daun.jpg" alt="Ikon Daun">
            </div>
            <h3>IDENTIFIKASI PENYAKIT</h3>
            <p>Unggah foto tanaman sakit untuk diagnosis cepat.</p>
        </div>
        <div class="feature-card">
            <div class="icon-group">
                <img src="../buku.png" alt="Ikon Buku">
            </div>
            <h3>INFO PENYAKIT</h3>
            <p>Cari informasi lengkap tentang ribuan jenis penyakit, gejala, dan cara mengatasi.</p>
        </div>
    </div>
</section>

<!--Section Data BPS-->
<section style="background:#fff;padding:60px 0;border-top:1px solid #e0e0e0;">
    <div class="container">

        <!-- Judul -->
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:14px;margin-bottom:20px;">
            <div>
                <p style="font-size:.7rem;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#4caf50;margin:0 0 5px;">📊 Data Resmi BPS</p>
                <h2 style="font-size:1.4rem;font-weight:800;color:#2e7d32;margin:0 0 4px;">Produksi Padi Menurut Provinsi (Ton)</h2>
                <p style="font-size:.8rem;color:#757575;margin:0;">Sumber: Badan Pusat Statistik Indonesia &middot; Ditampilkan otomatis dari API BPS</p>
            </div>
            <div style="background:#e8f5e9;border:1px solid #c8e6c9;border-radius:10px;padding:10px 20px;text-align:center;min-width:80px;">
                <span id="bps-jml" style="display:block;font-size:1.5rem;font-weight:800;color:#2e7d32;line-height:1;">&ndash;</span>
                <span style="font-size:.65rem;color:#757575;font-weight:600;text-transform:uppercase;letter-spacing:.5px;">Provinsi</span>
            </div>
        </div>

        <!-- Toolbar -->
        <div style="display:flex;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
            <div style="position:relative;flex:1;min-width:180px;">
                <span style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#9e9e9e;font-size:.85rem;pointer-events:none;">🔍</span>
                <input id="bps-cari" type="text" placeholder="Cari provinsi..."
                    oninput="bpsFilter()"
                    style="width:100%;padding:10px 14px 10px 34px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.85rem;font-family:'Poppins',sans-serif;outline:none;background:#f8fdf8;color:#333;box-sizing:border-box;">
            </div>
            <select id="bps-bulan" onchange="bpsFilter()"
                style="padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.85rem;font-family:'Poppins',sans-serif;outline:none;background:#f8fdf8;color:#333;cursor:pointer;min-width:155px;">
                <option value="all">Semua Bulan</option>
            </select>
            <select id="bps-sort" onchange="bpsFilter()"
                style="padding:10px 14px;border:1.5px solid #e0e0e0;border-radius:8px;font-size:.85rem;font-family:'Poppins',sans-serif;outline:none;background:#f8fdf8;color:#333;cursor:pointer;min-width:155px;">
                <option value="">Urutan Default</option>
                <option value="desc">Total: Terbesar</option>
                <option value="asc">Total: Terkecil</option>
                <option value="az">Nama A&ndash;Z</option>
            </select>
        </div>

        <!-- Loading -->
        <div id="bps-loading" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:60px 20px;border:1.5px solid #e0e0e0;border-radius:12px;text-align:center;">
            <div style="width:42px;height:42px;border:3px solid #e8f5e9;border-top-color:#4caf50;border-radius:50%;animation:bspin .75s linear infinite;"></div>
            <p style="font-size:.87rem;color:#757575;margin:0;">Mengambil data dari BPS&hellip;</p>
        </div>

        <!-- Error -->
        <div id="bps-error" style="display:none;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:60px 20px;border:1.5px solid #ffcdd2;border-radius:12px;text-align:center;background:#fff8f8;">
            <span style="font-size:2rem;">&#9888;&#65039;</span>
            <p id="bps-err-msg" style="font-size:.87rem;color:#c62828;font-weight:600;margin:0;">Gagal memuat data.</p>
            <button onclick="bpsMuat()" style="padding:9px 20px;background:#e53935;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;font-family:'Poppins',sans-serif;">&#8635; Coba Lagi</button>
        </div>

        <!-- Tabel -->
        <div id="bps-box" style="display:none;border:1px solid #e0e0e0;border-radius:12px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05);">
            <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;">
                <table style="width:100%;border-collapse:collapse;min-width:680px;">
                    <thead id="bps-thead" style="background:linear-gradient(135deg,#1b5e20,#2e7d32);"></thead>
                    <tbody id="bps-tbody"></tbody>
                </table>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:11px 16px;background:#f8fdf8;border-top:1px solid #e0e0e0;font-size:.75rem;color:#9e9e9e;flex-wrap:wrap;gap:6px;">
                <span id="bps-info">&ndash;</span>
                <b style="color:#4caf50;">Sumber: webapi.bps.go.id &middot; var/2506</b>
            </div>
        </div>

    </div>
</section>

<!-- CSS animasi spinner -->
<style>
@keyframes bspin { to { transform:rotate(360deg); } }
.bps-th { padding:13px 15px;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.9);white-space:nowrap;border:none; }
.bps-th-r { text-align:right !important; }
.bps-td-no { padding:11px 15px;text-align:center;color:#bdbdbd;font-size:.74rem; }
.bps-td-prov { padding:11px 15px;font-weight:700;color:#2e7d32;white-space:nowrap; }
.bps-td-n { padding:11px 15px;text-align:right;white-space:nowrap;font-size:.84rem;color:#1a237e;font-weight:600; }
.bps-td-e { padding:11px 15px;text-align:right;white-space:nowrap;font-size:.84rem;color:#bdbdbd; }
.bps-tr:nth-child(even) td { background:#fafff8; }
.bps-tr:hover td { background:#f1f8e9 !important; }
.bps-tot td { background:#e8f5e9 !important;font-weight:800;color:#1b5e20;border-top:2px solid #c8e6c9; }
.bps-empty { text-align:center;padding:40px;color:#9e9e9e;font-size:.86rem; }
</style>

<!--Bagian Footer-->
<footer class="main-footer section-padding text-center">
    <div class="container footer-container">
        <p>&copy; 2026 Website Cek Penyakit Tanaman (CePaT). Semua Hak Dilindungi.</p>
    </div>
</footer>

    <script src="../tugasweb.js"></script>
    <script>
    /* ── BPS DATA MODULE ───────────────────────────────────
     * Struktur JSON BPS (model/data):
     *   data[0] = info paginasi
     *   data[1] = label baris/vertikal  → provinsi
     *   data[2] = label kolom/horizontal → bulan
     *   data[3] = nilai flat (baris × kolom)
     * ─────────────────────────────────────────────────── */

    var _prov = [], _bln = [], _filt = [];

    // ── 1. AMBIL DATA DARI api.php ─────────────────────
    // Struktur JSON BPS yang BENAR:
    //   j.vervar    = [{val:1100, label:"ACEH"}, ...]          → 38 provinsi
    //   j.turtahun  = [{val:1, label:"Januari"}, ...]          → bulan
    //   j.tahun     = [{val:126, label:"2026"}]                → tahun
    //   j.var       = [{val:2506, ...}]                        → variabel
    //   j.datacontent = {"1100250601261": 55380.19, ...}       → nilai
    //
    // Format KEY datacontent: {vervar_val}{var_val}{tahun_val}{turtahun_val}
    // Contoh: "1100250601261"
    //   1100 = ACEH, 2506 = var, 126 = tahun 2026, 1 = Januari
    async function bpsMuat() {
        document.getElementById('bps-loading').style.display = 'flex';
        document.getElementById('bps-error').style.display   = 'none';
        document.getElementById('bps-box').style.display     = 'none';

        try {
            var res = await fetch('api.php');
            if (!res.ok) throw new Error('HTTP ' + res.status);

            var j = await res.json();

            if (j.error)  throw new Error(j.error);
            if (j.status && j.status !== 'OK')
                throw new Error(j['data-availability'] || 'Data tidak tersedia di BPS.');

            // ── Ambil komponen dari JSON BPS ──
            var vervar     = j.vervar;     // array provinsi
            var turtahun   = j.turtahun;   // array bulan
            var tahun      = j.tahun;      // array tahun
            var varData    = j.var;        // array variabel
            var datacontent = j.datacontent; // object nilai

            if (!vervar || !turtahun || !tahun || !datacontent)
                throw new Error('Struktur JSON tidak lengkap. Cek respons API.');

            // Ambil nilai kode untuk membangun key
            // Format key datacontent BPS:
            // {prov_val 4digit}{var_val 4digit}{tahun_val 4digit leadingzero}{bulan_val}
            // Contoh: 1100 + 2506 + 0126 + 1 = "1100250601261" → ACEH, Januari 2026

            // Pad number ke N digit dengan leading zero
            function pad(n, len) {
                return String(n).padStart(len, '0');
            }

            var kodeVar   = pad(varData[0].val, 4);   // "2506"
            var kodeTahun = pad(tahun[0].val, 4);     // "0126"

            // Bulan yang tersedia (exclude Tahunan val=13)
            var bulanList = turtahun.filter(function(b){ return b.val !== 13; });

            // Provinsi yang ditampilkan (exclude INDONESIA val=9999)
            var provList = vervar.filter(function(p){ return p.val !== 9999; });

            // ── Bangun _bln ──
            _bln = bulanList.map(function(b){ return b.label; });

            // ── Bangun _prov ──
            _prov = provList.map(function(p) {
                var kodeProv = pad(p.val, 4);
                var vals = bulanList.map(function(b) {
                    // Key: {prov 4digit}{var 4digit}{tahun 4digit}{bulan val}
                    var key = kodeProv + kodeVar + kodeTahun + String(b.val);
                    var v   = datacontent[key];
                    return (v !== undefined && v !== null) ? parseFloat(v) : null;
                });
                return { label: p.label, vals: vals };
            });

            // Isi dropdown bulan
            var sel = document.getElementById('bps-bulan');
            sel.innerHTML = '<option value="all">Semua Bulan</option>';
            _bln.forEach(function(b, i) {
                var o = document.createElement('option');
                o.value = i; o.textContent = b; sel.appendChild(o);
            });

            document.getElementById('bps-jml').textContent = _prov.length;
            _filt = _prov.slice();
            bpsRender('all');

            document.getElementById('bps-loading').style.display = 'none';
            document.getElementById('bps-box').style.display     = 'block';

        } catch (e) {
            document.getElementById('bps-loading').style.display = 'none';
            document.getElementById('bps-error').style.display   = 'flex';
            document.getElementById('bps-err-msg').textContent   = e.message || 'Terjadi kesalahan.';
            console.error('[BPS]', e);
        }
    }

    // ── 2. FILTER & SORT ───────────────────────────────
    function bpsFilter() {
        var q  = document.getElementById('bps-cari').value.toLowerCase();
        var bl = document.getElementById('bps-bulan').value;
        var sr = document.getElementById('bps-sort').value;

        _filt = _prov.filter(function(p) {
            return p.label.toLowerCase().indexOf(q) !== -1;
        });

        if (sr === 'asc' || sr === 'desc') {
            _filt.sort(function(a, b) {
                var ta = bpsTot(a.vals, bl), tb = bpsTot(b.vals, bl);
                return sr === 'asc' ? ta - tb : tb - ta;
            });
        } else if (sr === 'az') {
            _filt.sort(function(a, b) { return a.label.localeCompare(b.label); });
        }

        bpsRender(bl);
    }

    function bpsTot(vals, bl) {
        if (bl === 'all') return vals.reduce(function(s,v){ return s+(v||0); }, 0);
        return vals[parseInt(bl)] || 0;
    }

    // ── 3. RENDER TABEL ────────────────────────────────
    function bpsRender(bl) {
        var thead = document.getElementById('bps-thead');
        var tbody = document.getElementById('bps-tbody');
        var info  = document.getElementById('bps-info');

        var idx     = (bl === 'all') ? _bln.map(function(_,i){return i;}) : [parseInt(bl)];
        var showTot = (bl === 'all' && _bln.length > 1);
        var cols    = idx.length + 2 + (showTot ? 1 : 0);

        // ── Thead ──
        var th = '<tr>'
            + '<th class="bps-th" style="width:42px;text-align:center;">#</th>'
            + '<th class="bps-th" style="min-width:175px;">Provinsi</th>';
        idx.forEach(function(i) {
            th += '<th class="bps-th bps-th-r">' + bpsEsc(_bln[i]||'') + '</th>';
        });
        if (showTot) th += '<th class="bps-th bps-th-r">Total</th>';
        th += '</tr>';
        thead.innerHTML = th;

        // ── Tbody ──
        if (_filt.length === 0) {
            tbody.innerHTML = '<tr><td colspan="' + cols + '" class="bps-empty">Tidak ada data yang cocok.</td></tr>';
            info.textContent = 'Menampilkan 0 data';
            return;
        }

        var html = '', gt = new Array(idx.length).fill(0);

        _filt.forEach(function(p, i) {
            html += '<tr class="bps-tr">'
                + '<td class="bps-td-no">' + (i+1) + '</td>'
                + '<td class="bps-td-prov">' + bpsEsc(p.label) + '</td>';

            var rt = 0;
            idx.forEach(function(bi, j) {
                var v = p.vals[bi];
                if (v !== null && v !== undefined) {
                    gt[j] += v; rt += v;
                    html += '<td class="bps-td-n">' + bpsFmt(v) + '</td>';
                } else {
                    html += '<td class="bps-td-e">&ndash;</td>';
                }
            });

            if (showTot) html += '<td class="bps-td-n">' + (rt > 0 ? bpsFmt(rt) : '&ndash;') + '</td>';
            html += '</tr>';
        });

        // Baris total
        if (_filt.length > 1) {
            html += '<tr class="bps-tot"><td></td><td class="bps-td-prov" style="padding:11px 15px;">TOTAL</td>';
            var gs = 0;
            gt.forEach(function(v) { gs += v; html += '<td class="bps-td-n">' + (v>0 ? bpsFmt(v) : '&ndash;') + '</td>'; });
            if (showTot) html += '<td class="bps-td-n">' + (gs>0 ? bpsFmt(gs) : '&ndash;') + '</td>';
            html += '</tr>';
        }

        tbody.innerHTML = html;
        info.textContent = 'Menampilkan ' + _filt.length + ' dari ' + _prov.length + ' provinsi';
    }

    // ── 4. HELPER ──────────────────────────────────────
    function bpsFmt(n) {
        return Number(n).toLocaleString('id-ID', { maximumFractionDigits: 2 });
    }

    function bpsEsc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── AUTO-LOAD ──────────────────────────────────────
    document.addEventListener('DOMContentLoaded', bpsMuat);
    </script>
</body>
</html>