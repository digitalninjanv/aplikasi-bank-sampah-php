<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Bank Sampah Digital | Sistem Manajemen Bank Sampah Terintegrasi</title>
    <meta name="description" content="Aplikasi Bank Sampah digital untuk pengelolaan tabungan sampah, setoran, penarikan, dan laporan keuangan. Solusi tepat untuk bank sampah desa, kelurahan, atau kota Anda.">
    <meta name="keywords" content="bank sampah, aplikasi bank sampah, sistem bank sampah, tabungan sampah, manajemen bank sampah, digitalisasi bank sampah, pengelolaan sampah, go green, daur ulang">
    <meta name="robots" content="index, follow">
    <meta name="author" content="Digital Ninja">
    <link rel="canonical" href="https://banksampah.digitalninja.net/">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="Aplikasi Bank Sampah Digital — Kelola Bank Sampah Lebih Mudah">
    <meta property="og:description" content="Sistem manajemen bank sampah lengkap dengan fitur setoran, penarikan, tabungan nasabah, laporan keuangan, dan ekspor data.">
    <meta property="og:url" content="https://banksampah.digitalninja.net/">
    <meta property="og:site_name" content="Aplikasi Bank Sampah Digital">
    <meta property="og:locale" content="id_ID">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Aplikasi Bank Sampah Digital">
    <meta name="twitter:description" content="Sistem manajemen bank sampah lengkap — setoran, penarikan, tabungan, laporan, dan ekspor data.">

    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Aplikasi Bank Sampah Digital",
        "description": "Sistem manajemen bank sampah terintegrasi untuk pengelolaan tabungan sampah, setoran, penarikan, dan laporan keuangan.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web-based",
        "browserRequirements": "Modern browser (Chrome, Firefox, Safari, Edge)",
        "author": {
            "@type": "Organization",
            "name": "Digital Ninja",
            "email": "digitalninja.net@gmail.com"
        },
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "IDR",
            "availability": "https://schema.org/InStock"
        }
    }
    </script>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Poppins:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --accent: #059669;
            --accent-light: #d1fae5;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0f172a;
            --amber-50: #fffbeb;
            --amber-400: #f59e0b;
            --amber-500: #d97706;
            --red-50: #fef2f2;
            --red-500: #ef4444;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--slate-700);
            background: #fff;
            line-height: 1.6;
        }
        h1, h2, h3, h4 { font-family: 'Poppins', sans-serif; color: var(--slate-900); line-height: 1.3; }

        .container { max-width: 1200px; margin: 0 auto; padding: 0 24px; }

        /* Nav */
        nav {
            position: fixed; top: 0; left: 0; right: 0; z-index: 100;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--slate-200);
        }
        nav .container { display: flex; align-items: center; justify-content: space-between; height: 70px; }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; font-size: 1.25rem; color: var(--slate-900); text-decoration: none; }
        .logo i { color: var(--primary); font-size: 1.5rem; }
        nav .nav-links { display: flex; align-items: center; gap: 32px; }
        nav .nav-links a { color: var(--slate-600); text-decoration: none; font-weight: 500; font-size: 0.95rem; transition: color 0.2s; }
        nav .nav-links a:hover { color: var(--primary); }
        .nav-cta {
            padding: 10px 24px; background: var(--primary); color: #fff !important;
            border-radius: 10px; font-weight: 600 !important; transition: background 0.2s;
        }
        .nav-cta:hover { background: var(--primary-dark) !important; }
        .menu-toggle { display: none; background: none; border: none; font-size: 1.5rem; color: var(--slate-700); cursor: pointer; }

        /* Hero */
        .hero {
            padding: 140px 0 80px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #e0f2fe 100%);
            position: relative; overflow: hidden;
        }
        .hero::after {
            content: ''; position: absolute; bottom: -2px; left: 0; right: 0;
            height: 80px; background: #fff; clip-path: ellipse(70% 100% at 50% 100%);
        }
        .hero .container { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .hero h1 { font-size: 3rem; line-height: 1.2; margin-bottom: 20px; }
        .hero h1 span { color: var(--primary); }
        .hero p { font-size: 1.125rem; color: var(--slate-500); margin-bottom: 32px; max-width: 540px; }
        .hero-badges { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 36px; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; background: rgba(255,255,255,0.7); border-radius: 100px;
            font-size: 0.875rem; font-weight: 500; color: var(--slate-700);
        }
        .hero-badge i { color: var(--primary); }
        .hero-buttons { display: flex; gap: 16px; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; border-radius: 12px; font-weight: 600;
            font-size: 1rem; text-decoration: none; transition: all 0.2s; cursor: pointer; border: none;
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 4px 14px rgba(37,99,235,0.3); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 6px 20px rgba(37,99,235,0.4); }
        .btn-outline { background: transparent; color: var(--slate-700); border: 2px solid var(--slate-300); }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }
        .btn-accent { background: var(--accent); color: #fff; box-shadow: 0 4px 14px rgba(5,150,105,0.3); }
        .btn-accent:hover { background: #047857; transform: translateY(-2px); }
        .hero-image {
            display: flex; justify-content: center; align-items: center;
            background: rgba(255,255,255,0.5); border-radius: 24px;
            padding: 40px; box-shadow: 0 20px 60px rgba(37,99,235,0.1);
            aspect-ratio: 4/3; position: relative;
        }
        .hero-image .mockup {
            width: 100%; height: 100%;
            background: linear-gradient(135deg, var(--primary-light), #e0e7ff);
            border-radius: 16px; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 12px;
            color: var(--primary); font-size: 2rem; font-weight: 700;
        }
        .hero-image .mockup i { font-size: 4rem; }
        .hero-image .mockup small { font-size: 1rem; font-weight: 400; color: var(--slate-500); }

        /* Section common */
        section { padding: 80px 0; }
        .section-label {
            display: inline-block; padding: 6px 16px; border-radius: 100px;
            font-size: 0.875rem; font-weight: 600; margin-bottom: 12px;
        }
        .section-label.blue { background: var(--primary-light); color: var(--primary); }
        .section-label.green { background: var(--accent-light); color: var(--accent); }
        .section-label.amber { background: var(--amber-50); color: var(--amber-500); }
        .section-title { font-size: 2.25rem; text-align: center; margin-bottom: 16px; }
        .section-subtitle { text-align: center; color: var(--slate-500); max-width: 600px; margin: 0 auto 48px; font-size: 1.125rem; }

        /* Features */
        #features { background: var(--slate-50); }
        .features-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .feature-card {
            background: #fff; border-radius: 16px; padding: 32px 28px;
            border: 1px solid var(--slate-200); transition: all 0.3s;
        }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.06); border-color: var(--primary-light); }
        .feature-icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; margin-bottom: 18px;
        }
        .feature-icon.blue { background: var(--primary-light); color: var(--primary); }
        .feature-icon.green { background: var(--accent-light); color: var(--accent); }
        .feature-icon.amber { background: var(--amber-50); color: var(--amber-500); }
        .feature-icon.red { background: var(--red-50); color: var(--red-500); }
        .feature-icon.slate { background: var(--slate-100); color: var(--slate-600); }
        .feature-card h3 { font-size: 1.25rem; margin-bottom: 10px; }
        .feature-card p { color: var(--slate-500); font-size: 0.95rem; }

        /* How It Works */
        .steps-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; position: relative; }
        .steps-grid::before {
            content: ''; position: absolute; top: 40px; left: 10%; right: 10%;
            height: 2px; background: linear-gradient(to right, var(--primary-light), var(--slate-200), var(--slate-200));
        }
        .step { text-align: center; position: relative; }
        .step-number {
            width: 80px; height: 80px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 20px; font-size: 1.75rem; font-weight: 800; font-family: 'Poppins', sans-serif;
            background: #fff; border: 2px solid var(--slate-200); color: var(--slate-400);
            position: relative; z-index: 2; transition: all 0.3s;
        }
        .step:hover .step-number { border-color: var(--primary); color: var(--primary); background: var(--primary-light); }
        .step h3 { font-size: 1.125rem; margin-bottom: 8px; }
        .step p { color: var(--slate-500); font-size: 0.9rem; }

        /* Benefits */
        #benefits { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; }
        #benefits .section-title { color: #fff; }
        #benefits .section-subtitle { color: #94a3b8; }
        .benefits-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; }
        .benefit-card {
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 32px 28px; backdrop-filter: blur(4px);
        }
        .benefit-card i { font-size: 2rem; color: var(--primary); margin-bottom: 16px; display: block; }
        .benefit-card h3 { color: #fff; font-size: 1.2rem; margin-bottom: 10px; }
        .benefit-card p { color: #94a3b8; font-size: 0.95rem; }

        /* Screenshots / Preview */
        #preview { background: var(--slate-50); }
        .preview-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
        .preview-item {
            background: #fff; border-radius: 14px; padding: 28px 20px; text-align: center;
            border: 1px solid var(--slate-200);
        }
        .preview-item i { font-size: 2.5rem; color: var(--primary); margin-bottom: 12px; }
        .preview-item h4 { font-size: 1rem; margin-bottom: 4px; }
        .preview-item p { font-size: 0.85rem; color: var(--slate-400); }

        /* Pricing / CTA */
        #contact { text-align: center; background: #fff; }
        .cta-box {
            max-width: 700px; margin: 0 auto;
            background: linear-gradient(135deg, var(--primary) 0%, #1d4ed8 100%);
            border-radius: 24px; padding: 56px 40px; color: #fff;
            box-shadow: 0 20px 60px rgba(37,99,235,0.25);
        }
        .cta-box h2 { color: #fff; font-size: 2rem; margin-bottom: 16px; }
        .cta-box p { opacity: 0.9; margin-bottom: 32px; font-size: 1.05rem; }
        .cta-box .btn { background: #fff; color: var(--primary); }
        .cta-box .btn:hover { background: var(--slate-100); transform: translateY(-2px); }
        .cta-contact {
            display: flex; justify-content: center; gap: 32px; flex-wrap: wrap;
            margin-top: 32px; font-size: 1rem;
        }
        .cta-contact a { color: rgba(255,255,255,0.85); text-decoration: none; display: flex; align-items: center; gap: 8px; transition: color 0.2s; }
        .cta-contact a:hover { color: #fff; }

        /* Testimonials */
        #testimonials { background: var(--slate-50); }
        .testimonial-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
        .testimonial-card {
            background: #fff; border-radius: 16px; padding: 28px;
            border: 1px solid var(--slate-200);
        }
        .testimonial-card .stars { color: var(--amber-400); margin-bottom: 12px; }
        .testimonial-card blockquote { font-style: italic; color: var(--slate-600); margin-bottom: 16px; }
        .testimonial-card .author { display: flex; align-items: center; gap: 12px; }
        .testimonial-card .author .avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--primary-light); color: var(--primary);
            display: flex; align-items: center; justify-content: center; font-weight: 700;
        }
        .testimonial-card .author .name { font-weight: 600; font-size: 0.95rem; color: var(--slate-800); }
        .testimonial-card .author .role { font-size: 0.85rem; color: var(--slate-400); }

        /* Footer */
        footer {
            background: var(--slate-900); color: #94a3b8; padding: 48px 0 24px;
        }
        footer .container { display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 40px; }
        footer h4 { color: #fff; font-size: 1rem; margin-bottom: 16px; }
        footer a { color: #94a3b8; text-decoration: none; display: block; margin-bottom: 8px; transition: color 0.2s; font-size: 0.9rem; }
        footer a:hover { color: #fff; }
        footer .brand i { color: var(--primary); }
        footer .brand p { margin-top: 12px; font-size: 0.9rem; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,0.08); margin-top: 32px; padding-top: 20px; text-align: center; font-size: 0.85rem; }

        /* Responsive */
        @media (max-width: 768px) {
            nav .nav-links { display: none; position: absolute; top: 70px; left: 0; right: 0; background: #fff; flex-direction: column; padding: 20px; border-bottom: 1px solid var(--slate-200); box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
            nav .nav-links.open { display: flex; }
            .menu-toggle { display: block; }
            .hero .container { grid-template-columns: 1fr; gap: 40px; }
            .hero h1 { font-size: 2rem; }
            .hero-badges { display: none; }
            .features-grid, .benefits-grid, .testimonial-grid { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr 1fr; }
            .steps-grid::before { display: none; }
            .preview-grid { grid-template-columns: 1fr 1fr; }
            footer .container { grid-template-columns: 1fr 1fr; }
            .cta-box { padding: 36px 24px; }
        }
        @media (max-width: 480px) {
            .steps-grid { grid-template-columns: 1fr; }
            .preview-grid { grid-template-columns: 1fr; }
            footer .container { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav>
    <div class="container">
        <a href="/" class="logo"><i class="fas fa-leaf"></i> Bank Sampah</a>
        <div class="nav-links" id="navLinks">
            <a href="#features">Fitur</a>
            <a href="#how-it-works">Cara Kerja</a>
            <a href="#benefits">Keunggulan</a>
            <a href="#testimonials">Testimoni</a>
            <a href="index.php?page=auth/login" class="nav-cta"><i class="fas fa-sign-in-alt"></i> Masuk</a>
        </div>
        <button class="menu-toggle" onclick="document.getElementById('navLinks').classList.toggle('open')">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<!-- Hero -->
<section class="hero" id="home">
    <div class="container">
        <div>
            <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
                <span class="hero-badge"><i class="fas fa-check-circle"></i> Siap Pakai</span>
                <span class="hero-badge"><i class="fas fa-mobile-alt"></i> Mobile Friendly</span>
                <span class="hero-badge"><i class="fas fa-shield-alt"></i> Aman &amp; Terpercaya</span>
            </div>
            <h1>Aplikasi <span>Bank Sampah</span> Digital untuk Pengelolaan Sampah Modern</h1>
            <p>Sistem manajemen bank sampah terintegrasi — kelola setoran, tabungan nasabah, penarikan saldo, dan laporan keuangan dalam satu platform yang mudah digunakan.</p>
            <div class="hero-buttons">
                <a href="index.php?page=auth/login" class="btn btn-primary"><i class="fas fa-sign-in-alt"></i> Masuk ke Aplikasi</a>
                <a href="#contact" class="btn btn-outline"><i class="fas fa-rocket"></i> Demo Gratis</a>
                <a href="#features" class="btn btn-outline"><i class="fas fa-arrow-down"></i> Lihat Fitur</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="mockup">
                <i class="fas fa-leaf"></i>
                Bank Sampah Digital
                <small>Dashboard • Setoran • Laporan</small>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<section id="features">
    <div class="container">
        <span class="section-label blue">Fitur Unggulan</span>
        <h2 class="section-title">Semua Kebutuhan Bank Sampah dalam Satu Aplikasi</h2>
        <p class="section-subtitle">Dari pendataan nasabah hingga ekspor laporan keuangan — semua bisa dilakukan dengan cepat dan akurat.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon blue"><i class="fas fa-users"></i></div>
                <h3>Manajemen Nasabah</h3>
                <p>Pendaftaran, edit data, dan penghapusan nasabah dengan status aktif/nonaktif. Cari nasabah dengan cepat.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon green"><i class="fas fa-recycle"></i></div>
                <h3>Setoran Sampah</h3>
                <p>Transaksi setoran dengan harga otomatis, perhitungan saldo real-time, dan cetak struk otomatis.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon amber"><i class="fas fa-money-bill-wave"></i></div>
                <h3>Penarikan Saldo</h3>
                <p>Nasabah dapat menarik saldo tabungan sampah kapan saja. Validasi saldo otomatis sebelum transaksi.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon red"><i class="fas fa-chart-bar"></i></div>
                <h3>Laporan &amp; Ekspor</h3>
                <p>Laporan harian, bulanan, rekap warga. Ekspor ke Excel (XLSX) dan PDF untuk kebutuhan pelaporan.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon slate"><i class="fas fa-user-shield"></i></div>
                <h3>Multi Level User</h3>
                <p>Tiga level akses: Admin, Petugas, dan Warga. Kontrol penuh terhadap hak akses setiap pengguna.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon blue"><i class="fas fa-history"></i></div>
                <h3>Riwayat &amp; Keamanan</h3>
                <p>Log aktivitas lengkap, riwayat perubahan harga sampah, soft delete, dan perlindungan CSRF.</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works -->
<section id="how-it-works">
    <div class="container">
        <span class="section-label green">Cara Kerja</span>
        <h2 class="section-title">Bagaimana Aplikasi Ini Bekerja?</h2>
        <p class="section-subtitle">Empat langkah sederhana untuk memulai digitalisasi bank sampah Anda.</p>
        <div class="steps-grid">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Registrasi &amp; Setup</h3>
                <p>Instalasi cepat di server Anda. Atur jenis sampah dan harga jual.</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Daftarkan Nasabah</h3>
                <p>Input data warga yang ingin menabung sampah. Setiap nasabah mendapat ID unik.</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Transaksi Setoran</h3>
                <p>Petugas menimbang dan mencatat setoran. Saldo nasabah terisi otomatis.</p>
            </div>
            <div class="step">
                <div class="step-number">4</div>
                <h3>Pantau &amp; Laporkan</h3>
                <p>Lihat dashboard, cetak laporan keuangan, dan ekspor data kapan saja.</p>
            </div>
        </div>
    </div>
</section>

<!-- Benefits -->
<section id="benefits">
    <div class="container">
        <span class="section-label amber" style="background:rgba(245,158,11,0.15);color:var(--amber-400);">Keunggulan</span>
        <h2 class="section-title">Mengapa Memilih Aplikasi Ini?</h2>
        <p class="section-subtitle">Dirancang khusus untuk kebutuhan bank sampah di Indonesia dengan fitur keamanan dan kemudahan penggunaan terbaik.</p>
        <div class="benefits-grid">
            <div class="benefit-card">
                <i class="fas fa-bolt"></i>
                <h3>Cepat &amp; Responsif</h3>
                <p>Dibangun dengan teknologi modern untuk performa optimal di berbagai perangkat.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-lock"></i>
                <h3>Keamanan Berlapis</h3>
                <p>CSRF protection, session regeneration, prepared statements, dan perlindungan soft delete.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-file-export"></i>
                <h3>Ekspor Fleksibel</h3>
                <p>Ekspor laporan ke Excel (XLSX) dan PDF. Siap untuk kebutuhan audit dan pelaporan.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-mobile-screen"></i>
                <h3>Fully Responsive</h3>
                <p>Tampilan optimal di desktop, tablet, dan smartphone. Nasabah bisa cek saldo dari HP.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-database"></i>
                <h3>Backup &amp; Migrasi</h3>
                <p>Fitur backup database terintegrasi. Mudah dipindahkan ke server baru.</p>
            </div>
            <div class="benefit-card">
                <i class="fas fa-headset"></i>
                <h3>Dukungan Penuh</h3>
                <p>Konsultasi instalasi, pelatihan penggunaan, dan bantuan teknis dari tim Digital Ninja.</p>
            </div>
        </div>
    </div>
</section>

<!-- Preview -->
<section id="preview">
    <div class="container">
        <span class="section-label blue">Fitur Lengkap</span>
        <h2 class="section-title">Akses ke Seluruh Fitur Aplikasi</h2>
        <p class="section-subtitle">Dashboard, transaksi, laporan, manajemen pengguna, dan banyak lagi — semuanya dalam satu platform.</p>
        <div class="preview-grid">
            <div class="preview-item"><i class="fas fa-chart-pie"></i><h4>Dashboard</h4><p>Grafik setoran &amp; penarikan</p></div>
            <div class="preview-item"><i class="fas fa-users"></i><h4>Data Warga</h4><p>Manajemen nasabah</p></div>
            <div class="preview-item"><i class="fas fa-recycle"></i><h4>Jenis Sampah</h4><p>Daftar &amp; harga sampah</p></div>
            <div class="preview-item"><i class="fas fa-hand-holding-usd"></i><h4>Setor Sampah</h4><p>Input transaksi setoran</p></div>
            <div class="preview-item"><i class="fas fa-money-check"></i><h4>Tarik Saldo</h4><p>Penarikan tabungan</p></div>
            <div class="preview-item"><i class="fas fa-file-alt"></i><h4>Laporan</h4><p>Harian, bulanan, rekap</p></div>
            <div class="preview-item"><i class="fas fa-file-excel"></i><h4>Ekspor Excel</h4><p>XLSX &amp; PDF</p></div>
            <div class="preview-item"><i class="fas fa-user-cog"></i><h4>Kelola Petugas</h4><p>Manajemen akun</p></div>
        </div>
    </div>
</section>

<!-- Testimonials -->
<section id="testimonials">
    <div class="container">
        <span class="section-label green">Testimoni</span>
        <h2 class="section-title">Apa Kata Pengguna?</h2>
        <p class="section-subtitle">Bank sampah di berbagai daerah telah menggunakan aplikasi ini untuk memudahkan operasional mereka.</p>
        <div class="testimonial-grid">
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <blockquote>"Aplikasi ini sangat membantu kami dalam mengelola tabungan sampah warga. Laporan otomatis dan fitur ekspor Excel-nya luar biasa!"</blockquote>
                <div class="author">
                    <div class="avatar">SN</div>
                    <div><div class="name">Siti Nurhaliza</div><div class="role">Ketua Bank Sampah, Desa Makmur</div></div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <blockquote>"Dulu kami catat manual, sekarang semua digital. Warga juga bisa cek saldo sendiri lewat HP. Sangat direkomendasikan!"</blockquote>
                <div class="author">
                    <div class="avatar">AR</div>
                    <div><div class="name">Ahmad Rizki</div><div class="role">Pengelola Bank Sampah Berseri</div></div>
                </div>
            </div>
            <div class="testimonial-card">
                <div class="stars"><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i></div>
                <blockquote>"Fitur keamanannya bagus, ada log aktivitas dan backup database. Cocok untuk bank sampah skala desa maupun kecamatan."</blockquote>
                <div class="author">
                    <div class="avatar">DW</div>
                    <div><div class="name">Dwi Wahyuni</div><div class="role">Admin Bank Sampah Hijau Lestari</div></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact / CTA -->
<section id="contact">
    <div class="container">
        <span class="section-label blue">Tertarik?</span>
        <div class="cta-box">
            <h2>Mulai Digitalisasi Bank Sampah Anda Sekarang</h2>
            <p>Dapatkan demo gratis dan konsultasi kebutuhan bank sampah Anda. Tim Digital Ninja siap membantu instalasi dan pelatihan.</p>
            <a href="mailto:digitalninja.net@gmail.com" class="btn"><i class="fas fa-envelope"></i> Hubungi Kami</a>
            <div class="cta-contact">
                <a href="mailto:digitalninja.net@gmail.com"><i class="fas fa-envelope"></i> digitalninja.net@gmail.com</a>
            </div>
        </div>
    </div>
</section>

<!-- Footer -->
<footer>
    <div class="container">
        <div class="brand">
            <h4><i class="fas fa-leaf"></i> Bank Sampah Digital</h4>
            <p>Aplikasi manajemen bank sampah terintegrasi untuk pengelolaan sampah yang modern, efisien, dan transparan.</p>
        </div>
        <div>
            <h4>Fitur</h4>
            <a href="#features">Manajemen Nasabah</a>
            <a href="#features">Setoran Sampah</a>
            <a href="#features">Laporan Keuangan</a>
            <a href="#features">Ekspor Data</a>
        </div>
        <div>
            <h4>Perusahaan</h4>
            <a href="mailto:digitalninja.net@gmail.com">Kontak</a>
            <a href="#home">Tentang</a>
        </div>
        <div>
            <h4>Kontak</h4>
            <a href="mailto:digitalninja.net@gmail.com"><i class="fas fa-envelope"></i> digitalninja.net@gmail.com</a>
        </div>
    </div>
    <div class="container">
        <div class="footer-bottom">
            &copy; <?php echo date('Y'); ?> Digital Ninja. All rights reserved. Dibuat dengan <i class="fas fa-heart" style="color:var(--red-500);"></i> untuk lingkungan.
        </div>
    </div>
</footer>

</body>
</html>