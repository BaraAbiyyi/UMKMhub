# PRD UMKMhub — Marketplace UMKM (Simulasi Transaksi)

Nov 3, 2026 · @b

## 1. Ringkasan Produk

UMKMHub adalah marketplace multi-toko tempat pelaku UMKM membuka toko dan menjual produk, serta pembeli menjelajah dan memesan produk dari banyak UMKM dalam satu platform (konsep serupa Tokopedia, skala lebih kecil). Pembelian pada versi ini **hanya simulasi**: tidak ada payment gateway dan tidak ada uang sungguhan yang berpindah.

**Perubahan utama dari v1:**

| Aspek | v1 (UMKMHub REST API) | v2 (PRD ini) |
| --- | --- | --- |
| Model bisnis | Backend tunggal untuk satu pemilik toko | Marketplace multi-toko: banyak UMKM, satu platform |
| Peran | Satu jenis admin | Superadmin (pendiri), Admin UMKM (pemilik toko), User (pembeli), Pengunjung |
| Antarmuka | Headless API saja | Web aplikasi dengan landing page dan dashboard per peran, di atas API |
| Pembelian | Checkout oleh admin | Pembeli checkout sendiri dengan pembayaran simulasi |
| Onboarding UMKM | Tidak ada | Pendaftaran UMKM, diverifikasi Superadmin |

**Yang dipertahankan dari v1:** arsitektur berlapis Laravel 11, prefix `/api/v1` (v2 menambah endpoint baru), Sanctum, kontrak OpenAPI, Optimistic Concurrency Control pada stok, checkout atomik dengan row-level lock, validasi Form Request, dan penanganan error global.

### Latar belakang masalah

1. UMKM menjual lewat banyak kanal (WhatsApp, media sosial, marketplace besar) tanpa etalase milik sendiri yang terpusat.
2. Stok dan harga tidak sinkron antar kanal sehingga terjadi overselling.
3. Marketplace besar membuat UMKM kecil sulit terlihat; pembeli tidak punya satu tempat khusus untuk menemukan UMKM lokal.
4. Pesanan tidak tercatat terstruktur sehingga riwayat dan status pesanan sulit dilacak.

## 2. Tujuan dan Metrik Keberhasilan

Tujuan v2: dalam satu platform, UMKM dapat mendaftar dan berjualan, dan pembeli dapat menemukan serta memesan produk dengan alur simulasi yang utuh dari awal sampai akhir.

**Tujuan produk**

1. Memberi UMKM etalase digital sendiri yang dikelola mandiri lewat dashboard.
2. Memberi pembeli satu tempat untuk menemukan UMKM dan produknya.
3. Memberi pendiri (Superadmin) kendali atas siapa yang bergabung dan pemantauan seluruh platform.
4. Mendemonstrasikan alur transaksi marketplace lengkap tanpa payment gateway, sehingga siap diganti pembayaran nyata di versi berikutnya.

**Metrik keberhasilan (target awal untuk tahap simulasi, dapat disesuaikan)**

| Metrik | Target | Cara ukur |
| --- | --- | --- |
| UMKM terdaftar dan disetujui | 10 toko demo | Jumlah toko berstatus `approved` |
| Produk aktif | 100 produk | Jumlah produk berstatus aktif |
| Alur belanja selesai tanpa error | 100% skenario uji lulus | Automated feature test + Postman collection |
| Overselling pada uji konkurensi | 0 kasus | Uji dua checkout bersamaan pada stok terakhir |
| Waktu muat halaman katalog | di bawah 2 detik (jaringan lokal) | Pengukuran browser |
| Pendaftaran UMKM sampai terverifikasi | di bawah 1 hari kerja | Selisih waktu `submitted_at` dan `reviewed_at` |

## 3. Ruang Lingkup dan Batasan

Batasan utama: **tidak ada payment gateway**. Seluruh pembayaran adalah simulasi, dan antarmuka harus menyatakan hal itu dengan jelas kepada pengguna.

**Dalam lingkup (v2)**

- Landing page dengan dua pilihan: lihat UMKM dan daftarkan UMKM Anda.
- Pendaftaran UMKM, verifikasi oleh Superadmin, dan dashboard Admin UMKM.
- Katalog produk dan halaman toko publik, dengan pencarian, filter, dan pengurutan.
- Registrasi dan login pembeli, keranjang, checkout, dan pembayaran simulasi.
- Manajemen pesanan oleh Admin UMKM dan riwayat pesanan oleh pembeli.
- Manajemen stok dengan OCC dan checkout atomik (dari v1).
- Dashboard Superadmin: kelola UMKM, pengguna, kategori, dan ringkasan platform.

**Di luar lingkup (v2)**

- Payment gateway, dompet digital, rekening bersama (escrow), dan pencairan dana.
- Integrasi ekspedisi dan perhitungan ongkir nyata (ongkir diisi nilai tetap simulasi).
- Chat penjual-pembeli, ulasan dan rating, voucher dan promo.
- Aplikasi mobile native dan notifikasi email/WhatsApp nyata.
- Pajak, invoice resmi, dan laporan keuangan.

**Aturan simulasi pembelian**

- Tidak ada data kartu, rekening, atau nominal uang nyata yang diminta atau disimpan.
- Setiap halaman checkout dan pembayaran menampilkan label "MODE SIMULASI".
- Hasil pembayaran dipilih lewat tombol simulasi (berhasil, gagal, atau kedaluwarsa), bukan lewat pihak ketiga.
- Rincian di bagian 7.

## 4. Peran dan Persona

Ada empat peran. Akun UMKM dan pembeli dipisahkan agar hak akses jelas.

| Peran | Siapa | Cara mendapat akun | Kemampuan utama |
| --- | --- | --- | --- |
| Pengunjung | Siapa saja tanpa login | Tanpa akun | Melihat landing, daftar UMKM, halaman toko, katalog, dan detail produk |
| User (Pembeli) | Calon pembeli | Registrasi mandiri | Keranjang, checkout simulasi, riwayat dan status pesanan, profil dan alamat |
| Admin UMKM | Pemilik atau pengelola satu toko | Mendaftarkan UMKM; aktif setelah disetujui Superadmin | Kelola profil toko, produk, stok, dan pesanan **hanya milik tokonya sendiri** |
| Superadmin | Pendiri UMKMHub | Dibuat lewat seeder, tidak bisa mendaftar sendiri | Setujui atau tolak UMKM, suspend toko, kelola pengguna, kategori global, dan lihat seluruh pesanan serta statistik |

**Persona**

1. **Bu Sari, pemilik toko keripik (Admin UMKM).** Tidak terbiasa dengan teknologi, jualan lewat WhatsApp. Butuh form pendaftaran singkat, dashboard sederhana, dan tombol jelas untuk mengubah stok dan memproses pesanan.
2. **Dimas, mahasiswa (User).** Mencari produk lokal. Butuh pencarian cepat, filter kategori, halaman toko yang meyakinkan, dan proses checkout yang pendek.
3. **Pendiri UMKMHub (Superadmin).** Ingin memastikan hanya UMKM yang layak bergabung, dan memantau jumlah toko, produk, serta pesanan.

**Aturan hak akses penting**

- Admin UMKM tidak boleh melihat atau mengubah data toko lain (isolasi per toko).
- Satu akun Admin UMKM boleh memiliki maksimal 2 toko; data, stok, dan pesanan tiap toko terpisah.
- Superadmin tidak dapat dibuat lewat endpoint publik.

## 5. Alur Pengguna

Setelah membuka UMKMHub, pengguna memilih satu dari dua jalur: melihat UMKM atau mendaftarkan UMKM.

&#91;embedded content: alur pengguna · 2 jalur, 1 keputusan\]

Jalur pembeli berakhir pada pembayaran simulasi lalu pemantauan status pesanan (bagian 7). Pendaftaran UMKM baru aktif setelah Superadmin menyetujui; jika ditolak, pemilik melihat alasan dan dapat mengajukan ulang.

**Alur pendukung**

1. Pengunjung yang menekan Keranjang atau Checkout diarahkan ke login atau registrasi, lalu kembali ke halaman semula.
2. Setelah login, peran menentukan tujuan: Superadmin ke dashboard platform, Admin UMKM ke dashboard toko (atau halaman status bila belum disetujui), User ke beranda.

## 6. Kebutuhan Fungsional

Prioritas: **P0** wajib untuk rilis v2, **P1** sebaiknya ada, **P2** boleh menyusul.

### 6.1 Landing dan Penjelajahan Publik

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-01 | Landing page menampilkan dua aksi utama: "Lihat UMKM" dan "Daftarkan UMKM Anda" | P0 |
| FR-02 | Daftar UMKM berstatus disetujui, dengan pencarian nama dan filter kategori | P0 |
| FR-03 | Halaman toko publik: profil, logo, deskripsi, lokasi (kota), dan daftar produk toko | P0 |
| FR-04 | Katalog produk lintas toko dengan pencarian, filter kategori, pengurutan harga dan nama, dan paginasi (maks 100 per halaman) | P0 |
| FR-05 | Halaman detail produk: foto, harga, stok tersedia, deskripsi, dan tautan ke toko | P0 |
| FR-06 | Produk unggulan dan UMKM terbaru di landing page | P2 |

### 6.2 Autentikasi dan Akun

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-10 | Registrasi User (nama, email, password) dan login dengan token Sanctum | P0 |
| FR-11 | Login terpadu untuk semua peran; respons membawa peran agar frontend mengarahkan ke dashboard yang benar | P0 |
| FR-12 | Logout (cabut token) dan endpoint profil `/me` | P0 |
| FR-13 | User dapat mengelola profil dan beberapa alamat pengiriman | P1 |
| FR-14 | Ubah password dan reset password | P2 |

### 6.3 Onboarding UMKM

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-20 | Form "Daftarkan UMKM": data pemilik (akun), nama toko, deskripsi, kategori usaha, kota, alamat, nomor kontak, dan logo | P0 |
| FR-21 | Status toko: `pending`, `approved`, `rejected`, `suspended` | P0 |
| FR-22 | Admin UMKM yang belum disetujui hanya melihat halaman status pendaftaran, tidak bisa menambah produk | P0 |
| FR-23 | Slug toko unik dan otomatis dari nama toko (contoh: `keripik-bu-sari`) | P1 |
| FR-24 | Pendaftar ditolak dapat melihat alasan penolakan dan mengajukan ulang | P1 |

### 6.4 Dashboard Admin UMKM

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-30 | Ubah profil toko (nama, deskripsi, logo, kontak) | P0 |
| FR-31 | CRUD produk milik toko sendiri (nama, deskripsi, harga, kategori, foto, status aktif/nonaktif) | P0 |
| FR-32 | Kelola stok per produk dengan OCC: kirim `version`, konflik mengembalikan `409` | P0 |
| FR-33 | Daftar pesanan masuk untuk toko, filter berdasarkan status | P0 |
| FR-34 | Ubah status pesanan sesuai siklus di bagian 7 | P0 |
| FR-35 | Peringatan stok menipis (`low_stock`) | P1 |
| FR-36 | Ringkasan dashboard: jumlah produk, pesanan baru, total penjualan simulasi | P1 |

### 6.5 Pembeli: Keranjang dan Pesanan

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-40 | Keranjang: tambah, ubah jumlah, hapus item; keranjang tersimpan per akun | P0 |
| FR-41 | Keranjang dapat memuat produk dari beberapa toko; saat checkout dipecah menjadi satu pesanan per toko | P0 |
| FR-42 | Checkout: pilih alamat, lihat ringkasan, ongkir simulasi tetap, dan total | P0 |
| FR-43 | Pembayaran simulasi (bagian 7) | P0 |
| FR-44 | Riwayat pesanan dan detail pesanan dengan status terkini | P0 |
| FR-45 | Pembeli dapat membatalkan pesanan selama status belum diproses | P1 |

### 6.6 Dashboard Superadmin

| ID | Kebutuhan | Prioritas |
| --- | --- | --- |
| FR-50 | Daftar pendaftaran UMKM dengan aksi setujui atau tolak (alasan wajib saat menolak) | P0 |
| FR-51 | Suspend dan aktifkan kembali toko; produk toko suspend tidak tampil di publik | P0 |
| FR-52 | CRUD kategori global (tidak dapat dihapus jika masih dipakai) | P0 |
| FR-53 | Daftar pengguna dan toko, dengan pencarian dan paginasi | P1 |
| FR-54 | Statistik platform: jumlah toko, produk, pengguna, dan pesanan | P1 |
| FR-55 | Log aktivitas (siapa menyetujui, menolak, atau suspend toko) | P2 |

## 7. Simulasi Pembelian dan Siklus Pesanan

Checkout memotong stok secara nyata di database, tetapi pembayaran hanya disimulasikan oleh tombol di halaman "Pembayaran Simulasi". Tidak ada panggilan ke layanan pembayaran eksternal.

### 7.1 Langkah checkout

1. Pembeli menekan Checkout; server membuat satu pesanan per toko dalam satu transaksi database (`DB::transaction`).
2. Stok setiap produk dikunci dengan `lockForUpdate()` dan dipotong; jika kurang, seluruh transaksi di-rollback dengan `409` dan kode `INSUFFICIENT_STOCK`.
3. Pesanan dibuat berstatus `pending_payment` dengan batas waktu bayar simulasi 15 menit (`expires_at`).
4. Pembeli membuka halaman Pembayaran Simulasi dan memilih salah satu hasil: **Bayar Berhasil**, **Bayar Gagal**, atau **Biarkan Kedaluwarsa**.
5. Berhasil: status `paid`. Gagal atau kedaluwarsa: status `cancelled` dan stok dikembalikan (versi stok naik).
6. Admin UMKM memproses pesanan sampai selesai; pengiriman hanya berupa perubahan status (tanpa ekspedisi nyata).

### 7.2 Status pesanan dan siapa yang boleh mengubahnya

| Status | Arti | Transisi berikutnya | Pelaku |
| --- | --- | --- | --- |
| `pending_payment` | Menunggu pembayaran simulasi | `paid`, `cancelled` | Pembeli (aksi simulasi) atau sistem (kedaluwarsa) |
| `paid` | Pembayaran simulasi berhasil | `processing`, `cancelled` | Admin UMKM; pembeli hanya bisa membatalkan |
| `processing` | Pesanan sedang disiapkan | `shipped` | Admin UMKM |
| `shipped` | Dikirim (simulasi) | `completed` | Admin UMKM; pembeli dapat menandai diterima |
| `completed` | Selesai | tidak ada | Final |
| `cancelled` | Dibatalkan atau gagal bayar; stok dikembalikan | tidak ada | Final |

Transisi di luar tabel ditolak dengan `422` dan kode `INVALID_STATUS_TRANSITION`.

### 7.3 Aturan simulasi

- Tabel `payments` mencatat percobaan bayar: `order_id`, `method` (selalu `simulation`), `status` (`pending`, `success`, `failed`, `expired`), dan `paid_at`.
- Ongkir simulasi tetap Rp10.000 per pesanan (nilai konfigurasi, bukan hitungan ekspedisi).
- Total pesanan = jumlah harga item + ongkir; harga item disalin ke `order_items` saat checkout agar perubahan harga kemudian tidak mengubah riwayat.
- Pembatalan setelah `paid` mengembalikan stok dan mencatat status pembayaran tambahan: `refunded_simulation` (tanpa uang nyata).
- Kedaluwarsa diproses oleh scheduled command Laravel setiap menit.

## 8. Kebutuhan Non-Fungsional

| Kategori | Kebutuhan |
| --- | --- |
| Keamanan | Password di-hash (bcrypt), token Sanctum dengan kemampuan (abilities) per peran, otorisasi lewat Policy per toko, `APP_DEBUG=false` di produksi, rate limiting pada login dan registrasi |
| Validasi | Form Request di semua endpoint; `per_page` maks 100; whitelist kolom `sort`; unggah gambar dibatasi tipe (jpg, png, webp) dan ukuran maks 2 MB |
| Konsistensi data | Checkout atomik dengan row-level lock; OCC pada update stok; stok tidak boleh negatif (constraint database) |
| Kinerja | Katalog dan daftar toko dipaginasi; indeks pada `slug`, `category_id`, `store_id`, `status`; eager loading untuk mencegah N+1 query |
| Keandalan | Semua mutasi bernilai bisnis berjalan dalam transaksi; kedaluwarsa pesanan lewat scheduler |
| Format respons | Envelope `{"data", "links", "meta"}`; error `404`, `405`, `409`, `422` berformat konsisten tanpa kebocoran stack trace |
| Kontrak | Dokumen `docs/openapi.yaml` (OpenAPI 3.1) diperbarui untuk semua endpoint baru; Postman collection diperbarui |
| Pengujian | PHPUnit feature test per modul, termasuk uji isolasi toko, uji hak akses peran, dan uji konkurensi stok; Laravel Pint tanpa pelanggaran |
| Aksesibilitas dan responsif | Tampilan nyaman di ponsel (mobile first) dan desktop; kontras warna memenuhi WCAG AA; semua kontrol bisa diakses keyboard |
| Bahasa | Antarmuka Bahasa Indonesia; kode dan nama endpoint Bahasa Inggris |
| Kompatibilitas | Basis data utama MySQL 8 (pengembangan lokal dan produksi) agar row-level lock dan constraint stok berperilaku sama di semua lingkungan. SQLite in-memory hanya untuk tes PHPUnit biasa; uji konkurensi stok dijalankan di MySQL |

**Publikasi.** Proyek dipakai untuk UTS dan akan dipublikasikan, sehingga:

1. Kontrak OpenAPI dan Postman collection harus sinkron dengan implementasi.
2. Deployment ke hosting dengan HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, dan MySQL; data demo lewat `php artisan migrate --seed`.
3. Akun Superadmin demo dan beberapa toko demo disiapkan; kata sandi demo diganti sebelum rilis publik.
4. Label "MODE SIMULASI" tampil jelas di situs publik agar pengunjung tidak mengira transaksi nyata.
5. Rate limiting aktif pada login, registrasi, dan pendaftaran UMKM.

## 9. Desain UI/UX

**Status:** referensi "Chasus" (shot Dribbble AI-Powered E-Commerce Marketplace UI/UX) dan palet warna sudah diterima; analisisnya ada di 9.1, token desain di 9.2, spesifikasi halaman di 9.3; prinsip di bawah berlaku sebagai dasar perbaikan desain.

Panduan desain lengkap (token, komponen, wireframe, dashboard Admin UMKM dan Superadmin) ada di berkas terpisah `design-umkmhub.md`; bagian ini merangkumnya.

**Prinsip desain**

1. Mobile first: kebanyakan pembeli dan pemilik UMKM memakai ponsel.
2. Label "MODE SIMULASI" selalu terlihat di alur checkout dan pembayaran.
3. Tiap UMKM punya identitas: logo, warna aksen, dan deskripsi yang tampil di halaman tokonya.
4. Dashboard Admin UMKM dibuat sesederhana mungkin: satu tugas utama per layar, tombol besar, bahasa sehari-hari.
5. Status kosong, memuat, dan galat dirancang eksplisit (tidak ada layar kosong tanpa penjelasan).
6. Konsistensi komponen: kartu produk, kartu toko, tabel, form, dan badge status dipakai ulang di semua peran.

**Daftar halaman**

| Area | Halaman | Peran |
| --- | --- | --- |
| Publik | Landing, daftar UMKM, halaman toko, katalog, detail produk, login, registrasi | Pengunjung |
| Pembeli | Keranjang, checkout, pembayaran simulasi, riwayat pesanan, detail pesanan, profil dan alamat | User |
| Admin UMKM | Daftarkan UMKM, status pendaftaran, dashboard, produk, stok, pesanan, profil toko | Admin UMKM |
| Superadmin | Dashboard, persetujuan UMKM, kelola toko, kelola pengguna, kategori, statistik | Superadmin |

**Langkah lanjutan**

- [ ] Analisis referensi: selesai, lihat 9.1.
- [ ] Tetapkan design token: selesai, lihat 9.2.
- [ ] Susun wireframe halaman prioritas P0, lalu mockup beresolusi tinggi.
- [ ] Uji kegunaan singkat dengan 3 sampai 5 calon pengguna.

### 9.1 Analisis Referensi (Chasus)

Referensi memuat beranda hasil pencarian, detail produk, keranjang, checkout tiga langkah, konfirmasi pesanan, dan tampilan mobile. Strukturnya bagus untuk marketplace; yang perlu diubah adalah fitur di luar lingkup v2 (AI Assistant, ulasan, wishlist, pembayaran nyata) dan identitas UMKM yang belum ada.

| Elemen referensi | Keputusan | Adaptasi untuk UMKMHub |
| --- | --- | --- |
| Navbar: logo, menu, keranjang dengan badge, tombol Masuk (outline) dan Daftar (solid) | Pertahankan | Menu: Beranda, UMKM, Produk, Kategori. Tombol "Daftarkan UMKM" menonjol bagi pengunjung |
| Menu dan kartu "AI Assistant" | Hapus | Di luar lingkup v2; ruangnya diganti blok "UMKM pilihan" |
| Sidebar kategori dengan ikon | Ubah | Kategori dari database, tampil sebagai chip dan filter, bukan 12 item tetap |
| Bilah pencarian dengan chip "Results for" yang bisa dibaca ulang | Pertahankan | Chip "Hasil untuk: ..." yang bisa dihapus, memakai `?search=` |
| Kartu produk: foto, nama, harga, tombol keranjang merah | Pertahankan | Pengganti deskripsi lorem: nama toko dan kota. Harga dalam Rupiah |
| Harga coret, ikon hati (wishlist), bintang dan jumlah ulasan | Hapus atau ganti | Diskon, wishlist, dan ulasan di luar lingkup. Ganti dengan "Terjual N" dari pesanan selesai (P1) |
| Filter kanan: gender, warna, harga, bahan, merek | Ubah | Filter: kategori, rentang harga, kota toko, hanya stok tersedia; tombol "Hapus semua". Drawer di mobile |
| Detail produk: galeri, aksordeon rincian, panel "You Order", Buy Now dan Add to Cart | Pertahankan | Panel pesanan tetap terlihat; tampilkan stok tersedia |
| Blok detail penjual (reputasi, pengiriman, kontak) | Pertahankan sebagai inti | Jadi kartu toko UMKM: logo, nama, kota, jumlah produk, tautan "Kunjungi toko" (tanpa chat) |
| Checkout tiga langkah (Alamat, Pembayaran, Tinjau) dengan tombol Edit di tiap bagian | Pertahankan | Langkah Pembayaran menjadi panel simulasi |
| Metode bayar nyata (kartu, PayPal, USDT, Apple Pay) dan layar "Processing payment" | Ganti | Tanpa input kartu. Panel simulasi dengan tiga tombol hasil dan label "MODE SIMULASI" |
| Konfirmasi "Order Placed" dengan ID pesanan dan jendela ubah 30 menit | Pertahankan, ubah aturan | Kode pesanan dan status; batas bayar simulasi 15 menit; pembatalan selama belum diproses |
| Navigasi bawah mobile dan kartu produk dua kolom | Pertahankan | Beranda, UMKM, Keranjang, Akun |
| Latar foto gunung, efek kaca, gradasi ungu, aksen merah terang | Tinggalkan | Latar terang polos dengan kartu bergaris halus; warna mengikuti palet di 9.2 |
| Teks abu sangat kecil (sekitar 10 sampai 11 px) | Perbaiki | Teks isi minimal 14 px, kontras memenuhi WCAG AA |

### 9.2 Design Token

Palet berasal dari lima warna yang Anda kirim; warna turunan (putih, latar, teks utama, nada lembut, hijau status) adalah usulan saya. Angka kontras di bawah perkiraan terhadap latar putih, dan perlu diverifikasi dengan pemeriksa kontras sebelum final.

| Warna | Hex | Peran | Catatan kontras |
| --- | --- | --- | --- |
| Red Oxide | `#5E1C23` | Aksi utama: tombol utama, tautan aktif, badge keranjang | Sekitar 12:1; teks putih di atasnya aman |
| Volcanic Island | `#605246` | Teks sekunder dan ikon gelap | Sekitar 7,5:1 |
| Weather Board | `#A1947F` | Aksen hangat: badge "UMKM", pembatas, ilustrasi kosong | Sekitar 3:1; jangan untuk teks kecil |
| Tsunami | `#849AAD` | Garis aktif, ikon besar, grafik | Sekitar 2,9:1; jangan untuk teks |
| Cloudy Valley | `#B1C7D4` | Latar bagian, chip terpilih, hover baris | Hanya sebagai latar; teks di atasnya memakai Red Oxide |
| Turunan (usulan) | `#FFFFFF` kartu, `#F4F7F9` latar halaman, `#2B2420` teks utama, `#F3E9EA` nada lembut Red Oxide, `#EFEBE4` nada lembut Weather Board, `#2F6F4E` hijau status selesai | Dasar tampilan dan status | Teks utama di atas putih jauh di atas 7:1 |

```js
// tailwind.config.js
export default {
  content: ['./resources/**/*.blade.php', './resources/**/*.js'],
  theme: {
    extend: {
      colors: {
        brand: { DEFAULT: '#5E1C23', soft: '#F3E9EA' },            // Red Oxide
        ink:   { DEFAULT: '#2B2420', muted: '#605246' },            // muted = Volcanic Island
        mist:  { DEFAULT: '#B1C7D4', deep: '#849AAD', canvas: '#F4F7F9' }, // Cloudy Valley, Tsunami
        sand:  { DEFAULT: '#A1947F', soft: '#EFEBE4' },             // Weather Board
        ok:    '#2F6F4E',
      },
      fontFamily: { sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'] },
      borderRadius: { card: '16px', control: '12px' },
    },
  },
};
```

**Aturan visual**

- Tipografi: Plus Jakarta Sans (usulan; mirip karakter sans bulat pada referensi dan nyaman untuk Bahasa Indonesia). Isi 16 px, keterangan 14 px, harga 20 sampai 24 px semi-bold.
- Bentuk: kartu `rounded-card` dengan garis `mist` 1 px dan bayangan sangat tipis; tombol `rounded-control`. Tanpa efek kaca dan gradasi agar ringan dan terbaca.
- Tombol utama berlatar `brand` dengan teks putih; tombol sekunder berupa garis `mist-deep` dengan teks `ink`.
- Status pesanan memakai badge **berlabel teks** (warna bukan satu-satunya penanda): `pending_payment` sand, `paid` mist, `processing` mist deep, `shipped` ink muted, `completed` ok, `cancelled` brand soft.
- Grid produk 4 kolom (layar lebar), 3 (tablet), 2 (ponsel), seperti pada referensi.
- Komponen Blade yang dipakai ulang: `x-product-card`, `x-store-card`, `x-status-badge`, `x-stepper`, `x-simulation-banner`, `x-empty-state`.

### 9.3 Spesifikasi Halaman

| Halaman | Komponen utama | Catatan |
| --- | --- | --- |
| Landing | Hero, dua tombol setara "Lihat UMKM" dan "Daftarkan UMKM Anda", UMKM terbaru, produk pilihan, cara kerja 3 langkah | Tidak ada di referensi; footer memuat catatan mode simulasi |
| Daftar UMKM | Grid kartu toko (logo, nama, kota, kategori), pencarian, filter kategori | Tidak ada di referensi |
| Halaman toko | Banner dan logo, deskripsi, kota, kontak, grid produk toko | Tidak ada di referensi; identitas UMKM |
| Katalog | Pencarian dengan chip hasil, filter, grid produk, urutan, paginasi | Mengikuti halaman hasil pada referensi |
| Detail produk | Galeri, harga, stok, rincian aksordeon, panel pesanan, kartu toko | Mengikuti referensi tanpa ulasan |
| Keranjang | Item dikelompokkan per toko, stepper jumlah, ringkasan dan total per toko | Dikelompokkan karena pesanan dipecah per toko |
| Checkout | Stepper Alamat, Pembayaran simulasi, Tinjau; tombol Ubah per bagian | Mengikuti referensi |
| Pembayaran simulasi | Banner "MODE SIMULASI", ringkasan, tiga tombol hasil, hitung mundur 15 menit | Pengganti layar pembayaran nyata |
| Konfirmasi dan riwayat pesanan | Kode pesanan, badge status, tombol lihat pesanan dan lanjut belanja, filter status | Konfirmasi mengikuti referensi |
| Pendaftaran UMKM | Form bertahap (akun, data toko, logo) dan layar status pendaftaran | Tidak ada di referensi |
| Dashboard Admin UMKM | Kartu ringkasan, tabel produk dan stok (pesan khusus saat konflik versi), tabel pesanan dengan aksi ubah status, pemilih toko (maksimal 2) | Tidak ada di referensi; memakai komponen yang sama |
| Dashboard Superadmin | Antrean persetujuan, tabel toko dan pengguna, statistik, kategori | Tidak ada di referensi |

## 10. Arsitektur, Model Data, dan Hak Akses

**Arsitektur.** Backend tetap Laravel 11 dengan arsitektur berlapis dari v1 (Routes, Form Request, Controller tipis, Service, Eloquent Model, API Resource, penanganan exception global). Frontend memakai Blade dan Tailwind CSS dalam proyek Laravel yang sama (monolit); REST API tetap tersedia untuk klien eksternal dan pengujian Postman. Penambahan: **Policy** untuk otorisasi per toko, **scheduled command** untuk kedaluwarsa pesanan, dan **middleware** pengecekan peran dan status toko.

**Teknologi:** PHP 8.3, Laravel 11, Blade, Tailwind CSS, Sanctum, MySQL 8 (nama database umkmhub; SQLite in-memory hanya untuk tes), PHPUnit 11, Laravel Pint, OpenAPI 3.1, Postman v2.1.

**Konfigurasi basis data** (berkas `.env`, tidak ikut di-commit):

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=umkmhub
DB_USERNAME=root
DB_PASSWORD=090123
```

Setelah database kosong `umkmhub` dibuat, jalankan `php artisan migrate:fresh --seed`. Data v1 hanya data demo dari seeder sehingga tidak ada data yang dipindahkan. Kolom uang memakai `decimal` atau bilangan bulat (bukan `float`), dan stok diberi constraint `CHECK (quantity >= 0)`. Di produksi, kata sandi database harus berbeda dari kata sandi lokal dan disimpan hanya di environment server.

### 10.1 Entitas utama

| Tabel | Kolom penting | Catatan |
| --- | --- | --- |
| `users` | id, name, email, password, `role` (`superadmin`, `umkm_admin`, `customer`) | Perluasan tabel akun v1 dengan kolom peran |
| `stores` | id, owner\_id, name, `slug`, description, logo\_path, city, address, phone, `status`, rejection\_reason, submitted\_at, reviewed\_at, reviewed\_by | Satu pemilik maksimal 2 toko (dibatasi validasi) |
| `categories` | id, name, description | Global, dikelola Superadmin |
| `products` | id, `store_id`, category\_id, name, description, price, image\_path, is\_active | Baru: `store_id` |
| `stocks` | id, product\_id, quantity, `version` | OCC dari v1 |
| `addresses` | id, user\_id, recipient, phone, address, city, is\_default | Alamat pengiriman pembeli |
| `carts`, `cart_items` | cart: user\_id; item: cart\_id, product\_id, quantity | Satu keranjang aktif per user |
| `orders` | id, code, user\_id, `store_id`, status, subtotal, shipping\_cost, total, address snapshot, `expires_at` | Satu pesanan per toko |
| `order_items` | id, order\_id, product\_id, product\_name, unit\_price, quantity | Menyalin nama dan harga saat checkout |
| `payments` | id, order\_id, method (`simulation`), status, paid\_at | Catatan pembayaran simulasi |

Logika `customers` pada v1 digabung ke `users` dengan peran `customer`.

### 10.2 Matriks hak akses

| Aksi | Pengunjung | User | Admin UMKM | Superadmin |
| --- | --- | --- | --- | --- |
| Lihat toko, katalog, detail produk | Ya | Ya | Ya | Ya |
| Daftarkan UMKM | Ya | Ya, dengan akun baru | Tidak | Tidak |
| Keranjang, checkout, bayar simulasi | Tidak | Ya | Tidak | Tidak |
| Lihat pesanan | Tidak | Milik sendiri | Pesanan tokonya | Semua |
| CRUD produk dan stok | Tidak | Tidak | Tokonya saja | Semua |
| Setujui, tolak, atau suspend toko | Tidak | Tidak | Tidak | Ya |
| Kelola kategori dan pengguna | Tidak | Tidak | Tidak | Ya |

## 11. Daftar Endpoint API v2

Semua endpoint di bawah prefix `/api/v1` dan berformat JSON. Endpoint v1 yang dipertahankan: auth, categories, products (dengan penyesuaian), stocks. Kolom **Akses**: P = publik, U = User, A = Admin UMKM, S = Superadmin.

| Modul | Method | URI | Akses | Fungsi |
| --- | --- | --- | --- | --- |
| Auth | POST | `/auth/register` | P | Registrasi User |
| Auth | POST | `/auth/login` | P | Login semua peran, mengembalikan token dan peran |
| Auth | POST | `/auth/logout` | Login | Cabut token |
| Auth | GET | `/me` | Login | Profil akun aktif |
| Pendaftaran UMKM | POST | `/store-applications` | P | Daftar UMKM sekaligus akun Admin UMKM (`201`) |
| Pendaftaran UMKM | GET | `/my-store` | A | Lihat toko dan status persetujuan sendiri |
| Pendaftaran UMKM | PUT | `/my-store` | A | Ubah profil toko |
| Toko publik | GET | `/stores` | P | Daftar toko `approved`, filter `?search=`, `?category_id=` |
| Toko publik | GET | `/stores/{slug}` | P | Detail toko |
| Toko publik | GET | `/stores/{slug}/products` | P | Produk milik toko |
| Kategori | GET | `/categories` | P | Daftar kategori |
| Kategori | POST, PUT, DELETE | `/categories`, `/categories/{id}` | S | Kelola kategori |
| Produk | GET | `/products` | P | Katalog lintas toko: `?search=`, `?category_id=`, `?store=`, `?sort=` |
| Produk | GET | `/products/{id}` | P | Detail produk |
| Produk | POST | `/my-store/products` | A | Tambah produk (`201` + header `Location`) |
| Produk | PUT, DELETE | `/my-store/products/{id}` | A | Ubah atau hapus produk toko sendiri |
| Stok | GET | `/my-store/stocks` | A | Monitoring stok, filter `?low_stock=true` |
| Stok | PATCH | `/my-store/stocks/{id}` | A | Ubah stok dengan OCC (`409` jika versi usang) |
| Keranjang | GET | `/cart` | U | Lihat keranjang |
| Keranjang | POST | `/cart/items` | U | Tambah item |
| Keranjang | PATCH, DELETE | `/cart/items/{id}` | U | Ubah jumlah atau hapus item |
| Alamat | GET, POST | `/addresses` | U | Daftar dan tambah alamat |
| Alamat | PUT, DELETE | `/addresses/{id}` | U | Ubah atau hapus alamat |
| Pesanan | POST | `/orders/checkout` | U | Checkout atomik, menghasilkan satu atau lebih pesanan `pending_payment` |
| Pesanan | GET | `/orders` | U | Riwayat pesanan sendiri |
| Pesanan | GET | `/orders/{id}` | U, A, S | Detail pesanan sesuai hak akses |
| Pesanan | POST | `/orders/{id}/cancel` | U | Batalkan selama belum diproses |
| Pembayaran simulasi | POST | `/orders/{id}/simulate-payment` | U | Body `{"result": "success" \| "failed" \| "expired"}` |
| Pesanan toko | GET | `/my-store/orders` | A | Pesanan masuk toko, filter `?status=` |
| Pesanan toko | PATCH | `/my-store/orders/{id}/status` | A | Ubah status sesuai siklus |
| Superadmin | GET | `/admin/store-applications` | S | Daftar pendaftaran, filter `?status=` |
| Superadmin | POST | `/admin/stores/{id}/approve` | S | Setujui toko |
| Superadmin | POST | `/admin/stores/{id}/reject` | S | Tolak toko (alasan wajib) |
| Superadmin | POST | `/admin/stores/{id}/suspend`, `/activate` | S | Suspend atau aktifkan toko |
| Superadmin | GET | `/admin/users` | S | Daftar pengguna |
| Superadmin | GET | `/admin/stats` | S | Statistik platform |

**Kode error khusus:** `INSUFFICIENT_STOCK` (409), `STOK_VERSION_MISMATCH` (409), `INVALID_STATUS_TRANSITION` (422), `STORE_NOT_APPROVED` (403), `FORBIDDEN_STORE_ACCESS` (403).

**Catatan multi toko:** karena satu akun dapat memiliki maksimal 2 toko, semua endpoint `/my-store/...` menjadi `/my-stores/{store_id}/...` (contoh: `/my-stores/{store_id}/products`), dan `/my-store` menjadi `/my-stores` (daftar toko milik akun). Pembuatan toko ketiga ditolak dengan `422` dan kode `STORE_LIMIT_REACHED`. Admin hanya boleh mengakses `store_id` miliknya, selain itu `403` `FORBIDDEN_STORE_ACCESS`.

**Endpoint statistik dashboard (tambahan):** `GET /my-stores/{store_id}/stats?period=today|7d|month` (akses A) mengembalikan total penjualan simulasi, jumlah pesanan baru (`paid` belum diproses), jumlah produk aktif, jumlah stok menipis, dan deret harian untuk dua grafik. Ambang stok menipis bernilai awal 5 dan dapat dikonfigurasi. Statistik platform untuk Superadmin tetap di `/admin/stats`.

## 12. Strategi Migrasi dari v1

Proyek v1 dikembangkan lebih lanjut pada basis kode yang sama, bukan ditulis ulang, agar arsitektur berlapis, pengujian, dan kontrak yang sudah lulus tetap terpakai.

| Komponen v1 | Tindakan di v2 |
| --- | --- |
| Autentikasi Sanctum dan `/me` | Pertahankan; tambah kolom `role` pada `users` dan endpoint register |
| `categories` | Pertahankan; hak ubah dibatasi untuk Superadmin |
| `products` dan `stocks` | Tambah `store_id`; endpoint kelola dipindah ke `/my-store/...` dan katalog publik tetap di `/products` |
| `customers` | Gabungkan ke `users` (peran `customer`) dan tabel `addresses` |
| `orders` dan `OrderService::checkout()` | Ubah dari checkout oleh admin menjadi checkout oleh pembeli, pecah per toko, tambah `payments` dan `expires_at` |
| Seeder admin demo | Ganti menjadi seeder Superadmin, beberapa toko demo, dan produk demo |
| OpenAPI, Postman, PHPUnit | Perbarui untuk seluruh endpoint baru; pertahankan skenario OCC dan checkout atomik |

**Frontend baru:** halaman publik, pembeli, dan dashboard dibangun dengan Blade dan Tailwind CSS di proyek yang sama. Controller halaman memanggil Service yang sama dengan controller API, sehingga logika bisnis (checkout atomik, OCC, transisi status) hanya ada satu dan tidak diduplikasi.

**Langkah teknis:** buat migrasi tambahan (bukan menghapus tabel lama), sediakan skrip pemindahan data demo, dan jalankan ulang seluruh test v1 yang masih relevan sebelum menambah fitur baru.

**Kompatibilitas:** karena v1 belum dipakai klien produksi, perubahan yang memutus kompatibilitas (misal URI stok) dapat dilakukan langsung tanpa versi `/v2`. Jika ada klien yang masih memakai v1, pertimbangkan prefix `/api/v2`.

## 13. Roadmap dan Milestone

Pengembangan berjalan dalam lima fase berurutan, kira-kira sembilan minggu; backend didahulukan, dan antarmuka menyesuaikan referensi desain begitu diterima.

&#91;embedded content: roadmap · 5 fase, 5 gerbang\]

Fase berikutnya baru dimulai setelah gerbang fase sebelumnya lulus. Fitur P1 dan P2 dikerjakan setelah seluruh P0 pada fase terkait selesai.

## 14. Risiko, Asumsi, dan Pertanyaan Terbuka

**Risiko**

| Risiko | Dampak | Mitigasi |
| --- | --- | --- |
| Pengguna mengira pembayaran sungguhan | Salah paham, keluhan | Label "MODE SIMULASI" di checkout dan pembayaran; tanpa input data kartu atau rekening |
| Admin UMKM mengakses data toko lain | Kebocoran data | Policy per toko, uji otomatis isolasi data, filter `store_id` di setiap query |
| Overselling saat checkout bersamaan | Stok negatif | Row-level lock, constraint database, uji konkurensi |
| Stok tertahan oleh pesanan yang tidak dibayar | Produk tampak habis | Kedaluwarsa 15 menit dan pengembalian stok otomatis |
| Ruang lingkup membesar (ulasan, chat, voucher) | Jadwal molor | Disiplin pada daftar di luar lingkup; fitur baru masuk backlog v3 |
| Pendaftaran UMKM palsu atau spam | Katalog tercemar | Verifikasi Superadmin, rate limiting, kemampuan suspend |
| Referensi desain terlambat | Pengerjaan frontend tertunda | Kerjakan backend dulu; desain disesuaikan setelah referensi datang |

**Asumsi**

- Satu akun Admin UMKM memiliki maksimal 2 toko.
- Ongkir simulasi tetap Rp10.000 dan mata uang Rupiah.
- Foto produk disimpan di penyimpanan lokal Laravel pada tahap ini.
- Frontend memakai Blade dan Tailwind CSS di proyek Laravel yang sama; REST API tetap tersedia untuk klien eksternal dan pengujian Postman.

**Pertanyaan terbuka**

- [ ] Terjawab: Laravel (Blade) dengan Tailwind CSS untuk frontend.
- [ ] Terjawab: ya, satu keranjang boleh berisi banyak toko dan dipecah per toko saat checkout.
- [ ] Terjawab: satu akun maksimal 2 toko.
- [ ] Terjawab: pembeli wajib punya akun dan login sebelum checkout; verifikasi email belum diwajibkan.
- [ ] Terjawab: untuk UTS kuliah, tetapi akan dipublikasikan (lihat paragraf Publikasi di bagian 8).
- [ ] Terjawab: referensi Chasus, referensi dashboard Shopy, dan palet warna sudah diterima (bagian 9 dan berkas design-umkmhub.md).
