<?php
// 1. Fungsi Format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

// 2. Fungsi Ambil Semua Produk Sepatu (dari Session jika ada)
function getAllSepatu() {
    if (isset($_SESSION['sepatu_list'])) {
        return $_SESSION['sepatu_list'];
    }
    global $sepatu_list;
    return $sepatu_list;
}

// 3. Fungsi Cari Sepatu berdasarkan ID
function getSepatuById($id) {
    $sepatu_list = getAllSepatu();
    foreach ($sepatu_list as $sepatu) {
        if ($sepatu['id'] == $id) {
            return $sepatu;
        }
    }
    return null;
}

// 4. Fungsi Hitung Total Harga per Item (Harga * Jumlah)
function hitungSubtotal($harga, $jumlah) {
    return $harga * (int)$jumlah;
}

// 5. Fungsi Hitung Total Bayar + Pajak (PPN 10%)
function hitungTotalBayar($subtotal, $pajakPersen = 10) {
    $pajak = $subtotal * ($pajakPersen / 100);
    return [
        'subtotal' => $subtotal,
        'pajak' => $pajak,
        'total' => $subtotal + $pajak
    ];
}

// 6. Fungsi Pengurangan Stok Saat Checkout Berhasil
function kurangiStok($id, $jumlahBeli) {
    if (!isset($_SESSION['sepatu_list'])) {
        global $sepatu_list;
        $_SESSION['sepatu_list'] = $sepatu_list;
    }

    foreach ($_SESSION['sepatu_list'] as &$sepatu) {
        if ($sepatu['id'] == $id) {
            if ($sepatu['stok'] >= $jumlahBeli) {
                $sepatu['stok'] -= $jumlahBeli;
                return true; 
            }
            return false; 
        }
    }
    return false;
}
?>