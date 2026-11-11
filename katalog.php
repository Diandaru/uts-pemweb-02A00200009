<?php
session_start();
require_once 'data.php';
require_once 'functions.php';

// Inisialisasi session data sepatu jika belum ada
if (!isset($_SESSION['sepatu_list'])) {
    $_SESSION['sepatu_list'] = $sepatu_list;
}

// Inisialisasi session keranjang belanja jika belum ada
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$sepatu_list = getAllSepatu();
$selected_id = isset($_GET['select_id']) ? (int)$_GET['select_id'] : 0;
$error_message = '';
$success_message = '';
$checkout_receipt = null;

// Tangani aksi dari form (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = isset($_POST['aksi']) ? $_POST['aksi'] : '';

    // 1. Aksi Tambah ke Keranjang
    if ($aksi === 'tambah_keranjang') {
        $sepatu_id = isset($_POST['sepatu_id']) ? (int)$_POST['sepatu_id'] : 0;
        $jumlah = isset($_POST['jumlah']) ? (int)$_POST['jumlah'] : 0;

        $detail_sepatu = getSepatuById($sepatu_id);

        if ($detail_sepatu) {
            $stok_tersedia = $detail_sepatu['stok'];
            $jumlah_di_keranjang = isset($_SESSION['cart'][$sepatu_id]) ? $_SESSION['cart'][$sepatu_id] : 0;
            $total_permintaan = $jumlah_di_keranjang + $jumlah;

            if ($jumlah > 0 && $total_permintaan <= $stok_tersedia) {
                $_SESSION['cart'][$sepatu_id] = $total_permintaan;
                $success_message = "Produk berhasil ditambahkan ke keranjang belanja!";
            } else {
                $error_message = "Jumlah melebihi sisa stok yang tersedia (Stok: {$stok_tersedia})!";
            }
        } else {
            $error_message = "Produk tidak ditemukan.";
        }
    }

    // 2. Aksi Kurangi Jumlah Produk (-1) di Keranjang
    elseif ($aksi === 'kurangi_qty') {
        $sepatu_id = isset($_POST['sepatu_id']) ? (int)$_POST['sepatu_id'] : 0;
        if (isset($_SESSION['cart'][$sepatu_id])) {
            if ($_SESSION['cart'][$sepatu_id] > 1) {
                $_SESSION['cart'][$sepatu_id] -= 1;
                $success_message = "Jumlah produk berhasil dikurangi.";
            } else {
                // Jika jumlahnya sisa 1 dan dikurangi lagi, hapus item dari keranjang
                unset($_SESSION['cart'][$sepatu_id]);
                $success_message = "Produk dihapus dari keranjang karena jumlah mencapai 0.";
            }
        }
    }

    // 3. Aksi Tambah Jumlah Produk (+1) di Keranjang langsung dari tabel
    elseif ($aksi === 'tambah_qty') {
        $sepatu_id = isset($_POST['sepatu_id']) ? (int)$_POST['sepatu_id'] : 0;
        $detail_sepatu = getSepatuById($sepatu_id);
        if (isset($_SESSION['cart'][$sepatu_id]) && $detail_sepatu) {
            $qty_sekarang = $_SESSION['cart'][$sepatu_id];
            if ($qty_sekarang + 1 <= $detail_sepatu['stok']) {
                $_SESSION['cart'][$sepatu_id] += 1;
                $success_message = "Jumlah produk berhasil ditambah.";
            } else {
                $error_message = "Jumlah melebihi sisa stok yang tersedia ({$detail_sepatu['stok']}).";
            }
        }
    }

    // 4. Aksi Hapus Item dari Keranjang
    elseif ($aksi === 'hapus_item') {
        $sepatu_id = isset($_POST['sepatu_id']) ? (int)$_POST['sepatu_id'] : 0;
        if (isset($_SESSION['cart'][$sepatu_id])) {
            unset($_SESSION['cart'][$sepatu_id]);
            $success_message = "Produk berhasil dihapus dari keranjang.";
        }
    }

    // 5. Aksi Checkout Semua Keranjang
    elseif ($aksi === 'checkout_semua') {
        $nama_pemesan = htmlspecialchars(trim($_POST['nama_pemesan']));

        if (empty($nama_pemesan)) {
            $error_message = "Nama pemesan wajib diisi!";
        } elseif (empty($_SESSION['cart'])) {
            $error_message = "Keranjang belanja Anda masih kosong!";
        } else {
            $valid_stok = true;
            foreach ($_SESSION['cart'] as $id => $qty) {
                $item = getSepatuById($id);
                if (!$item || $item['stok'] < $qty) {
                    $valid_stok = false;
                    $error_message = "Stok untuk produk '{$item['nama']}' tidak mencukupi saat proses checkout.";
                    break;
                }
            }

            if ($valid_stok) {
                $items_dibeli = [];
                $subtotal_seluruhnya = 0;

                foreach ($_SESSION['cart'] as $id => $qty) {
                    kurangiStok($id, $qty);
                    $item = getSepatuById($id);
                    $subtotal_item = hitungSubtotal($item['harga'], $qty);
                    $subtotal_seluruhnya += $subtotal_item;

                    $items_dibeli[] = [
                        'nama' => $item['nama'],
                        'gambar' => $item['gambar'],
                        'harga_satuan' => $item['harga'],
                        'jumlah' => $qty,
                        'subtotal' => $subtotal_item
                    ];
                }

                $kalkulasi = hitungTotalBayar($subtotal_seluruhnya, 10); // PPN 10%

                $checkout_receipt = [
                    'nama_pemesan' => $nama_pemesan,
                    'items' => $items_dibeli,
                    'subtotal' => $kalkulasi['subtotal'],
                    'pajak' => $kalkulasi['pajak'],
                    'total' => $kalkulasi['total']
                ];

                $_SESSION['cart'] = [];
                $sepatu_list = getAllSepatu();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog & Keranjang Belanja - Fision Ara Store</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<header>
    <h1>Fision Ara Store</h1>
    <nav>
        <a href="index.php">Beranda & Profil</a>
        <a href="katalog.php" class="active">Katalog & Keranjang</a>
    </nav>
</header>

<div class="container">
    <div class="checkout-box">
        <h2>Katalog & Keranjang Belanja</h2>
        <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">Pilih produk, masukkan ke keranjang, dan sesuaikan jumlah pembelian Anda di tabel keranjang.</p>
        
        <?php if (!empty($error_message)): ?>
            <div class="error-msg"><?= $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div style="background: #dcfce7; color: #166534; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #86efac;">
                <?= $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Form Tambah Produk ke Keranjang -->
        <form action="katalog.php" method="POST" style="margin-bottom: 2rem;">
            <input type="hidden" name="aksi" value="tambah_keranjang">
            
            <div class="form-group">
                <label>Pilih Produk Sepatu:</label>
                <div class="product-select-grid">
                    <?php foreach ($sepatu_list as $sepatu): ?>
                        <?php 
                            $is_checked = ($sepatu['id'] === $selected_id) ? 'checked' : '';
                        ?>
                        <label class="product-radio-card" style="<?= ($sepatu['stok'] <= 0) ? 'opacity: 0.5; cursor: not-allowed;' : ''; ?>">
                            <input type="radio" name="sepatu_id" value="<?= $sepatu['id']; ?>" <?= $is_checked; ?> <?= ($sepatu['stok'] <= 0) ? 'disabled' : 'required'; ?>>
                            <img src="<?= $sepatu['gambar']; ?>" alt="<?= $sepatu['nama']; ?>">
                            <div class="card-content" style="width: 100%;">
                                <div style="font-size: 0.95rem; font-weight: 600;"><?= $sepatu['nama']; ?></div>
                                <div style="color: #2563eb; font-size: 0.85rem; margin-top: 2px;"><?= formatRupiah($sepatu['harga']); ?></div>
                                <div style="margin-top: 4px;">
                                    <?php if ($sepatu['stok'] > 0): ?>
                                        <span class="badge-stok" style="font-size: 0.75rem;">Stok: <?= $sepatu['stok']; ?></span>
                                    <?php else: ?>
                                        <span class="badge-stok badge-habis" style="font-size: 0.75rem;">Habis</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="form-group" style="max-width: 300px;">
                <label for="jumlah">Jumlah Pasang:</label>
                <input type="number" id="jumlah" name="jumlah" min="1" value="1" required>
            </div>

            <button type="submit" class="btn">+ Tambah ke Keranjang</button>
        </form>

        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 2rem 0;">

        <!-- Daftar Item di Keranjang Belanja -->
        <h3>Keranjang Belanja Anda</h3>
        <?php if (!empty($_SESSION['cart'])): ?>
            <div style="overflow-x: auto; margin-top: 1rem;">
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 1.5rem;">
                    <thead>
                        <tr style="background: #f1f5f9; text-align: left;">
                            <th style="padding: 0.75rem; border-bottom: 2px solid #cbd5e1;">Produk</th>
                            <th style="padding: 0.75rem; border-bottom: 2px solid #cbd5e1;">Harga Satuan</th>
                            <th style="padding: 0.75rem; border-bottom: 2px solid #cbd5e1; text-align: center;">Jumlah</th>
                            <th style="padding: 0.75rem; border-bottom: 2px solid #cbd5e1;">Subtotal</th>
                            <th style="padding: 0.75rem; border-bottom: 2px solid #cbd5e1; text-align: center;">Aksi (Kurangi / Hapus)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_sementara = 0;
                        foreach ($_SESSION['cart'] as $id => $qty): 
                            $item = getSepatuById($id);
                            if (!$item) continue;
                            $sub = hitungSubtotal($item['harga'], $qty);
                            $total_sementara += $sub;
                        ?>
                            <tr>
                                <td style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 0.75rem;">
                                    <img src="<?= $item['gambar']; ?>" alt="" style="width: 50px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <strong><?= $item['nama']; ?></strong>
                                </td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0;"><?= formatRupiah($item['harga']); ?></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0; text-align: center; font-weight: bold;">
                                    <?= $qty; ?> pasang
                                </td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #2563eb;"><?= formatRupiah($sub); ?></td>
                                <td style="padding: 0.75rem; border-bottom: 1px solid #e2e8f0; text-align: center;">
                                    <div style="display: flex; gap: 0.4rem; justify-content: center; align-items: center;">
                                        <!-- Tombol Kurangi (-1) -->
                                        <form action="katalog.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="aksi" value="kurangi_qty">
                                            <input type="hidden" name="sepatu_id" value="<?= $id; ?>">
                                            <button type="submit" title="Kurangi 1" style="background: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-weight: bold;">-</button>
                                        </form>

                                        <!-- Tombol Tambah (+1) -->
                                        <form action="katalog.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="aksi" value="tambah_qty">
                                            <input type="hidden" name="sepatu_id" value="<?= $id; ?>">
                                            <button type="submit" title="Tambah 1" style="background: #e2e8f0; color: #1e293b; border: 1px solid #cbd5e1; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-weight: bold;">+</button>
                                        </form>

                                        <!-- Tombol Hapus Seluruhnya -->
                                        <form action="katalog.php" method="POST" style="display:inline;">
                                            <input type="hidden" name="aksi" value="hapus_item">
                                            <input type="hidden" name="sepatu_id" value="<?= $id; ?>">
                                            <button type="submit" style="background: #fee2e2; color: #991b1b; border: 1px solid #f87171; padding: 0.3rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem;">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr style="background: #f8fafc; font-weight: bold;">
                            <td colspan="3" style="padding: 0.75rem; text-align: right;">Total Estimasi Belanja:</td>
                            <td colspan="2" style="padding: 0.75rem; color: #2563eb; font-size: 1.1rem;"><?= formatRupiah($total_sementara); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Form Final Checkout Keranjang -->
            <form action="katalog.php" method="POST" style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #cbd5e1;">
                <input type="hidden" name="aksi" value="checkout_semua">
                <h3>3. Konfirmasi Pesanan & Pembayaran</h3>
                <div class="form-group" style="margin-top: 1rem;">
                    <label for="nama_pemesan">Nama Pemesan:</label>
                    <input type="text" id="nama_pemesan" name="nama_pemesan" required placeholder="Masukkan nama lengkap Anda">
                </div>
                <button type="submit" class="btn" style="background: #16a34a;">Proses Checkout Semua Produk</button>
            </form>
        <?php else: ?>
            <p style="color: #64748b; font-style: italic; margin-top: 0.5rem;">Keranjang belanja Anda masih kosong. Silakan pilih produk di atas lalu klik tombol tambah ke keranjang.</p>
        <?php endif; ?>

        <!-- Nota / Hasil Checkout Berhasil -->
        <?php if ($checkout_receipt): ?>
            <div class="nota">
                <h3 style="color: #16a34a;">✔ Checkout Berhasil!</h3>
                <p style="margin-top: 0.5rem; color: #475569;">Terima kasih, <strong><?= $checkout_receipt['nama_pemesan']; ?></strong>. Pesanan multi-produk Anda telah berhasil diproses dan stok telah diperbarui.</p>
                
                <table style="margin-top: 1rem;">
                    <thead>
                        <tr style="border-bottom: 2px solid #94a3b8;">
                            <th style="text-align: left; padding: 0.4rem;">Produk</th>
                            <th style="text-align: center; padding: 0.4rem;">Jumlah</th>
                            <th style="text-align: right; padding: 0.4rem;">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checkout_receipt['items'] as $item): ?>
                            <tr>
                                <td style="padding: 0.4rem;"><?= $item['nama']; ?> <br><small style="color: #64748b;"><?= formatRupiah($item['harga_satuan']); ?></small></td>
                                <td style="text-align: center; padding: 0.4rem;"><?= $item['jumlah']; ?> pasang</td>
                                <td style="text-align: right; padding: 0.4rem;"><?= formatRupiah($item['subtotal']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2" style="padding-top: 0.75rem;">Subtotal Keseluruhan</td>
                            <td style="text-align: right; padding-top: 0.75rem;"><?= formatRupiah($checkout_receipt['subtotal']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="2">PPN (10%)</td>
                            <td style="text-align: right;"><?= formatRupiah($checkout_receipt['pajak']); ?></td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="2">Total Bayar</td>
                            <td style="text-align: right; color: #2563eb;"><?= formatRupiah($checkout_receipt['total']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>