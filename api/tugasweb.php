<?php
session_start();
require_once 'auth_helper.php';
if (!cek_login_pengguna()) {
    header("Location: loginpage.php"); exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Penyakit Tanaman (CePaT)</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* ── RESET & ROOT ─────────────────────────────── */
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Poppins',sans-serif; }
        :root {
            --fresh-green: #4caf50;
            --dark-green:  #2e7d32;
            --light-green-bg: #f1f8e9;
            --pure-white:  #ffffff;
            --text-dark:   #333333;
            --text-grey:   #555555;
            --border-color:#e0e0e0;
        }
        body { background:#fff; color:var(--text-dark); line-height:1.6; }
        .container { max-width:1200px; margin:0 auto; padding:0 20px; }
        .section-padding { padding:60px 0; }
        .text-center { text-align:center; }

        /* ── HEADER ───────────────────────────────────── */
        .main-header {
            background:rgba(255,255,255,0.92);
            backdrop-filter:blur(8px);
            border-bottom:1px solid rgba(224,224,224,0.5);
            position:fixed; width:100%; top:0; left:0; z-index:1000;
        }
        .main-header nav {
            display:flex; justify-content:space-between;
            align-items:center; padding:15px 20px;
        }
        .logo-area { display:flex; align-items:center; gap:10px; }
        .logo-area img { height:40px; width:40px; object-fit:contain; }
        .logo-text { font-size:24px; font-weight:700; color:var(--fresh-green); }
        .nav-links { list-style:none; display:flex; gap:25px; }
        .nav-links li a {
            text-decoration:none; color:var(--text-grey);
            font-weight:500; transition:color .3s; font-size:14px;
        }
        .nav-links li a:hover, .nav-links li a.active { color:var(--fresh-green); }
        .auth-buttons { display:flex; gap:10px; align-items:center; }
        .logout-btn {
            background:#e53935 !important; color:#fff !important;
            padding:10px 20px; border-radius:6px; text-decoration:none !important;
            font-weight:600; font-size:14px; transition:background .3s;
        }
        .logout-btn:hover { background:#c62828 !important; }

        /* ── HERO ─────────────────────────────────────── */
        .hero-section {
            position:relative;
            background-image:url('../fotopetani.jpg.jpeg');
            background-size:cover; background-position:center; background-repeat:no-repeat;
            min-height:100vh; display:flex; align-items:center; padding-top:80px;
        }
        .hero-section::before {
            content:""; position:absolute; inset:0;
            background:linear-gradient(to right,rgba(255,255,255,.95) 0%,rgba(255,255,255,.7) 45%,rgba(255,255,255,0) 100%);
            z-index:1;
        }
        .hero-container {
            position:relative; z-index:2;
            max-width:650px; margin-left:10%; padding:0 20px;
        }
        .hero-text h1 {
            font-size:36px; font-weight:800; color:var(--fresh-green);
            line-height:1.2; margin-bottom:15px;
        }
        .hero-text p { font-size:16px; color:var(--text-grey); margin-bottom:25px; }
        .green-btn {
            background:var(--fresh-green); color:#fff;
            padding:12px 24px; border:none; border-radius:6px;
            font-size:16px; cursor:pointer; transition:background .3s;
            text-decoration:none; display:inline-flex; align-items:center; gap:8px;
        }
        .green-btn img { width:24px; height:24px; object-fit:contain; }
        .green-btn:hover { background:var(--dark-green); }

        /* ── FEATURE CARDS ────────────────────────────── */
        .feature-cards {
            background:var(--light-green-bg);
            display:flex; justify-content:center; align-items:center;
        }
        .feature-container {
            display:grid; grid-template-columns:1fr 1fr;
            gap:30px; max-width:750px; width:100%; margin:0 auto;
        }
        .feature-card {
            background:#fff; padding:30px; border-radius:12px;
            box-shadow:0 4px 12px rgba(0,0,0,.06); text-align:center;
        }
        .icon-group {
            display:flex; justify-content:center; align-items:center;
            gap:10px; margin-bottom:20px;
        }
        .icon-group img { height:60px; width:60px; object-fit:contain; }
        .feature-card h3 { font-size:18px; font-weight:700; color:var(--text-dark); margin-bottom:10px; }
        .feature-card p  { font-size:14px; color:var(--text-grey); }

        /* ── FOOTER ───────────────────────────────────── */
        .main-footer {
            background:#fff; color:var(--fresh-green);
            border-top:1px solid var(--border-color);
        }

        /* ── BPS TABLE ────────────────────────────────── */
        .bps-section { background:#fff; padding:60px 0; border-top:1px solid #e0e0e0; }
        .bps-head {
            display:flex; justify-content:space-between; align-items:flex-end;
            flex-wrap:wrap; gap:14px; margin-bottom:20px;
        }
        .bps-eyebrow { font-size:.7rem; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#4caf50; margin:0 0 5px; }
        .bps-title   { font-size:1.4rem; font-weight:800; color:#2e7d32; margin:0 0 4px; }
        .bps-desc    { font-size:.8rem; color:#757575; margin:0; }
        .bps-count   { background:#e8f5e9; border:1px solid #c8e6c9; border-radius:10px; padding:10px 20px; text-align:center; min-width:80px; }
        .bps-count-num   { display:block; font-size:1.5rem; font-weight:800; color:#2e7d32; line-height:1; }
        .bps-count-label { font-size:.65rem; color:#757575; font-weight:600; text-transform:uppercase; letter-spacing:.5px; }
        .bps-toolbar { display:flex; gap:10px; margin-bottom:14px; flex-wrap:wrap; }
        .bps-sw   { position:relative; flex:1; min-width:180px; }
        .bps-si   { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:#9e9e9e; font-size:.85rem; pointer-events:none; }
        .bps-input, .bps-select {
            padding:10px 14px; border:1.5px solid #e0e0e0; border-radius:8px;
            font-size:.85rem; font-family:'Poppins',sans-serif; outline:none;
            background:#f8fdf8; color:#333; transition:border-color .2s;
        }
        .bps-input  { width:100%; padding-left:34px; box-sizing:border-box; }
        .bps-select { min-width:155px; cursor:pointer; }
        .bps-input:focus, .bps-select:focus { border-color:#4caf50; }
        .bps-state {
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            gap:14px; padding:60px 20px; border:1.5px solid #e0e0e0; border-radius:12px; text-align:center;
        }
        .bps-spinner {
            width:42px; height:42px; border:3px solid #e8f5e9;
            border-top-color:#4caf50; border-radius:50%;
            animation:bspin .75s linear infinite;
        }
        @keyframes bspin { to { transform:rotate(360deg); } }
        .bps-state p { font-size:.87rem; color:#757575; margin:0; }
        .bps-state.err { border-color:#ffcdd2; background:#fff8f8; }
        .bps-state.err p { color:#c62828; font-weight:600; }
        .bps-retry { padding:9px 20px; background:#e53935; color:#fff; border:none; border-radius:8px; font-size:.82rem; font-weight:700; cursor:pointer; font-family:'Poppins',sans-serif; }
        .bps-box { border:1px solid #e0e0e0; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.05); }
        .bps-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; }
        .bps-table { width:100%; border-collapse:collapse; min-width:680px; }
        .bps-table thead { background:linear-gradient(135deg,#1b5e20,#2e7d32); }
        .bps-table th {
            padding:13px 15px; text-align:left; font-size:.68rem; font-weight:700;
            letter-spacing:1px; text-transform:uppercase; color:rgba(255,255,255,.9);
            white-space:nowrap; border:none;
        }
        .bps-table th.r { text-align:right; }
        .bps-table tbody tr { border-bottom:1px solid #f1f8e9; transition:background .12s; }
        .bps-table tbody tr:last-child { border-bottom:none; }
        .bps-table tbody tr:hover td { background:#f1f8e9; }
        .bps-table tbody tr:nth-child(even) td { background:#fafff8; }
        .bps-table tbody tr:nth-child(even):hover td { background:#f1f8e9; }
        .bps-table td { padding:11px 15px; font-size:.84rem; color:#424242; vertical-align:middle; }
        .td-no   { text-align:center; color:#bdbdbd; font-size:.74rem; }
        .td-prov { font-weight:700; color:#2e7d32; white-space:nowrap; }
        .td-num  { text-align:right; white-space:nowrap; font-weight:600; color:#1a237e; }
        .td-nil  { text-align:right; white-space:nowrap; color:#bdbdbd; }
        .bps-table tr.tot td { background:#e8f5e9 !important; font-weight:800; color:#1b5e20; border-top:2px solid #c8e6c9; }
        .td-empty { text-align:center; padding:40px; color:#9e9e9e; font-size:.86rem; }
        .bps-foot {
            display:flex; justify-content:space-between; align-items:center;
            padding:11px 16px; background:#f8fdf8; border-top:1px solid #e0e0e0;
            font-size:.75rem; color:#9e9e9e; flex-wrap:wrap; gap:6px;
        }
        .bps-foot b { color:#4caf50; }

        /* ── HAMBURGER ────────────────────────────────── */
        .hamburger {
            display:none; flex-direction:column; gap:5px;
            cursor:pointer; background:none; border:none; padding:4px;
        }
        .hamburger span {
            display:block; width:24px; height:2px;
            background:var(--text-dark); border-radius:2px; transition:all .3s;
        }
        .hamburger.open span:nth-child(1) { transform:translateY(7px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity:0; }
        .hamburger.open span:nth-child(3) { transform:translateY(-7px) rotate(-45deg); }

        /* ── MOBILE DRAWER ────────────────────────────── */
        .mobile-menu {
            display:none; flex-direction:column;
            background:rgba(255,255,255,0.98);
            border-top:1px solid var(--border-color);
            padding:16px 20px 20px;
        }
        .mobile-menu.open { display:flex; }
        .mobile-menu a {
            text-decoration:none; color:var(--text-grey);
            font-weight:500; font-size:15px;
            padding:10px 0; border-bottom:1px solid #f0f0f0;
            transition:color .2s;
        }
        .mobile-menu a:hover, .mobile-menu a.active { color:var(--fresh-green); }
        .mobile-menu .mobile-auth {
            margin-top:14px; display:flex; flex-direction:column; gap:10px;
        }
        .mobile-menu .mobile-auth span { font-weight:600; color:#555; font-size:14px; }
        .mobile-menu .mobile-auth .logout-btn { text-align:center; width:100%; }

        /* ── RESPONSIVE ───────────────────────────────── */
        @media (max-width:768px) {
            .nav-links    { display:none; }
            .auth-buttons { display:none; }
            .hamburger    { display:flex; }
            .hero-section::before { background:rgba(255,255,255,.85); }
            .hero-container { margin-left:0; text-align:center; display:flex; flex-direction:column; align-items:center; }
            .hero-text h1 { font-size:28px; }
            .feature-container { grid-template-columns:1fr; gap:20px; max-width:100%; }
            .section-padding { padding:40px 0; }
            .bps-head    { flex-direction:column; align-items:flex-start; }
            .bps-toolbar { flex-direction:column; }
            .bps-select  { width:100%; }
        }
    </style>
</head>
<body>

<!-- NAVIGASI -->
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
            <li><a href="hasil_diagnosa.php">HASIL DIAGNOSA</a></li>
        </ul>
        <div class="auth-buttons">
            <span style="font-weight:600;color:#555;align-self:center;">
                Halo, <?= htmlspecialchars($_SESSION["username"]) ?>!
            </span>
            <a href="proseslogout.php" class="logout-btn">LOGOUT</a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu" onclick="toggleMenu()">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- Mobile drawer -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#" class="active">BERANDA</a>
        <a href="Analisispage.php">IDENTIFIKASI PENYAKIT</a>
        <a href="infopenyakit.php">INFO PENYAKIT</a>
        <a href="hasil_diagnosa.php">HASIL DIAGNOSA</a>
        <div class="mobile-auth">
            <span>Halo, <?= htmlspecialchars($_SESSION["username"]) ?>!</span>
            <a href="proseslogout.php" class="logout-btn">LOGOUT</a>
        </div>
    </div>
</header>

<!-- HERO -->
<section class="hero-section">
    <div class="container hero-container">
        <div class="hero-text">
            <h1>DETEKSI DINI &<br>KENDALIKAN PENYAKIT<br>TANAMAN ANDA</h1>
            <p>Platform Digital Pintar untuk Diagnosis Akurat, Penanganan Efektif, dan Peningkatan Hasil Panen Petani Indonesia.</p>
            <a href="Analisispage.php" class="green-btn">
                <img src="../logokamera.png" alt="kamera">
                MULAI IDENTIFIKASI SEKARANG
            </a>
        </div>
    </div>
</section>

<!-- FEATURED CARD -->
<section class="feature-cards section-padding">
    <div class="container feature-container">
        <div class="feature-card">
            <div class="icon-group"><img src="../daun.jpg" alt="Ikon Daun"></div>
            <h3>IDENTIFIKASI PENYAKIT</h3>
            <p>Unggah foto tanaman sakit untuk diagnosis cepat.</p>
        </div>
        <div class="feature-card">
            <div class="icon-group"><img src="../buku.png" alt="Ikon Buku"></div>
            <h3>INFO PENYAKIT</h3>
            <p>Cari informasi lengkap tentang ribuan jenis penyakit, gejala, dan cara mengatasi.</p>
        </div>
    </div>
</section>

<!-- SECTION DATA BPS -->
<section class="bps-section">
    <div class="container">
        <div class="bps-head">
            <div>
                <p class="bps-eyebrow">📊 Data Resmi BPS</p>
                <h2 class="bps-title">Produksi Padi Menurut Provinsi (Ton)</h2>
                <p class="bps-desc">Sumber: Badan Pusat Statistik Indonesia · Ditampilkan otomatis dari API BPS</p>
            </div>
            <div class="bps-count">
                <span class="bps-count-num" id="bps-jml">–</span>
                <span class="bps-count-label">Provinsi</span>
            </div>
        </div>

        <div class="bps-toolbar">
            <div class="bps-sw">
                <span class="bps-si">🔍</span>
                <input type="text" id="bps-cari" class="bps-input"
                       placeholder="Cari provinsi..." oninput="bpsFilter()">
            </div>
            <select id="bps-bulan" class="bps-select" onchange="bpsFilter()">
                <option value="all">Semua Bulan</option>
            </select>
            <select id="bps-sort" class="bps-select" onchange="bpsFilter()">
                <option value="">Urutan Default</option>
                <option value="desc">Total: Terbesar</option>
                <option value="asc">Total: Terkecil</option>
                <option value="az">Nama A-Z</option>
            </select>
        </div>

        <div id="bps-loading" class="bps-state">
            <div class="bps-spinner"></div>
            <p>Mengambil data dari BPS…</p>
        </div>

        <div id="bps-error" class="bps-state err" style="display:none">
            <span style="font-size:2rem">⚠️</span>
            <p id="bps-err-msg">Gagal memuat data.</p>
            <button class="bps-retry" onclick="bpsMuat()">↻ Coba Lagi</button>
        </div>

        <div id="bps-box" class="bps-box" style="display:none">
            <div class="bps-scroll">
                <table class="bps-table">
                    <thead id="bps-thead"></thead>
                    <tbody id="bps-tbody"></tbody>
                </table>
            </div>
            <div class="bps-foot">
                <span id="bps-info">-</span>
                <b>Sumber: webapi.bps.go.id · var/2506</b>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="main-footer section-padding text-center">
    <div class="container">
        <p>© 2026 Website Cek Penyakit Tanaman (CePaT). Semua Hak Dilindungi.</p>
    </div>
</footer>

<script>
/* ── BPS MODULE ─────────────────────────────────────────────
 * Fetch LANGSUNG ke API BPS dari browser (tanpa api.php)
 * Key: {prov 4digit}{var 4digit}{tahun 4digit}{bulan val}
 * Contoh: 1100 + 2506 + 0126 + 1 = "1100250601261" → ACEH Jan 2026
 * ──────────────────────────────────────────────────────── */

var _prov=[], _bln=[], _filt=[];
var BPS_URL = "https://webapi.bps.go.id/v1/api/list/model/data/lang/ind/domain/0000/var/2506/th/126/key/d034dfbaf31081d490ec63859f2daa49";

function pad(n,len){ return String(n).padStart(len,'0'); }

async function bpsMuat(){
    var L=document.getElementById('bps-loading');
    var E=document.getElementById('bps-error');
    var B=document.getElementById('bps-box');
    L.style.display='flex'; E.style.display='none'; B.style.display='none';

    try {
        // Fetch langsung dari JS ke API BPS (CORS sudah diizinkan BPS)
        var res = await fetch(BPS_URL);
        if (!res.ok) throw new Error('HTTP ' + res.status);

        var j = await res.json();

        if (j.error)  throw new Error(j.error);
        if (j.status && j.status !== 'OK')
            throw new Error(j['data-availability'] || 'Data tidak tersedia.');

        // Struktur JSON BPS yang BENAR:
        //   j.vervar    = [{val:1100, label:"ACEH"}, ...]   → provinsi
        //   j.turtahun  = [{val:1, label:"Januari"}, ...]   → bulan
        //   j.tahun     = [{val:126, label:"2026"}]         → tahun
        //   j.var       = [{val:2506, ...}]                 → variabel
        //   j.datacontent = {"1100250601261": 55380.19, ...}

        var vervar      = j.vervar;
        var turtahun    = j.turtahun;
        var tahun       = j.tahun;
        var varData     = j.var;
        var datacontent = j.datacontent;

        if (!vervar || !turtahun || !tahun || !datacontent)
            throw new Error('Struktur JSON BPS tidak dikenali.');

        var kodeVar   = pad(varData[0].val, 4);   // "2506"
        var kodeTahun = pad(tahun[0].val,   4);   // "0126"

        // Bulan: exclude Tahunan (val=13)
        var bulanList = turtahun.filter(function(b){ return b.val !== 13; });
        // Provinsi: exclude INDONESIA (val=9999)
        var provList  = vervar.filter(function(p){ return p.val !== 9999; });

        _bln  = bulanList.map(function(b){ return b.label; });
        _prov = provList.map(function(p){
            var kodeProv = pad(p.val, 4);
            var vals = bulanList.map(function(b){
                var key = kodeProv + kodeVar + kodeTahun + String(b.val);
                var v   = datacontent[key];
                return (v !== undefined && v !== null) ? parseFloat(v) : null;
            });
            return { label: p.label, vals: vals };
        });

        // Isi dropdown bulan
        var sel = document.getElementById('bps-bulan');
        sel.innerHTML = '<option value="all">Semua Bulan</option>';
        _bln.forEach(function(b,i){
            var o = document.createElement('option');
            o.value = i; o.textContent = b; sel.appendChild(o);
        });

        document.getElementById('bps-jml').textContent = _prov.length;
        _filt = _prov.slice();
        bpsRender('all');

        L.style.display='none'; B.style.display='block';

    } catch(e) {
        L.style.display='none'; E.style.display='flex';
        document.getElementById('bps-err-msg').textContent = e.message || 'Terjadi kesalahan.';
        console.error('[BPS]', e);
    }
}

function bpsFilter(){
    var q  = document.getElementById('bps-cari').value.toLowerCase();
    var bl = document.getElementById('bps-bulan').value;
    var sr = document.getElementById('bps-sort').value;
    _filt  = _prov.filter(function(p){ return p.label.toLowerCase().indexOf(q)!==-1; });
    if (sr==='asc'||sr==='desc'){
        _filt.sort(function(a,b){
            var ta=bpsTot(a.vals,bl), tb=bpsTot(b.vals,bl);
            return sr==='asc' ? ta-tb : tb-ta;
        });
    } else if (sr==='az'){
        _filt.sort(function(a,b){ return a.label.localeCompare(b.label); });
    }
    bpsRender(bl);
}

function bpsTot(vals,bl){
    if (bl==='all') return vals.reduce(function(s,v){return s+(v||0);},0);
    return vals[parseInt(bl)] || 0;
}

function bpsRender(bl){
    var thead = document.getElementById('bps-thead');
    var tbody = document.getElementById('bps-tbody');
    var info  = document.getElementById('bps-info');
    var idx   = bl==='all' ? _bln.map(function(_,i){return i;}) : [parseInt(bl)];
    var showTot = bl==='all' && _bln.length>1;
    var cols  = idx.length + 2 + (showTot?1:0);

    // Thead
    var th = '<tr><th style="width:42px;text-align:center;padding:13px 10px;font-size:.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.9);border:none;">#</th>'
           + '<th style="min-width:175px;padding:13px 15px;font-size:.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.9);border:none;">Provinsi</th>';
    idx.forEach(function(i){
        th += '<th class="r" style="text-align:right;padding:13px 15px;font-size:.68rem;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.9);border:none;">'+esc(_bln[i]||'')+'</th>';
    });
    if (showTot) th += '<th class="r" style="text-align:right;padding:13px 15px;font-size:.68rem;font-weight:700;color:rgba(255,255,255,.9);border:none;">Total</th>';
    th += '</tr>';
    thead.innerHTML = th;

    if (_filt.length===0){
        tbody.innerHTML='<tr><td colspan="'+cols+'" class="td-empty">Tidak ada data yang cocok.</td></tr>';
        info.textContent='Menampilkan 0 data'; return;
    }

    var html='', gt=new Array(idx.length).fill(0);
    _filt.forEach(function(p,i){
        html += '<tr><td class="td-no">'+(i+1)+'</td><td class="td-prov">'+esc(p.label)+'</td>';
        var rt=0;
        idx.forEach(function(bi,j){
            var v=p.vals[bi];
            if (v!==null&&v!==undefined){ gt[j]+=v; rt+=v; html+='<td class="td-num">'+fmt(v)+'</td>'; }
            else html+='<td class="td-nil">–</td>';
        });
        if (showTot) html+='<td class="td-num">'+(rt>0?fmt(rt):'–')+'</td>';
        html+='</tr>';
    });

    if (_filt.length>1){
        html+='<tr class="tot"><td></td><td class="td-prov">TOTAL</td>';
        var gs=0;
        gt.forEach(function(v){ gs+=v; html+='<td class="td-num">'+(v>0?fmt(v):'–')+'</td>'; });
        if (showTot) html+='<td class="td-num">'+(gs>0?fmt(gs):'–')+'</td>';
        html+='</tr>';
    }

    tbody.innerHTML = html;
    info.textContent = 'Menampilkan '+_filt.length+' dari '+_prov.length+' provinsi';
}

function fmt(n){ return Number(n).toLocaleString('id-ID',{maximumFractionDigits:2}); }
function esc(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

document.addEventListener('DOMContentLoaded', bpsMuat);

/* ── Hamburger toggle ── */
function toggleMenu() {
    var btn  = document.getElementById('hamburger');
    var menu = document.getElementById('mobileMenu');
    btn.classList.toggle('open');
    menu.classList.toggle('open');
}
</script>
</body>
</html>