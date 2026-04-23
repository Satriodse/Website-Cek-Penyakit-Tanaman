<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analisis Penyakit Tanaman CePaT</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Background dan perataan */
        body {
            background-color: #f4f6f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem; /* Padding ekstra untuk form yang lebih panjang */
        }

        /* Kontainer form analisis (dibuat lebih lebar dari login/register) */
        .analysis-container {
            width: 100%;
            max-width: 600px; 
            padding: 2.5rem;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 8px 24px rgba(0, 0, 0, 0.08);
        }

        /* Warna kustom hijau CePaT */
        .btn-custom-green {
            background-color: #4CAF50;
            border-color: #4CAF50;
            color: #ffffff;
            font-weight: 500;
        }

        .btn-custom-green:hover {
            background-color: #43a047;
            border-color: #43a047;
            color: #ffffff;
        }

        .text-custom-green {
            color: #4CAF50;
        }

        /* Styling area logo */
        .logo-area {
            gap: 15px;
        }
        
        .logo-leaf-icon {
            height: 45px;
            width: auto;
        }
        
        .logo-subtext-item {
            display: block;
            line-height: 1.1;
        }
    </style>
</head>
<body>

    <div class="analysis-container">
        <div class="text-center mb-4">
            <div class="logo-area d-flex align-items-center justify-content-center mb-2">
                <div class="text-start">
                    <img src="../logocepat.png" alt="Logo CePaT" class="logo-leaf-icon img-fluid mb-1">
                </div>
                <h1 class="text-custom-green fw-bold mb-0" style="font-size: 2.8rem;">CePaT</h1>
            </div>
            <h4 class="mt-3 fw-bold text-dark">Form Analisis Penyakit</h4>
            <p class="text-muted">Lengkapi data di bawah ini agar sistem dapat mendiagnosis tanaman Anda dengan akurat.</p>
        </div>

        <form enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="namaInput" class="form-label fw-semibold">Nama Lengkap</label>
                <input type="text" class="form-control" id="namaInput" placeholder="Masukkan nama Anda" required>
            </div>

            <div class="mb-3">
                <label for="lokasiInput" class="form-label fw-semibold">Lokasi Lahan</label>
                <input type="text" class="form-control" id="lokasiInput" placeholder="Contoh: Desa Sukamaju, Kec. Bantul" required>
            </div>
            
            <div class="mb-3">
                <label for="jenisTanamanInput" class="form-label fw-semibold">Jenis Tanaman</label>
                <input type="text" class="form-control" id="jenisTanamanInput" placeholder="Contoh: Cabai Merah, Padi, Tomat" required>
            </div>
            
            <div class="mb-3">
                <label for="deskripsiInput" class="form-label fw-semibold">Deskripsi Singkat Gejala</label>
                <textarea class="form-control" id="deskripsiInput" rows="4" placeholder="Jelaskan gejala yang terlihat (contoh: daun menguning, ada bercak hitam, batang membusuk...)" required></textarea>
            </div>

            <div class="mb-4">
                <label for="fotoInput" class="form-label fw-semibold">Unggah Foto Tanaman</label>
                <input class="form-control" type="file" id="fotoInput" accept="image/*" required>
                <div class="form-text">Pastikan foto terlihat jelas, terang, dan fokus pada bagian yang terkena penyakit.</div>
            </div>
            
           <button type="submit" class="btn btn-custom-green w-100 py-2 mb-2 fw-semibold" style="font-size: 0.95rem;">KIRIM</button>
            
            <div class="text-center mt-3">
                <a href="tugasweb.php" class="text-decoration-none text-muted">KEMBALI KE BERANDA</a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>