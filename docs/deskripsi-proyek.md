# Deskripsi Proyek: UMKMHub REST API (v1)

**Nama Proyek:** UMKMHub REST API  
**Versi:** `v1.0.0`  
**Mata Kuliah:** Pemrograman Web Service  
**Pola Arsitektur:** *Layered Architecture* (Struktur Berlapis)  
**Dokumen Kontrak:** OpenAPI 3.1.0 (`docs/openapi.yaml`)  
**Status Kepatuhan Kontrak:** **100% Compliant (Sangat Baik / Skor 4)**  

---

## 1. Ringkasan Eksekutif (*Executive Summary*)

**UMKMHub** adalah layanan *backend* berbasis **RESTful API** yang berfungsi sebagai pusat integrasi data katalog produk, pemantauan persediaan stok secara *real-time*, pencatatan profil pelanggan, serta pemrosesan transaksi pesanan (*checkout*) bagi pelaku Usaha Mikro, Kecil, dan Menengah (UMKM).

Aplikasi ini dirancang sebagai *headless backend* (tanpa antarmuka visual/monolitik) yang siap dikonsumsi oleh berbagai aplikasi *client* eksternal seperti website toko online e-commerce, aplikasi kasir Point of Sale (POS), aplikasi mobile, maupun sistem pengujian otomatis (*automated API evaluation tools*).

---

## 2. Latar Belakang & Masalah yang Diselesaikan

Pelaku UMKM sering kali memasarkan produknya melalui berbagai saluran secara simultan (WhatsApp, media sosial, marketplace pihak ketiga, dan etalase toko fisik). Pola operasional tanpa backend terpusat ini menimbulkan sejumlah persoalan:

1. **Selisih Stok & Rebutan Barang (*Overselling / Race Condition*):**  
   Barang yang telah terjual di toko fisik masih ditawarkan kepada pembeli di media online karena keterlambatan pembaruan stok manual, sehingga merugikan reputasi toko.
2. **Katalog Tersebar & Inkonsisten:**  
   Ketika terjadi perubahan harga, spesifikasi, atau ketersediaan produk, pemilik toko harus memperbarui data satu per satu di setiap etalase.
3. **Pencatatan Pesanan Tidak Terstruktur:**  
   Ketiadaan pencatatan transaksi yang terintegrasi menyulitkan pemantauan siklus status pesanan (*order lifecycle*) dan penelusuran riwayat belanja pelanggan.

**Solusi:** UMKMHub hadir sebagai **satu sumber kebenaran (*single source of truth*)** yang mengelola data secara konsisten, aman dari konflik data bersamaan, dan memiliki aturan validasi bisnis yang ketat.

---

## 3. Arsitektur Sistem (*Layered Architecture*)

Aplikasi menerapkan pemisahan tanggung jawab (*Separation of Concerns*) secara ketat untuk menjamin skalabilitas, kemudahan pemeliharaan (*maintainability*), dan kemudahan pengujian (*testability*):

```text
[ HTTP Request ] (Client Eksternal / Postman / Web Testing)
       │
       ▼
[ Routes (routes/api.php) ]
       │  ──> Pemetaan HTTP Verb, URI prefix /api/v1
       ▼
[ Form Requests (app/Http/Requests/*) ]
       │  ──> Validasi ketat (Pagination maks 100, Whitelist Sort, Type Safety)
       ▼
[ Controllers (app/Http/Controllers/Api/V1/*) ]
       │  ──> Thin Controller (Orkestrasi request ke service & penentuan status code)
       ▼
[ Services (app/Services/*) ]
       │  ──> Logika bisnis terisolasi, DB Transaction, Row-Level Lock, Validasi OCC
       ▼
[ Eloquent Models (app/Models/*) ]
       │  ──> ORM, relasi database, casts, mass assignment
       ▼
[ API Resources (app/Http/Resources/*) ]
       │  ──> Transformasi envelope JSON standar ({"data": ..., "links": ..., "meta": ...})
       ▼
[ HTTP Response / Global Exceptions (bootstrap/app.php) ]
          ──> Menangkap 404, 405, 422, 409 tanpa kebocoran stack trace
```

---

## 4. Modul & Daftar Endpoint API

Semua endpoint beroperasi di bawah prefix URL `/api/v1` dan selalu bertukar data menggunakan format `application/json`:

| Modul | Method | Endpoint URI | Hak Akses | Deskripsi & Fungsi |
|---|---|---|---|---|
| **Auth** | `POST` | `/api/v1/auth/login` | Publik | Otentikasi admin & perolehan Sanctum Bearer Token (`200 OK`). |
| **Auth** | `POST` | `/api/v1/auth/logout` | Terotentikasi | Pencabutan token sesi aktif. |
| **Auth** | `GET` | `/api/v1/me` | Terotentikasi | Melihat profil admin yang sedang aktif. |
| **Categories** | `GET` | `/api/v1/categories` | Publik | Menampilkan seluruh daftar kategori produk UMKM. |
| **Categories** | `POST` | `/api/v1/categories` | Admin | Menambahkan kategori baru (`201 Created` + header `Location`). |
| **Categories** | `GET` | `/api/v1/categories/{id}` | Publik | Menampilkan detail spesifik suatu kategori. |
| **Categories** | `PUT` | `/api/v1/categories/{id}` | Admin | Memperbarui informasi nama dan deskripsi kategori. |
| **Categories** | `DELETE` | `/api/v1/categories/{id}` | Admin | Menghapus kategori (dilindungi dari penghapusan jika masih memuat produk). |
| **Products** | `GET` | `/api/v1/products` | Publik | Katalog produk terpaginasi (filter `?category_id=`, search `?search=`, sort `?sort=`). |
| **Products** | `POST` | `/api/v1/products` | Admin | Menambahkan produk baru ke katalog (`201 Created` + header `Location`). |
| **Products** | `GET` | `/api/v1/products/{id}` | Publik | Menampilkan detail produk lengkap beserta relasi kategori dan stok. |
| **Products** | `PUT` | `/api/v1/products/{id}` | Admin | Memperbarui data katalog produk. |
| **Products** | `DELETE` | `/api/v1/products/{id}` | Admin | Menghapus produk dari katalog. |
| **Stocks** | `GET` | `/api/v1/stocks` | Admin | Monitoring inventori stok berpaginasi (filter `?low_stock=true`). |
| **Stocks** | `PATCH` | `/api/v1/stocks/{id}` | Admin | Update kuantitas stok sebagian dengan pengecekan versi OCC (`409 Conflict` jika versi usang). |
| **Customers** | `GET` | `/api/v1/customers` | Admin | Menampilkan daftar seluruh pelanggan terdaftar. |
| **Customers** | `POST` | `/api/v1/customers` | Admin | Registrasi data pelanggan baru (`201 Created` + header `Location`). |
| **Customers** | `GET` | `/api/v1/customers/{id}` | Admin | Menampilkan profil pelanggan tertentu. |
| **Customers** | `GET` | `/api/v1/customers/{id}/orders` | Admin | Riwayat seluruh pesanan milik pelanggan tertentu (*nested resource*). |
| **Orders** | `POST` | `/api/v1/orders` | Admin | Checkout pesanan multi-item dengan transaksi atomik dan pengurangan stok otomatis. |
| **Orders** | `GET` | `/api/v1/orders/{id}` | Admin | Menampilkan rincian pesanan lengkap beserta daftar item barangnya. |
| **Orders** | `PATCH` | `/api/v1/orders/{id}/status` | Admin | Pembaruan status siklus pesanan (*pending*, *processing*, *completed*, *cancelled*). |

---

## 5. Fitur Teknis Khusus (*Technical Highlights*)

### A. Optimistic Concurrency Control (OCC) pada Stok
Untuk mencegah *lost update* saat beberapa kasir/admin mengakses barang yang sama:
* Setiap record stok memiliki kolom `version` (dimulai dari `1`).
* Saat client melakukan pembaruan stok via `PATCH /api/v1/stocks/{id}`, client wajib mengirimkan nilai `version` yang dibacanya.
* Jika versi yang dikirim cocok, kuantitas diperbarui dan `version` otomatis dinaikkan (`version + 1`).
* Jika versi tidak cocok (sudah diubah oleh pihak lain), server melempar `StockConflictException` dengan status **`409 Conflict`** (`code: STOK_VERSION_MISMATCH`).

### B. Transaksi Checkout Atomik (*Atomic Transactions*)
Alur checkout pesanan pada `OrderService::checkout()` dirancang dengan prinsip ACID:
* Dibungkus dalam blok `DB::transaction`.
* Stok setiap produk yang dibeli dikunci sementara menggunakan *row-level lock* (`lockForUpdate()`).
* Jika ada kuantitas stok yang kurang, transaksi langsung di-rollback dan server membalas dengan status **`409 Conflict`** (`code: INSUFFICIENT_STOCK`).
* Jika stok mencukupi, stok otomatis terpotong, versi stok bertambah, dan item pesanan tersimpan secara utuh.

### C. Validasi Input Ketat (*Strict Input Validation*)
Menggunakan *Form Request Classes*:
* Membatasi query paginasi `per_page` maksimum **100** item (mencegah eksploitasi beban server / DoS via `per_page=99999`).
* Menyaring parameter pengurutan (*whitelist sorting*) hanya pada atribut yang diizinkan (misal: `price`, `-price`, `name`, `-name`).

### D. Standarisasi Respon & Penanganan Error Global
Dikonfigurasikan melalui pipeline exception Laravel 11 pada `bootstrap/app.php`:
* **Respon Sukses:** Menggunakan envelope standar `{"data": ...}` dan metadata `{"links": ..., "meta": ...}`.
* **404 Not Found:** `{"message": "Resource tidak ditemukan."}`
* **405 Method Not Allowed:** `{"message": "Method tidak diizinkan."}`
* **422 Unprocessable Content:** Respon validasi standar Laravel (`message` dan `errors`).
* **Keamanan:** Parameter `APP_DEBUG=false` menjamin tidak ada kebocoran *stack trace*, query database, maupun direktori server ke publik.

---

## 6. Tumpukan Teknologi (*Tech Stack*)

* **Bahasa Pemrograman:** PHP 8.3 (CLI & ZTS support)
* **Framework:** Laravel 11.x
* **Otentikasi:** Laravel Sanctum (Personal Access Token)
* **Basis Data:** SQLite (Development Default) / Kompatibel dengan MySQL 8.0 & PostgreSQL
* **Standardisasi Kontrak:** OpenAPI Specification 3.1.0
* **Pengujian Otomatis:** PHPUnit 11 (Database in-memory testing)
* **Code Formatter & Linter:** Laravel Pint (Standar PSR-12)
* **HTTP Client Testing:** Postman Collection v2.1

---

## 7. Status Kesiapan & Pengujian (*Verification Status*)

* **Automated Feature Tests (PHPUnit):**  
  Seluruh 20 skenario uji pada `tests/Feature/ProductApiTest.php` dan `tests/Feature/ProductWeek3ApiTest.php` lulus 100% (**20 passed, 114 assertions, 0 failed**).
* **Kepatuhan Kontrak Postman:**  
  14 skenario pengujian alur positif, negatif, validasi, dan konkurensi pada `docs/postman/collection.json` lulus 100% (terdokumentasi pada `docs/postman/contract-compliance.md`).
* **Linter Code Style:**  
  Lolos verifikasi `vendor/bin/pint --test` (0 violations).
* **Database & Seeder:**  
  10 tabel migrasi dan seeder akun admin demo (`admin@umkmhub.id` / `password123`) siap pakai.

---

## 8. Panduan Menjalankan API

1. **Jalankan Server Lokal:**
   ```bash
   php artisan serve
   ```
   API aktif di `http://127.0.0.1:8000/api/v1`.

2. **Koneksi dari Website Penguji Online (Tunneling):**
   ```bash
   ngrok http 8000
   ```
   Gunakan URL publik yang dihasilkan oleh Ngrok (contoh: `https://xxxx.ngrok-free.app/api/v1`) sebagai *base URL* pada website penguji eksternal.

