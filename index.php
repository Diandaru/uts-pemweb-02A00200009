<?php
session_start();
require_once 'data.php';
require_once 'functions.php';

// Inisialisasi session data sepatu jika belum ada
if (!isset($_SESSION['sepatu_list'])) {
    $_SESSION['sepatu_list'] = $sepatu_list;
}

$sepatu_list = getAllSepatu();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda & Profil - Fision Ara Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Fision Ara Store</h1>
    <nav>
        <a href="index.php" class="active">Beranda & Profil</a>
        <a href="katalog.php">Pesan & Checkout</a>
    </nav>
</header>

<div class="container">
    <!-- Banner Latar Belakang Toko dengan Logo Perisai Huruf F -->
    <div class="storefront-banner">
        <div class="store-logo-shield">F</div>
        <div class="store-title-group">
            <h2>Fision Ara Store</h2>
            <p>Pusat Sepatu Original & Berkualitas Tinggi</p>
        </div>
    </div>

    <!-- Profil UMKM Singkat -->
    <section class="hero" style="margin-top: -1rem;">
        <h2>Tentang UMKM Kami</h2>
        <p style="max-width: 800px; margin: 0.5rem auto 1.5rem auto; color: #475569;">
            <strong>Fision Ara Store</strong> adalah Usaha Mikro, Kecil, dan Menengah (UMKM) lokal terpercaya yang berdedikasi menyediakan koleksi sepatu original dan berkualitas tinggi. Kami menghadirkan berbagai pilihan alas kaki mulai dari gaya <em>casual</em>, <em>streetwear</em>, hingga sepatu lari (<em>running</em>) untuk menunjang kenyamanan aktivitas dan langkah harian Anda dengan harga terbaik.
        </p>
        <div>
            <a href="katalog.php" class="btn" style="text-decoration: none; display: inline-block;">Lihat Katalog & Pesan Sekarang</a>
        </div>
    </section>

    <h2 style="margin-bottom: 1rem;">Koleksi Sepatu Unggulan</h2>
    <div class="grid">
        <?php foreach ($sepatu_list as $sepatu): ?>
            <div class="card">
                <img src="<?= $sepatu['gambar']; ?>" alt="<?= $sepatu['nama']; ?>">
                <div class="card-body">
                    <div>
                        <small style="color: #64748b;"><?= $sepatu['kategori']; ?></small>
                        <h3><?= $sepatu['nama']; ?></h3>
                        <p style="font-size: 0.9rem; color: #475569; margin: 0.5rem 0;"><?= $sepatu['deskripsi']; ?></p>
                        <?php if ($sepatu['stok'] > 0): ?>
                            <span class="badge-stok">Stok: <?= $sepatu['stok']; ?> pasang</span>
                        <?php else: ?>
                            <span class="badge-stok badge-habis">Stok Habis</span>
                        <?php endif; ?>
                    </div>
                    <div style="margin-top: 1rem;">
                        <div class="price"><?= formatRupiah($sepatu['harga']); ?></div>
                        <?php if ($sepatu['stok'] > 0): ?>
                            <a href="katalog.php?select_id=<?= $sepatu['id']; ?>" class="btn" style="display:block; text-decoration:none; text-align:center; margin-top: 0.5rem;">Pilih & Pesan</a>
                        <?php else: ?>
                            <button class="btn" style="background: #94a3b8; cursor: not-allowed; width:100%; margin-top: 0.5rem;" disabled>Habis</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>