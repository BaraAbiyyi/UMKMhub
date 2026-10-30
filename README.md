# UMKMHub REST API v1

**Proyek:** UMKMHub API (Integrasi Katalog, Stok, dan Transaksi Pesanan UMKM)  
**Mata Kuliah:** Pemrograman Web Service — Minggu 6 (*Implementasi Laravel REST API & Layered Structure*)  
**Dosen Pengampu:** Zaenul Arif, S.Kom., M.Kom. (Universitas Harkat Negeri)  
**Status Kepatuhan Kontrak:** **100% COMPLIANT — Sangat Baik / Skor 4**

REST API profesional berbasis Laravel 13 dengan implementasi **Layered Architecture (Struktur Berlapis)**, **Optimistic Concurrency Control (OCC)**, **Atomic Database Transactions**, **Sanctum Bearer Authentication**, dan kepatuhan penuh terhadap spesifikasi **OpenAPI 3.1.0**.

---

## 1. Prasyarat Sistem

Sebelum menjalankan aplikasi, pastikan sistem Anda memenuhi prasyarat berikut:
- **PHP:** Versi 8.3 atau lebih baru. dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`.
- **Composer:** Versi 2.x
- **Database:** SQLite (default lokal terisolasi) atau MySQL 8.0+
- **HTTP Client:** Postman v10+ atau Curl / VS Code REST Client

---

## 2. Arsitektur Layered (Struktur Berlapis)

Aplikasi UMKMHub v1 menerapkan pemisahan tanggung jawab (*Separation of Concerns*) secara ketat sesuai standar Modul Ajar 06:

```
[ HTTP Request ]
       │
       ▼
[ Routes (routes/api.php) ]  ──> Memetakan HTTP Verb, URI prefix /api/v1, & Middleware Sanctum
       │
       ▼
[ Form Requests (app/Http/Requests/*) ]  ──> Validasi ketat input & query parameters (422 response)
       │
       ▼
[ Controllers (app/Http/Controllers/Api/V1/*) ]  ──> Thin controller (orkestrasi data valid & service)
       │
       ▼
[ Services (app/Services/*) ]  ──> Business logic, DB Transactions, OCC lockForUpdate(), Concurrency
       │
       ▼
[ Eloquent Models (app/Models/*) ]  ──> ORM, skema relasi antar entitas, casts, mass assignment
       │
       ▼
[ API Resources (app/Http/Resources/*) ]  ──> Standarisasi envelope JSON (data, links, meta)
       │
       ▼
[ HTTP Response / Global Exceptions (bootstrap/app.php) ]
```

### Tanggung Jawab Tiap Lapisan:
1. **Routes (`routes/api.php`):** Hanya memetakan URI dan controller class. Tidak berisi *in-memory closures* atau logika bisnis.
2. **Form Requests (`app/Http/Requests`):** Menjamin seluruh parameter (termasuk filter, sorting whitelist, batas paginasi `per_page: max 100`) divalidasi sebelum menyentuh controller. Mengembalikan HTTP 422 jika tidak valid.
3. **Controllers (`app/Http/Controllers/Api/V1`):** *Thin controllers* yang hanya memanggil Service dan membungkus hasil dalam API Resource. Mengatur kode HTTP (`200`, `201` + header `Location`, `204`).
4. **Services (`app/Services`):** Menangani seluruh transaksi basis data, locking (`lockForUpdate()`), mekanisme OCC peredam race condition, serta melempar domain exception (`StockConflictException`).
5. **Eloquent Models (`app/Models`):** Mengelola entitas basis data (`User`, `Category`, `Product`, `Stock`, `Customer`, `Order`, `OrderItem`).
6. **API Resources (`app/Http/Resources`):** Memformat representasi JSON sesuai kontrak OpenAPI, termasuk transformasi pagination links dan meta.
7. **Global Exception Handling (`bootstrap/app.php`):** Menangkap semua kegagalan API, menyaring `NotFoundHttpException` (404), `MethodNotAllowedHttpException` (405), dan error 500 tanpa mengekspos stack trace server (`APP_DEBUG=false`).

---

## 3. Langkah Instalasi & Menjalankan Proyek

### Langkah 1: Clone Repository
```bash
git clone https://github.com/BaraAbiyyi/UMKMhub.git
cd UMKMhub
```

### Langkah 2: Install Dependensi PHP
```bash
composer install
```

### Langkah 3: Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Pastikan pengaturan environment berikut aktif pada `.env`:
```ini
APP_NAME=UMKMHub
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite
# Database SQLite otomatis menggunakan database/database.sqlite
```

Generate application key:
```bash
php artisan key:generate
```

### Langkah 4: Migrasi & Seeding Data Awal
Jalankan migrasi tabel beserta seeder data demo:
```bash
php artisan migrate:fresh --seed
```
Perintah ini akan membuat skema basis data lengkap dan mengisi data awal:
- Akun Administrator Demo
- Kategori Produk (Makanan Ringan, Minuman Herbal, Kerajinan Tangan)
- Katalog Produk UMKM
- Stok Inventori dengan initial OCC `version = 1`
- Data Pelanggan Demo

### Langkah 5: Jalankan Server Lokal
```bash
php artisan serve
```
Aplikasi REST API siap diakses pada URL: `http://127.0.0.1:8000/api/v1`.

---

## 4. Kredensial Akun Demo

Untuk mengakses endpoint privat (manajemen stok, pembuatan pesanan, penambahan produk), gunakan kredensial berikut pada endpoint `POST /api/v1/auth/login`:

- **Email:** `admin@umkmhub.id`
- **Password:** `password123`
- **Role:** `admin`
- **Token Type:** `Bearer <token>` (Personal Access Token Sanctum)

---

## 5. Ringkasan Endpoint API v1

Semua endpoint berada di bawah prefix `/api/v1`.

| Modul | Method | Endpoint | Akses | Deskripsi & Response |
|---|---|---|---|---|
| **Auth** | `POST` | `/api/v1/auth/login` | Publik | Otentikasi admin & perolehan Sanctum Bearer Token (`200 OK`) |
| **Categories** | `GET` | `/api/v1/categories` | Publik | Ambil semua kategori UMKM (`200 OK`) |
| **Categories** | `POST` | `/api/v1/categories` | Admin | Buat kategori baru (`201 Created` + header `Location`) |
| **Categories** | `GET` | `/api/v1/categories/{id}` | Publik | Detail kategori (`200 OK` / `404 Not Found`) |
| **Categories** | `PUT` | `/api/v1/categories/{id}` | Admin | Update kategori (`200 OK`) |
| **Categories** | `DELETE` | `/api/v1/categories/{id}` | Admin | Hapus kategori (`204 No Content` / `409 Conflict`) |
| **Products** | `GET` | `/api/v1/products` | Publik | Katalog produk terpaginasi dengan filter, search, & sort (`200 OK`) |
| **Products** | `POST` | `/api/v1/products` | Admin | Tambah produk baru (`201 Created` + header `Location`) |
| **Products** | `GET` | `/api/v1/products/{id}` | Publik | Detail produk lengkap dengan kategori & stok (`200 OK` / `404`) |
| **Products** | `PUT` | `/api/v1/products/{id}` | Admin | Perbarui data produk (`200 OK`) |
| **Products** | `DELETE` | `/api/v1/products/{id}` | Admin | Hapus produk (`204 No Content` / `409 Conflict`) |
| **Stocks** | `GET` | `/api/v1/stocks` | Admin | Monitoring daftar stok & filter low-stock (`200 OK`) |
| **Stocks** | `GET` | `/api/v1/stocks/{id}` | Admin | Detail stok inventori & versi OCC (`200 OK` / `404`) |
| **Stocks** | `PATCH` | `/api/v1/stocks/{id}` | Admin | Update kuantitas stok dengan Optimistic Locking (`200 OK` / `409 Conflict`) |
| **Customers** | `GET` | `/api/v1/customers` | Admin | Ambil daftar pelanggan terpaginasi (`200 OK`) |
| **Customers** | `POST` | `/api/v1/customers` | Admin | Pendaftaran pelanggan baru (`201 Created` + header `Location`) |
| **Customers** | `GET` | `/api/v1/customers/{id}` | Admin | Detail pelanggan (`200 OK` / `404`) |
| **Customers** | `GET` | `/api/v1/customers/{id}/orders` | Admin | Riwayat pesanan pelanggan terpaginasi (`200 OK`) |
| **Orders** | `GET` | `/api/v1/orders` | Admin | Ambil semua transaksi pesanan (`200 OK`) |
| **Orders** | `POST` | `/api/v1/orders` | Admin | Checkout pesanan baru dengan atomic lock & validasi stok (`201 Created` / `409`) |
| **Orders** | `GET` | `/api/v1/orders/{id}` | Admin | Detail pesanan lengkap dengan items (`200 OK` / `404`) |
| **Orders** | `PATCH` | `/api/v1/orders/{id}/status` | Admin | Pembaruan status siklus pesanan (`200 OK` / `422`) |

---

## 6. Penanganan Error Terpusat & Keamanan

Penanganan pengecualian global dikonfigurasikan pada `bootstrap/app.php` menggunakan pipeline resmi Laravel 11 (`Application::configure()` dan `withExceptions()`):
- **404 Not Found:** `{"message": "Resource tidak ditemukan."}`
- **405 Method Not Allowed:** `{"message": "Method tidak diizinkan."}`
- **422 Unprocessable Content:** Respon validasi standar Laravel (`message` dan `errors`).
- **409 Conflict:** Respon khusus `StockConflictException` (`code: STOK_VERSION_MISMATCH` atau `INSUFFICIENT_STOCK`).
- **500 Server Error:** Respon aman tanpa kebocoran jejak server: `{"message": "Terjadi kesalahan internal server."}`.
- **Perlindungan Data Rahasia:** `APP_DEBUG=false` memastikan jejak tumpukan (*stack trace*), path file lokal, query SQL, dan kredensial server tidak akan pernah bocor ke publik.

---

## 7. Pengujian Otomatis (Automated Tests)

Pengujian fitur (*Feature Tests*) diuji secara komprehensif menggunakan PHPUnit dengan database in-memory:

```bash
php artisan test
```

### Cakupan Pengujian:
1. `tests/Feature/ProductApiTest.php`:
   - Validasi paginasi default, parameter filter, query search, dan whitelist sort.
   - Penolakan parameter di luar batas (`per_page=500` -> 422).
   - CRUD Produk lengkap dengan header `Location` pada HTTP 201.
   - Penanganan 404 ketika ID produk tidak ada di database.
2. `tests/Feature/ProductWeek3ApiTest.php`:
   - Login admin Sanctum (200 OK) dan penolakan kredensial salah (401 Unauthorized).
   - CRUD Kategori dan Pelanggan.
   - Optimistic Concurrency Control (OCC) pada stok: penolakan versi usang dengan kode `409 Conflict`.
   - Transaksi Checkout Pesanan dengan *row-level locking* (`lockForUpdate()`) dan penolakan pesanan saat stok kurang (`409 Conflict`).
   - Riwayat pesanan pelanggan berpaginasi.
   - Verifikasi penanganan error global 404 dan 405 tanpa kebocoran *stack trace*.

**Status Uji:** 20 Test Lolos, 0 Gagal, 114 Assertions (100% Pass Rate).

---

## 8. Dokumentasi Kontrak & Postman Collection

- **Spesifikasi Kontrak OpenAPI 3.1.0:** [`docs/openapi.yaml`](docs/openapi.yaml) (Validasi Redocly CLI: 0 errors, 0 warnings).
- **Postman Collection v2.1:** [`docs/postman/collection.json`](docs/postman/collection.json) (14 skenario pengujian alur positif, negatif, validasi, dan konkurensi).
- **Checklist Kepatuhan Kontrak (Aktivitas 4):** [`docs/postman/contract-compliance.md`](docs/postman/contract-compliance.md).
