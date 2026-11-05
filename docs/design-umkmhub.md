# Panduan Desain UMKMHub v2

Dokumen ini adalah turunan dari PRD UMKMHub v2 (bagian 9). Isinya: design token, komponen, spesifikasi halaman publik dan pembeli, serta dashboard Admin UMKM dan Superadmin.

**Sumber desain**

| Sumber | Dipakai untuk |
| --- | --- |
| Chasus (shot Dribbble "AI-Powered E-Commerce Marketplace UI/UX") | Halaman publik dan pembeli: katalog, detail produk, keranjang, checkout, konfirmasi, mobile |
| Shopy (dashboard admin e-commerce) | Layout dashboard Admin UMKM dan Superadmin |
| Palet "Cloudy Valley, Tsunami, Weather Board, Volcanic Island, Red Oxide" | Seluruh warna UMKMHub |

**Teknologi:** Laravel 11, Blade, Tailwind CSS. Grafik memakai Chart.js (usulan; ringan dan mudah dipasang di Blade).

**Batasan produk yang memengaruhi desain:** pembayaran hanya simulasi (tanpa payment gateway), tidak ada AI Assistant, ulasan, wishlist, diskon, atau chat pada v2.

---

## 1. Prinsip Desain

1. **Mobile first.** Mayoritas pembeli dan pemilik UMKM memakai ponsel.
2. **Jujur soal simulasi.** Label "MODE SIMULASI" selalu terlihat di checkout, pembayaran, dan footer.
3. **UMKM punya wajah.** Logo, nama, kota, dan deskripsi toko tampil di kartu produk, kartu toko, dan halaman toko.
4. **Satu tugas utama per layar**, terutama di dashboard Admin UMKM (pemilik usaha, bukan ahli teknologi).
5. **Status eksplisit.** Layar kosong, memuat, dan galat selalu punya tampilan dan teks penjelas.
6. **Satu set komponen** dipakai ulang di semua peran supaya konsisten dan murah dipelihara.
7. **Warna bukan satu-satunya penanda.** Status selalu berlabel teks.

---

## 2. Design Token

### 2.1 Palet warna

Lima warna pertama berasal dari palet yang Anda pilih. Warna turunan adalah usulan. Angka kontras adalah perkiraan terhadap latar putih; verifikasi dengan pemeriksa kontras sebelum final.

| Nama token | Hex | Peran | Catatan kontras |
| --- | --- | --- | --- |
| `brand` (Red Oxide) | `#5E1C23` | Tombol utama, tautan aktif, item menu aktif, badge keranjang | Sekitar 12:1. Teks putih di atasnya aman |
| `ink-muted` (Volcanic Island) | `#605246` | Teks sekunder, ikon gelap | Sekitar 7,5:1 |
| `sand` (Weather Board) | `#A1947F` | Aksen hangat: badge "UMKM", pembatas, ilustrasi kosong | Sekitar 3:1. Jangan untuk teks kecil |
| `mist-deep` (Tsunami) | `#849AAD` | Garis aktif, ikon besar, warna seri grafik | Sekitar 2,9:1. Jangan untuk teks |
| `mist` (Cloudy Valley) | `#B1C7D4` | Garis kartu, latar chip terpilih, hover baris | Hanya latar dan garis. Teks di atasnya memakai `brand` atau `ink` |
| `surface` (turunan) | `#FFFFFF` | Kartu, tabel, input | |
| `canvas` (turunan) | `#F4F7F9` | Latar halaman dan sidebar | |
| `ink` (turunan) | `#2B2420` | Teks utama, judul | Jauh di atas 7:1 |
| `brand-soft` (turunan) | `#F3E9EA` | Latar lembut Red Oxide: badge dibatalkan, baris disorot | |
| `sand-soft` (turunan) | `#EFEBE4` | Latar lembut Weather Board: badge menunggu | |
| `ok` (turunan) | `#2F6F4E` | Status selesai, notifikasi berhasil | |

### 2.2 Konfigurasi Tailwind

```js
// tailwind.config.js
export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js'],
  theme: {
    extend: {
      colors: {
        brand: { DEFAULT: '#5E1C23', soft: '#F3E9EA' },
        ink: { DEFAULT: '#2B2420', muted: '#605246' },
        mist: { DEFAULT: '#B1C7D4', deep: '#849AAD', canvas: '#F4F7F9' },
        sand: { DEFAULT: '#A1947F', soft: '#EFEBE4' },
        ok: '#2F6F4E',
      },
      fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
      borderRadius: { card: '16px', control: '12px' },
      boxShadow: { card: '0 1px 2px rgba(43, 36, 32, 0.06)' },
    },
  },
};
```

### 2.3 Tipografi

Font: Plus Jakarta Sans (usulan; sans bulat bersih, mirip karakter pada referensi, nyaman untuk Bahasa Indonesia).

| Peran | Kelas Tailwind | Ukuran |
| --- | --- | --- |
| Judul halaman | `text-2xl font-bold md:text-3xl` | 24 / 30 px |
| Judul seksi, judul kartu | `text-lg font-semibold` | 18 px |
| Isi | `text-base` | 16 px |
| Keterangan, label tabel | `text-sm` | 14 px (minimum untuk teks yang harus dibaca) |
| Harga di kartu | `text-xl font-bold` | 20 px |
| Angka statistik dashboard | `text-3xl font-bold` | 30 px |

Pada referensi banyak teks abu berukuran sekitar 10 sampai 11 px; di UMKMHub ukuran terkecil adalah 14 px.

### 2.4 Bentuk, jarak, dan breakpoint

- Kartu: `rounded-card border border-mist bg-surface shadow-card`. Tanpa efek kaca dan gradasi.
- Tombol dan input: `rounded-control`, tinggi 44 px (area sentuh nyaman di ponsel).
- Jarak: kelipatan 4 px (`p-4`, `p-6`, `gap-4`, `gap-6`). Padding konten halaman `p-4 md:p-6`.
- Breakpoint Tailwind bawaan: `sm` 640, `md` 768, `lg` 1024, `xl` 1280.
- Format uang: `Rp15.000` (titik ribuan, tanpa desimal).
- Format tanggal: `3 Nov 2026`, waktu 24 jam WIB.

---

## 3. Komponen

Setiap komponen dibuat sebagai komponen Blade anonim di `resources/views/components/`.

| Komponen | Isi dan perilaku |
| --- | --- |
| `x-button` | Varian `primary` (latar `brand`, teks putih), `secondary` (garis `mist-deep`, teks `ink`), `danger-ghost`. Status memuat (spinner, tombol nonaktif) dan nonaktif |
| `x-input`, `x-select`, `x-textarea` | Label di atas, teks bantuan, pesan galat merah (`brand`) di bawah dengan ikon, tinggi 44 px |
| `x-product-card` | Foto 1:1, nama produk (maks 2 baris), nama toko dan kota, harga, tombol keranjang. Jika stok 0: label "Habis" dan tombol nonaktif |
| `x-store-card` | Logo, nama toko, kota, kategori, jumlah produk, tautan "Kunjungi toko" |
| `x-status-badge` | Pil berlabel teks, lihat 3.1 |
| `x-stepper` | Tiga langkah checkout (Alamat, Pembayaran simulasi, Tinjau): bulatan bernomor, garis penghubung, langkah selesai berisi centang |
| `x-simulation-banner` | Pita `sand-soft` dengan ikon info: "MODE SIMULASI: tidak ada uang sungguhan yang diproses" |
| `x-stat-card` | Ikon dalam kotak `brand-soft`, label, angka besar, keterangan perubahan atau waktu pembaruan |
| `x-table` | Header `canvas`, baris `hover:bg-mist/20`, di ponsel berubah menjadi daftar kartu |
| `x-empty-state` | Ilustrasi sederhana warna `sand`, judul, satu kalimat, satu tombol aksi |
| `x-toast` | Pojok kanan atas; varian sukses (`ok`), galat (`brand`), info (`mist-deep`); hilang sendiri 5 detik |
| `x-modal`, `x-drawer` | Modal untuk konfirmasi (tolak toko, batalkan pesanan); drawer dari kanan untuk form produk dan filter di ponsel |
| `x-pagination` | Mengikuti `links` dan `meta` dari API (maks 100 per halaman) |

### 3.1 Badge status pesanan

| Status | Label | Latar | Teks |
| --- | --- | --- | --- |
| `pending_payment` | Menunggu pembayaran | `sand-soft` | `ink` |
| `paid` | Sudah dibayar | `mist/40` | `ink` |
| `processing` | Diproses | `mist` | `brand` |
| `shipped` | Dikirim | `ink-muted` | putih |
| `completed` | Selesai | `ok` | putih |
| `cancelled` | Dibatalkan | `brand-soft` | `brand` |

Status toko: `pending` (sand-soft), `approved` (ok), `rejected` (brand-soft), `suspended` (ink-muted dengan teks putih).

---

## 4. Halaman Publik dan Pembeli

Berdasarkan referensi Chasus, dengan penyesuaian UMKM.

### 4.1 Keputusan terhadap referensi

| Elemen referensi | Keputusan | Adaptasi |
| --- | --- | --- |
| Navbar: logo, menu, keranjang bertanda, Masuk (outline) dan Daftar (solid) | Pertahankan | Menu: Beranda, UMKM, Produk, Kategori. Tombol "Daftarkan UMKM" menonjol bagi pengunjung |
| Menu dan kartu AI Assistant | Hapus | Diganti blok "UMKM pilihan" |
| Sidebar kategori 12 item | Ubah | Kategori dari database, tampil sebagai chip dan filter |
| Bilah pencarian dengan chip "Results for" | Pertahankan | Chip "Hasil untuk: ..." yang bisa dihapus |
| Kartu produk | Pertahankan | Deskripsi lorem diganti nama toko dan kota |
| Harga coret, hati, bintang dan ulasan | Hapus atau ganti | Ganti dengan "Terjual N" dari pesanan selesai (P1) |
| Filter kanan: gender, warna, bahan, merek | Ubah | Filter: kategori, rentang harga, kota toko, hanya stok tersedia |
| Detail produk dan panel pesanan | Pertahankan | Tanpa ulasan; tampilkan stok |
| Blok detail penjual | Pertahankan sebagai inti | Jadi kartu toko UMKM |
| Checkout tiga langkah dengan Edit per bagian | Pertahankan | Pembayaran jadi simulasi |
| Metode bayar nyata dan layar "Processing payment" | Ganti | Panel simulasi dengan tiga tombol hasil |
| Konfirmasi "Order Placed" | Pertahankan | Kode pesanan, batas bayar 15 menit, pembatalan selama belum diproses |
| Navigasi bawah mobile | Pertahankan | Beranda, UMKM, Keranjang, Akun |
| Latar foto, efek kaca, gradasi ungu | Tinggalkan | Latar terang polos |

### 4.2 Landing

```
+------------------------------------------------------------------+
| [Logo UMKMHub]   Beranda  UMKM  Produk  Kategori   [Keranjang]   |
|                                         [Masuk] [Daftarkan UMKM] |
+------------------------------------------------------------------+
|   Temukan dan dukung UMKM pilihan                                 |
|   Satu tempat untuk toko-toko UMKM.                               |
|   [ Lihat UMKM ]   [ Daftarkan UMKM Anda ]   <- dua aksi setara   |
+------------------------------------------------------------------+
|   UMKM terbaru:   [kartu toko] [kartu toko] [kartu toko]          |
|   Produk pilihan: [kartu produk] x 4                              |
|   Cara kerja:  1 Pilih toko   2 Checkout   3 Pantau pesanan       |
+------------------------------------------------------------------+
| Footer: tentang, kontak, catatan "Situs ini mode simulasi"        |
+------------------------------------------------------------------+
```

### 4.3 Katalog dan hasil pencarian

```
+------------------------------------------------------------------+
| Navbar                                                            |
+--------------+---------------------------------------------------+
| FILTER       | [ Cari produk atau toko...           ] [Cari]      |
| Kategori     | Hasil untuk: [ keripik  x ]      Urutkan: [Harga v]|
| ( ) Makanan  |---------------------------------------------------|
| ( ) Fashion  | [kartu] [kartu] [kartu] [kartu]                    |
| Rentang harga| [kartu] [kartu] [kartu] [kartu]                    |
| Kota toko    |                                                    |
| [x] Stok ada | < 1 2 3 >                                          |
| [Hapus semua]|                                                    |
+--------------+---------------------------------------------------+
```

Grid: 4 kolom di `xl`, 3 di `lg`, 2 di bawahnya. Di ponsel, filter menjadi drawer yang dibuka dari tombol "Filter".

### 4.4 Detail produk

Galeri dengan thumbnail di kiri, nama, harga, stok, pemilih jumlah, rincian dalam aksordeon (Deskripsi, Kategori), panel "Pesanan Anda" di kanan dengan tombol **Beli Sekarang** (utama) dan **Tambah ke Keranjang** (sekunder), serta kartu toko UMKM di bawahnya. Jika stok 0, kedua tombol nonaktif dengan teks "Stok habis".

### 4.5 Keranjang

Item dikelompokkan per toko (karena checkout dipecah menjadi satu pesanan per toko). Tiap kelompok: nama toko, daftar item dengan stepper jumlah dan tombol hapus, subtotal. Ringkasan di kanan: subtotal, ongkir simulasi tetap, total, tombol **Checkout**. Pengunjung yang belum login diarahkan ke login lalu kembali ke keranjang.

### 4.6 Checkout tiga langkah

1. **Alamat:** pilih alamat tersimpan atau isi baru (penerima, telepon, alamat, kota). Tombol "Lanjut".
2. **Pembayaran simulasi:** `x-simulation-banner`, ringkasan pesanan, tiga tombol hasil (**Bayar Berhasil**, **Bayar Gagal**, **Biarkan Kedaluwarsa**), hitung mundur 15 menit. Tidak ada input kartu atau rekening.
3. **Tinjau:** rincian item, alamat, dan pembayaran masing-masing dengan tombol **Ubah** yang kembali ke langkah terkait tanpa menghapus isian.

Konfirmasi: kode pesanan, badge status, kalimat "Pesanan dapat dibatalkan selama belum diproses", tombol **Lihat Pesanan** dan **Lanjut Belanja**. Satu keranjang multi toko menghasilkan beberapa kode pesanan; tampilkan semuanya.

### 4.7 Mobile

Navigasi bawah (Beranda, UMKM, Keranjang, Akun), kolom pencarian di atas, chip kategori yang bisa digeser, kartu produk dua kolom, tombol utama lebar penuh di dasar layar pada detail produk dan checkout.

---

## 5. Dashboard Admin UMKM

Berdasarkan referensi Shopy: sidebar kiri, bar atas, sapaan, kartu statistik, dua grafik, dan tabel pesanan.

### 5.1 Keputusan terhadap referensi Shopy

| Elemen referensi | Keputusan | Adaptasi UMKMHub |
| --- | --- | --- |
| Sidebar dengan item aktif berlatar penuh | Pertahankan | Item aktif berlatar `brand`, teks putih |
| Menu Customers, Vendors, Payments | Ganti | Admin UMKM: Dashboard, Produk, Stok, Pesanan, Profil Toko |
| Bar atas: pencarian, profil, lonceng | Pertahankan | Tambah **pemilih toko** (maksimal 2 toko per akun). Lonceng ditunda (P2) |
| Sapaan "Good Morning" dengan emoji | Ubah | "Selamat pagi, {nama}" sesuai jam, tanpa emoji; subjudul nama toko aktif |
| Filter periode "This Month" dan tombol unduh | Pertahankan | Periode: Hari ini, 7 hari, Bulan ini. Unduh CSV pesanan (P2) |
| Empat kartu statistik dengan keterangan "Update: tanggal" | Pertahankan, ubah isi | Lihat 5.3. Keterangan memakai ukuran 14 px |
| Grafik pendapatan (area) dan statistik pesanan (batang) | Pertahankan | Satu warna seri; sumbu Y berskala rapi (kelipatan seragam); tanpa gelombang berlebihan |
| Tabel pesanan dengan badge status pucat dan ikon lihat dan ubah | Ubah | Badge berlabel dengan kontras cukup; aksi utama berupa tombol berlabel sesuai status |
| (tidak ada) | Tambah | Status kosong, memuat, galat; peringatan stok menipis; pesan konflik versi stok |

### 5.2 Layout

```
+-----------+------------------------------------------------------+
| [Logo]    | [ Cari pesanan/produk... ]  [Toko: Keripik Bu Sari v]|
|           |                                   [Bu Sari v] [lonceng]|
| Dashboard |------------------------------------------------------|
| Produk    | Selamat pagi, Sari                  [Bulan ini v]     |
| Stok      | Toko: Keripik Bu Sari                                  |
| Pesanan   |                                                       |
| Profil    | [Penjualan] [Pesanan baru] [Produk aktif] [Stok tipis]|
| Toko      |                                                       |
|           | +---------------------+ +--------------------------+ |
|           | | Penjualan (simulasi)| | Pesanan per hari         | |
|           | |  grafik area        | |  grafik batang           | |
|           | +---------------------+ +--------------------------+ |
|           |                                                       |
| [Keluar]  | Pesanan terbaru                         [Lihat semua] |
|           | Kode | Tanggal | Pelanggan | Total | Status | Aksi    |
+-----------+------------------------------------------------------+
```

- Sidebar: lebar 256 px (`w-64`), latar `surface`, garis kanan `mist`. Di bawah `lg`, sidebar menjadi drawer yang dibuka dari ikon menu.
- Bar atas: tinggi 64 px, garis bawah `mist`.
- Konten: `canvas` sebagai latar, kartu `surface` di atasnya.

### 5.3 Kartu statistik (Admin UMKM)

| Kartu | Isi | Sumber data |
| --- | --- | --- |
| Penjualan (simulasi) | Jumlah total pesanan berstatus `paid` ke atas pada periode, dalam Rupiah, dengan teks "simulasi" | Pesanan toko |
| Pesanan baru | Jumlah pesanan `paid` yang belum diproses; klik menuju daftar pesanan terfilter | Pesanan toko |
| Produk aktif | Jumlah produk `is_active` | Produk toko |
| Stok menipis | Jumlah produk di bawah ambang (usulan ambang awal 5; dapat dikonfigurasi) | Stok toko (`low_stock=true`) |

Setiap kartu: ikon dalam kotak `brand-soft`, label 14 px, angka `text-3xl`, dan baris bawah "Diperbarui {jam}".

### 5.4 Grafik

- **Penjualan (simulasi):** grafik garis dengan area samar `mist`, satu seri warna `brand`, tooltip saat disentuh atau diarahkan. Label sumbu X: tanggal; sumbu Y: Rupiah dengan kelipatan seragam.
- **Pesanan per hari:** grafik batang satu warna `mist-deep`, jumlah pesanan per hari, sumbu Y bilangan bulat.
- Keduanya memakai Chart.js, warna dibaca dari token, dan punya ringkasan teks bagi pembaca layar (tabel tersembunyi atau `aria-label`).
- Status kosong: "Belum ada pesanan pada periode ini" dengan ilustrasi `x-empty-state`.

### 5.5 Tabel pesanan terbaru

| Kolom | Isi |
| --- | --- |
| Kode | Kode pesanan, tautan ke detail |
| Tanggal | `3 Nov 2026, 14:20` |
| Pelanggan | Nama pembeli |
| Total | Rupiah |
| Status | `x-status-badge` |
| Aksi | Tombol utama sesuai status berikutnya, dan tombol **Lihat** |

Tombol aksi utama per status: `paid` menampilkan **Proses**, `processing` menampilkan **Kirim**, `shipped` menampilkan **Selesai**. Status `pending_payment`, `completed`, dan `cancelled` tidak punya aksi ubah. Perubahan status memakai konfirmasi singkat; transisi tidak sah ditolak server dengan `INVALID_STATUS_TRANSITION` dan ditampilkan sebagai toast galat.

Di ponsel, baris tabel menjadi kartu bertumpuk dengan badge di kanan atas dan tombol aksi di bawah.

### 5.6 Halaman Produk

- Toolbar: pencarian, filter kategori dan status, tombol **Tambah Produk** (membuka drawer).
- Tabel: foto kecil, nama, kategori, harga, stok, status aktif (saklar), aksi **Ubah** dan **Hapus** (dengan konfirmasi).
- Form drawer: nama, deskripsi, kategori, harga, foto (jpg, png, webp, maks 2 MB), status aktif. Galat validasi `422` tampil per kolom.

### 5.7 Halaman Stok (Optimistic Concurrency Control)

- Tabel: produk, stok saat ini, penanda **Stok menipis** (chip `brand-soft`), tombol **Ubah stok**.
- Ubah stok dilakukan di baris (input angka dan tombol **Simpan**). Permintaan membawa `version` yang terakhir dibaca.
- **Jika server membalas `409` (`STOK_VERSION_MISMATCH`):** baris disorot `brand-soft`, input mempertahankan angka yang diketik, toast galat: "Stok ini baru saja diubah pihak lain." dan tombol **Muat nilai terbaru** yang mengambil stok dan versi baru lalu membiarkan pengguna menyimpan ulang. Tidak ada penimpaan diam-diam.
- Filter **Stok menipis** mengikuti `?low_stock=true`.

### 5.8 Halaman Pesanan

Tab status (Semua, Menunggu pembayaran, Sudah dibayar, Diproses, Dikirim, Selesai, Dibatalkan) dengan jumlah, tabel seperti 5.5 dengan paginasi. Halaman detail pesanan: kode, status dan riwayatnya, daftar item (nama dan harga saat checkout), alamat, total, dan tombol aksi status berikutnya.

### 5.9 Profil Toko dan pemilih toko

- Profil toko: nama, deskripsi, logo, kota, alamat, kontak. Menampilkan status toko (`pending`, `approved`, `rejected`, `suspended`) dan, bila ditolak, alasan penolakan dengan tombol **Ajukan ulang**.
- Pemilih toko di bar atas: daftar maksimal 2 toko milik akun; beralih toko memuat ulang seluruh data dashboard. Tombol **Tambah toko** nonaktif disertai teks "Maksimal 2 toko per akun" saat batas tercapai.
- Admin yang tokonya belum disetujui hanya melihat layar status pendaftaran, bukan dashboard.

### 5.10 Catatan kebutuhan API

PRD v2 belum memuat endpoint statistik untuk dashboard Admin UMKM. Usulan penambahan: `GET /my-stores/{store_id}/stats?period=today|7d|month` yang mengembalikan total penjualan simulasi, jumlah pesanan baru, jumlah produk aktif, jumlah stok menipis, dan deret harian untuk dua grafik.

---

## 6. Dashboard Superadmin

Memakai layout dan komponen yang sama dengan Admin UMKM.

| Bagian | Isi |
| --- | --- |
| Menu sidebar | Dashboard, Persetujuan UMKM, Toko, Pengguna, Kategori, Pesanan, Statistik |
| Kartu statistik | UMKM aktif, **Menunggu persetujuan** (kartu beraksen `brand`, klik menuju antrean), Total produk, Total pesanan |
| Grafik | Pendaftaran UMKM per minggu (batang), pesanan platform per hari (garis) |
| Antrean persetujuan | Tabel: nama toko, pemilik, kota, kategori, tanggal daftar; aksi **Lihat**, **Setujui**, **Tolak**. **Tolak** membuka modal dengan kolom alasan wajib |
| Kelola toko | Tabel semua toko dengan badge status; aksi **Suspend** atau **Aktifkan** dengan konfirmasi (produk toko suspend tidak tampil di publik) |
| Pengguna | Tabel dengan pencarian dan paginasi, kolom peran |
| Kategori | Tabel dengan tambah dan ubah lewat drawer; tombol hapus nonaktif jika kategori masih dipakai, disertai alasan |

---

## 7. Aksesibilitas dan Responsif

- Kontras teks memenuhi WCAG AA; warna `mist-deep` dan `sand` tidak dipakai sebagai warna teks kecil.
- Semua kontrol dapat dioperasikan dengan keyboard; indikator fokus tampak jelas (`focus-visible:ring-2 ring-brand`).
- Area sentuh minimal 44 x 44 px.
- Gambar memiliki teks alternatif (nama produk atau logo toko).
- Grafik punya `aria-label` ringkas dan tabel data alternatif.
- Tabel di bawah `md` berubah menjadi daftar kartu.
- Teks tidak pernah dipotong tanpa cara membaca penuh (tooltip atau halaman detail).
- Hormati `prefers-reduced-motion`: matikan animasi grafik dan transisi besar.

---

## 8. Struktur Berkas Blade (usulan)

```
resources/
  views/
    layouts/
      public.blade.php          // navbar + footer
      dashboard.blade.php       // sidebar + topbar
    components/
      button.blade.php, input.blade.php, product-card.blade.php,
      store-card.blade.php, status-badge.blade.php, stepper.blade.php,
      simulation-banner.blade.php, stat-card.blade.php, table.blade.php,
      empty-state.blade.php, toast.blade.php, modal.blade.php, drawer.blade.php
    pages/
      landing.blade.php, stores/index.blade.php, stores/show.blade.php,
      products/index.blade.php, products/show.blade.php,
      cart.blade.php, checkout/{address,payment,review,done}.blade.php,
      orders/{index,show}.blade.php
    dashboard/
      umkm/{index,products,stocks,orders,order-show,store-profile}.blade.php
      superadmin/{index,applications,stores,users,categories}.blade.php
```

---

## 9. Urutan Pengerjaan Desain

1. Terapkan token di `tailwind.config.js` dan buat komponen dasar (`x-button`, `x-input`, `x-status-badge`, `x-product-card`).
2. Layout `public` dan `dashboard`.
3. Halaman P0 publik: landing, daftar UMKM, halaman toko, katalog, detail produk.
4. Keranjang, checkout, pembayaran simulasi, konfirmasi.
5. Dashboard Admin UMKM: dashboard, produk, stok, pesanan, profil toko.
6. Dashboard Superadmin.
7. Pengujian kegunaan singkat dengan 3 sampai 5 calon pengguna, lalu perbaikan.
