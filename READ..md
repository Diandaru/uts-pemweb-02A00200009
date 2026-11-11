# UTS Pemrograman Web - Warung Makan Berkah

Proyek Mini Individu Website Profil & Katalog Dinamis Berbasis PHP Dasar.

---

## 1. Identitas Mahasiswa
- **Nama Lengkap** : Diandaru Irfansyah Fathiin
- **NIM**          : 025A00200009
- **Kelas**        : Kelas Malam
- **Mata Kuliah**  : Pemrograman Web 
- **Program Studi**: S1 Informatika, Fakultas Teknik, Tiga Serangkai University

---

## 2. Deskripsi Singkat Tema
Warung Makan Berkah adalah aplikasi web berbasis PHP dinamis untuk usaha kuliner UMKM. Website ini menyajikan informasi profil usaha, cerita komitmen, daftar menu favorit/unggulan, katalog menu lengkap (makanan utama, minuman, dan camilan), serta fitur pemesanan dan simulasi perhitungan total tagihan pembayaran secara transparan dan instan tanpa menggunakan database eksternal (menggunakan data array PHP).

---

## 3. Alur Website (USer Flow & Data Flow)
                       ┌─────────────────────────┐
                       │        Pengunjung       │
                       └────────────┬────────────┘
                                    │
           ┌────────────────────────┴────────────────────────┐
           ▼                                                 ▼
┌──────────────────────┐                          ┌──────────────────────┐
│      index.php       │                          │     katalog.php      │
│   (Profil & Toko)    │                          │  (Katalog & Keranjang)│
└──────────┬───────────┘                          └──────────┬───────────┘
           │                                                 │
           │  Menampilkan Koleksi Sepatu                     │  Proses POST (Keranjang & Qty)
           ▼                                                 ▼
┌────────────────────────────────────────────────────────────────────────┐
│                             functions.php                              │
│  - formatRupiah()    -> Format mata uang Rupiah                        │
│  - getAllSepatu()    -> Mengambil data sepatu dari session/array       │
│  - getSepatuById()   -> Mencari sepatu berdasarkan ID                  │
│  - kurangiStok()     -> Mengurangi stok saat checkout berhasil         │
└──────────────────────────────────┬─────────────────────────────────────┘
                                   │
                                   │  Membaca Master Data
                                   ▼
┌────────────────────────────────────────────────────────────────────────┐
│                                data.php                                │
│  $sepatu_list = [Array Multidimensi Produk Sepatu (ID,Nama, Harga,dll])│
└────────────────────────────────────────────────────────────────────────┘

## 4. Cara Menjalankan di Localhost

Aplikasi ini tidak membutuhkan database (semua data tersimpan dalam array PHP) dan tidak menggunakan framework apa pun.

### Opsi A: Menggunakan PHP Built-in Server (Paling Cepat & Praktis)
1. Buka terminal (Command Prompt / PowerShell / Terminal VS Code).
2. Arahkan direktori aktif ke dalam folder proyek ini:
   ```bash
   cd path/ke/tema-1-warung-makan
   ```
3. Jalankan server lokal bawaan PHP:
   ```bash
   php -S localhost:8000
   ```
4. Buka browser dan akses URL:
   ```
   http://localhost:8000
   ```

### Opsi B: Menggunakan XAMPP
1. Salin (*copy*) folder `tema-1-warung-makan ke direktori `htdocs` XAMPP Anda:
   - Windows: `C:\xampp\htdocs\tema-1-warung-makan 
2. Jalankan modul **Apache** pada XAMPP Control Panel.
3. Buka browser dan buka:
   ```
   http://localhost/tema-1-warung-makan index.php
   ```

---

## 5. Daftar Fitur & Penanggung Jawab File/Fungsi

No | Fitur Utama               | File Terkait | Fungsi / Logika Penangan | Keterangan

1  | Format Mata Uang Rupiah   | functions.php | formatRupiah()          |	Memformat angka numerik ke format mata uang Rupiah (contoh: Rp 1.500.000).

2  | Pengelolaan Sesi & Stok   | functions.php, katalog.php |	getAllSepatu(), kurangiStok() | 	Mengelola ketersediaan stok produk secara dinamis menggunakan sesi PHP ($_SESSION['sepatu_list']) agar berkurang secara real-time saat checkout.

3  | Render Otomatis Koleksi   | index.php, katalog.php	| foreach ($sepatu_list as $sepatu)	| Merender kartu produk sepatu secara dinamis dari array master tanpa duplikasi kode HTML manual.

4  | Keranjang Belanja Multi-Produk | katalog.php	| $_SESSION['cart'] | 	Memungkinkan pelanggan memilih beberapa produk berbeda, memasukkannya ke keranjang, menambah/mengurangi kuantitas per item, atau menghapusnya.

5  | Kalkulasi Subtotal & Pajak  | 	functions.php, katalog.php  | 	hitungSubtotal(), hitungTotalBayar()    |	Menghitung subtotal belanja, PPN 10%, serta total akumulasi tagihan secara otomatis dan transparan.

6  | Notifikasi & Nota Transaksi |	katalog.php  |	Kondisional $checkout_receipt   |	Menampilkan pesan error stok, notifikasi sukses, serta nota rincian belanja setelah checkout berhasil.
---


## 6. Daftar Fungsi Array Bawaan PHP yang Digunakan

No | Fungsi Bawaan Array   | Letak File:Baris   | Tujuan / Kegunaan dalam Proyek
1  | $_SESSION        | index.php, katalog.php, functions.php      | Menyimpan data state persisten seperti daftar stok produk ($_SESSION['sepatu_list']) dan item keranjang belanja ($_SESSION['cart']) selama sesi kunjungan.usaha (index.php).

2  | foreach (Array Iteration)   | index.php, katalog.php, functions.php  | Melakukan perulangan untuk merender kartu produk, daftar item di tabel keranjang belanja, serta kalkulasi total harga.

3  | isset() & empty() (Array Key Check)  | index.php, katalog.php | Memeriksa keberadaan parameter URL, data POST, serta status keaktifan sesi dan isi keranjang belanja sebelum diproses.
---

## 7. Tangkapan Layar Aplikasi

> Letakkan gambar tangkapan layar di dalam folder `screenshots/` dengan nama file `screenshot-profil.svg` dan `screenshot-katalog.svg`.

1. **Tampilan Halaman Profil (`index.php`)**:
   ![Tangkapan Layar Halaman Profil SpaceHub](screenshots/screenshot-profil.svg)
   *(Menampilkan navigasi semantik, hero banner ringkas, ringkasan statistik, profil singkat perusahaan, dan 3 fasilitas utama).*

2. **Tampilan Halaman Katalog (`katalog.php`)**:
   ![Tangkapan Layar Halaman Katalog SpaceHub](screenshots/screenshot-katalog.svg)
   *(Menampilkan kartu statistik hitung PHP, render kartu item dengan foreach, dan badge status).*

---

## 8. Sumber Referensi & Catatan Pemakaian AI

### Sumber Referensi:
1. Dokumentasi Resmi PHP: [https://www.php.net/manual/en/](https://www.php.net/manual/en/) (Referensi `strict_types`, `array_filter`, `array_column`, `array_sum`, `usort`, `match expression`).
2. MDN Web Docs: [https://developer.mozilla.org/](https://developer.mozilla.org/) (Referensi elemen semantik HTML5 dan CSS Box Model).
3. Standar Penulisan Kode PHP PSR-12: [https://www.php-fig.org/psr/psr-12/](https://www.php-fig.org/psr/psr-12/).

### Catatan Pemakaian AI:
- **Bagian yang dibantu**:  AI digunakan untuk merancang variasi data dummy pada array multidimensi (data.php), diagram alur kerja ASCII, serta penyusunan format dokumentasi README ini.
- **Verifikasi Mandiri**: Seluruh logika PHP (declare(strict_types=1);, fungsi buatan sendiri bertipe, sanitasi e(), dan fungsi manipulasi array) telah diuji, dipahami, dan diverifikasi langsung melalui PHP CLI / Localhost untuk memastikan tidak ada error, warning, maupun notice. Mahasiswa memahami alur eksekusi baris per baris dan siap mendemokannya saat sesi ujian di hadapan dosen.

---

## Panduan Tambahan: 5 Langkah Commit Git Bertahap

Untuk memenuhi kriteria Git Level 4 (minimal 5 commit bertahap dengan pesan bermakna), ikuti langkah berikut di terminal:

```bash
# Inisialisasi git repository
git init

# Commit 1: Tahap 1 - Rancang tema dan struktur folder awal
git add README.md .gitignore
git commit -m "feat(init): inisialisasi struktur proyek dan dokumentasi awal tema layanan kerja"

# Commit 2: Tahap 2 - Kerangka HTML semantik dan stylesheet CSS murni
git add style.css assets/
git commit -m "feat(ui): rancang antarmuka semantik, tata letak CSS box model, dan aset grafis"

# Commit 3: Tahap 3 - Data array multidimensi dan fungsi pembantu bertipe
git add data.php functions.php
git commit -m "feat(core): implementasi data array multidimensi dan fungsi logika bertipe PSR-12"

# Commit 4: Tahap 3 - Render halaman dinamis dengan foreach dan ringkasan hitung
git add index.php katalog.php
git commit -m "feat(render): integrasi render halaman profil simpel dan katalog dinamis dengan foreach"

# Commit 5: Tahap 4 - Refaktor clean code dan kelengkapan dokumentasi
git add screenshots/ README.md
git commit -m "docs: lengkapi dokumentasi teknis, tangkapan layar, dan persiapan review kode"
```


