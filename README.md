# 🛒 UMKMHub API

> **Platform REST API backend untuk ekosistem digital UMKM — mulai dari manajemen toko, katalog produk, keranjang belanja, pemrosesan pesanan, hingga pembayaran simulasi.**

<div align="center">

[![PHP Version](https://img.shields.io/badge/PHP-8.3%2B-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/)
[![Sanctum](https://img.shields.io/badge/Sanctum-4.x-FF2D20?logo=laravel&logoColor=white)](https://laravel.com/docs/sanctum)
[![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?logo=vite&logoColor=white)](https://vitejs.dev/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4.x-06B6D4?logo=tailwindcss&logoColor=white)](https://tailwindcss.com/)
[![Database](https://img.shields.io/badge/SQLite-Default-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)
[![PHPUnit](https://img.shields.io/badge/PHPUnit-12.x-6E5494?logo=php&logoColor=white)](https://phpunit.de/)
[![OpenAPI Spec](https://img.shields.io/badge/OpenAPI-3.1.0-6BA539?logo=openapiinitiative&logoColor=white)](https://www.openapis.org/)
[![License](https://img.shields.io/badge/Lisensi-MIT-blue.svg)](composer.json)

</div>

---

## 📑 Daftar Isi

- [1. Deskripsi Proyek](#1-deskripsi-proyek)
- [2. Prasyarat](#2-prasyarat)
- [3. Instalasi](#3-instalasi)
- [4. Data Awal (Seeder)](#4-data-awal-seeder)
- [5. Menjalankan Aplikasi](#5-menjalankan-aplikasi)
- [6. Dokumentasi API](#6-dokumentasi-api)
- [7. Menjalankan Test](#7-menjalankan-test)
- [8. Struktur Folder](#8-struktur-folder)
- [9. Troubleshooting](#9-troubleshooting)
- [10. Deployment](#10-deployment)
- [11. Kontribusi dan Lisensi](#11-kontribusi-dan-lisensi)
- [Tim Pengembang](#tim-pengembang)

---

## 1. Deskripsi Proyek

**UMKMHub API** adalah layanan *backend* RESTful API yang dirancang untuk mendukung transformasi digital pelaku UMKM (Usaha Mikro, Kecil, dan Menengah). Platform ini memungkinkan pengelola UMKM mendaftarkan dan mengelola toko mereka, sementara pembeli dapat menjelajahi katalog, menambahkan produk ke keranjang, melakukan checkout, dan melacak status pesanan — semua melalui antarmuka API yang terstandarisasi.

### Masalah yang Diselesaikan

1. **Race Condition pada Stok** — Mekanisme *Optimistic Concurrency Control* (OCC) berbasis kolom `version` mencegah *lost update* saat transaksi simultan.
2. **Ketiadaan Kontrak API Terpadu** — Spesifikasi **OpenAPI 3.1.0** berfungsi sebagai *single source of truth* antara backend, frontend, dan mobile.
3. **Pencampuran Logika Bisnis** — Arsitektur berlapis (*Controller → Form Request → Service → Model → API Resource*) memisahkan tanggung jawab secara tegas.
4. **Manajemen Multi-Peran** — Sistem Role-Based Access Control (RBAC) memisahkan hak akses `superadmin`, `umkm_admin`, dan `customer`.

### Fitur Utama

- **Autentikasi & Manajemen Akun** — Registrasi, login/logout via Sanctum Bearer Token, dan inspeksi profil (`/me`).
- **Pendaftaran & Manajemen Toko UMKM** — Pengajuan toko, alur persetujuan oleh superadmin (`pending → approved/rejected/suspended`), dan pengelolaan profil toko.
- **Katalog Produk Publik** — Penelusuran produk dengan filter, pencarian teks bebas, dan paginasi.
- **Manajemen Produk & Stok Toko** — CRUD produk, monitoring stok, dan pembaruan stok dengan OCC.
- **Keranjang Belanja** — Tambah, perbarui, dan hapus item keranjang per pengguna.
- **Manajemen Alamat Pengiriman** — Simpan beberapa alamat dengan penanda `is_default`.
- **Checkout & Pesanan Atomik** — Transaksi pesanan dalam `DB::transaction()` dengan penguncian baris (`lockForUpdate()`), dekremen stok otomatis, dan kode pesanan unik (`ORD-XXXXXX`).
- **Alur Pembayaran Simulasi** — Konfirmasi pembayaran via endpoint `simulate-payment`, pembatalan pesanan, dan pelacakan status pembayaran.
- **Manajemen Pesanan Toko** — Pemilik toko dapat memperbarui status pesanan yang masuk ke toko mereka.
- **Panel Admin Superadmin** — Kelola pengguna, daftar toko, persetujuan/penolakan/suspensi, dan statistik platform.
- **Dokumentasi API Interaktif** — Antarmuka OpenAPI via Scramble tersedia langsung di `/docs/api`.

### Tech Stack

| Komponen | Teknologi | Versi |
|---|---|---|
| Bahasa | PHP | `^8.3` |
| Framework Backend | Laravel | `^13.17` |
| Autentikasi API | Laravel Sanctum | `^4.0` |
| Dokumentasi API | Dedoc Scramble | `^0.13.52` |
| Bundler Frontend | Vite | `^8.0.0` |
| Framework CSS | Tailwind CSS | `^4.0.0` |
| Database (default) | SQLite | Built-in |
| Testing | PHPUnit | `^12.5.12` |
| Linter OpenAPI | Redocly CLI | `^2.71.0` |
| Code Formatter | Laravel Pint | `^1.27` |

---

## 2. Prasyarat

Pastikan mesin pengembang memenuhi kebutuhan berikut sebelum memulai instalasi:

| Perangkat Lunak | Versi Minimal | Perintah Verifikasi |
|---|---|---|
| **PHP** | `>= 8.3.0` | `php -v` |
| **Ekstensi PHP** | `pdo_sqlite`, `sqlite3`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` | `php -m` |
| **Composer** | `>= 2.2.0` | `composer --version` |
| **Node.js** | `>= 20.0.0` (LTS direkomendasikan) | `node -v` |
| **npm** | `>= 10.0.0` | `npm -v` |
| **Git** | `>= 2.30.0` | `git --version` |

> [!NOTE]
> Database default menggunakan **SQLite** sehingga tidak perlu menginstal MySQL atau PostgreSQL untuk pengembangan lokal. Pastikan ekstensi `pdo_sqlite` sudah aktif di `php.ini`.

---

## 3. Instalasi

### a. Instalasi Langkah demi Langkah

**1. Clone repository**

```bash
git clone <url-repository>
cd UMKMhub
```

**2. Instal dependensi PHP**

```bash
composer install
```

**3. Instal dependensi JavaScript**

```bash
npm install
```

**4. Salin file environment**

```powershell
# PowerShell (Windows):
Copy-Item .env.example .env
```

```bash
# Bash (Linux/macOS):
cp .env.example .env
```

**5. Generate application key**

```bash
php artisan key:generate
```

**6. Konfigurasi file `.env`**

Buka file `.env` dan sesuaikan variabel berikut (minimal):

```env
APP_NAME=UMKMHub
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Untuk MySQL, ubah ke:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=umkmhub
# DB_USERNAME=root
# DB_PASSWORD=
```

> [!IMPORTANT]
> Untuk SQLite, buat file database kosong sebelum migrasi (jika belum ada):
> ```bash
> # PowerShell: New-Item database\database.sqlite
> # Bash: touch database/database.sqlite
> ```
> Laravel juga akan membuatnya otomatis pada langkah migrate.

**7. Jalankan migrasi dan seeding**

```bash
php artisan migrate --seed
```

Perintah ini membuat semua tabel database (17 migrasi) dan mengisi data awal demo sekaligus.

**8. Buat symlink storage**

```bash
php artisan storage:link
```

**9. Build aset frontend**

```bash
npm run build
```

**10. Jalankan server**

```bash
php artisan serve
```

Aplikasi dapat diakses di: **`http://127.0.0.1:8000`**
Dokumentasi API interaktif: **`http://127.0.0.1:8000/docs/api`**

---

### b. Instalasi Ringkas

> [!NOTE]
> Pastikan terlebih dahulu Anda mengonfigurasi variabel database di file `.env` sebelum menjalankan `migrate --seed` jika menggunakan MySQL/PostgreSQL.

**PowerShell (Windows) — gunakan `;` sebagai pemisah:**

```powershell
git clone <url-repository>; cd UMKMhub; composer install; npm install; Copy-Item .env.example .env; php artisan key:generate; php artisan migrate --seed; php artisan storage:link; npm run build
```

**Bash (Linux/macOS) — gunakan `&&` sebagai pemisah:**

```bash
git clone <url-repository> && cd UMKMhub && composer install && npm install && cp .env.example .env && php artisan key:generate && php artisan migrate --seed && php artisan storage:link && npm run build
```

Atau gunakan skrip `setup` yang sudah tersedia di `composer.json` (tanpa seeding):

```bash
composer run setup
```

> Skrip `setup` menjalankan `composer install`, pembuatan `.env`, generate key, migrasi tabel, `npm install`, dan `npm run build` secara berurutan. Jalankan `php artisan db:seed` secara terpisah jika ingin data demo.

---

## 4. Data Awal (Seeder)

Menjalankan `php artisan migrate --seed` atau `php artisan db:seed` akan menginjeksi data demo berikut:

### Akun Pengguna Demo

| Nama | Email | Password | Peran |
|---|---|---|---|
| Super Admin UMKMHub | `superadmin@umkmhub.id` | `password123` | `superadmin` |
| Sari Rahayu | `sari@umkmhub.id` | `password123` | `umkm_admin` |
| Budi Santoso | `budi@umkmhub.id` | `password123` | `umkm_admin` |
| Rina Handayani | `rina@umkmhub.id` | `password123` | `umkm_admin` |
| Dimas Pratama | `dimas@gmail.com` | `password123` | `customer` |

### Data Demo Lainnya

| Data | Keterangan |
|---|---|
| **Kategori** | Makanan Ringan, Minuman, Fashion, Kerajinan Tangan, Pertanian & Perkebunan, Kuliner |
| **Toko** | Keripik Bu Sari (approved), Kopi Dampit Budi (approved), satu toko pending |
| **Produk** | Beberapa produk dengan gambar, stok, dan variasi jumlah |
| **Pesanan** | 3 pesanan demo dengan status berbeda (completed, paid, pending_payment) |
| **Pembayaran** | Rekam pembayaran simulasi untuk pesanan demo |
| **Keranjang** | Keranjang demo milik Dimas dengan 2 item |

> [!CAUTION]
> **Jangan gunakan password demo (`password123`) di lingkungan production.** Ganti seluruh password sebelum deploy ke server publik.

---

## 5. Menjalankan Aplikasi

### Mode Development (Server + Vite HMR)

Jalankan server Laravel dan Vite secara bersamaan menggunakan skrip terpadu:

```bash
composer run dev
```

Atau jalankan secara terpisah di dua terminal:

```bash
# Terminal 1 — Backend API server:
php artisan serve

# Terminal 2 — Vite dev server (HMR):
npm run dev
```

### Akses Setelah Server Aktif

| Alamat | Deskripsi |
|---|---|
| `http://127.0.0.1:8000` | Aplikasi utama |
| `http://127.0.0.1:8000/docs/api` | Dokumentasi API interaktif (Scramble/OpenAPI) |
| `http://127.0.0.1:8000/docs/api.json` | Spesifikasi OpenAPI dalam format JSON |

---

## 6. Dokumentasi API

- **Base URL:** `http://127.0.0.1:8000/api/v1`
- **Format Data:** Semua request dan response menggunakan `Content-Type: application/json` dan `Accept: application/json`.
- **Autentikasi:** Header `Authorization: Bearer <token>` untuk endpoint yang dilindungi.
- **Envelope Respons:** Respons sukses selalu dibungkus dalam key `"data"`.

### Matriks Endpoint (48 Route)

<details>
<summary>Klik untuk melihat seluruh daftar endpoint</summary>

#### Autentikasi & Profil

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/api/v1/auth/register` | Publik | Registrasi akun baru |
| `POST` | `/api/v1/auth/login` | Publik | Login dan dapatkan Bearer Token |
| `POST` | `/api/v1/auth/logout` | 🔐 Auth | Revokasi token aktif |
| `GET` | `/api/v1/me` | 🔐 Auth | Profil pengguna aktif |

#### Pendaftaran Toko UMKM

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/api/v1/store-applications` | Publik | Ajukan pendaftaran toko baru |

#### Manajemen Toko Sendiri (`umkm_admin`)

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/v1/my-stores` | 🔐 umkm_admin | Daftar toko milik sendiri |
| `GET` | `/api/v1/my-stores/{store}` | 🔐 umkm_admin | Detail toko |
| `PUT` | `/api/v1/my-stores/{store}` | 🔐 umkm_admin | Perbarui profil toko |
| `GET` | `/api/v1/my-stores/{store}/stats` | 🔐 umkm_admin | Statistik toko |
| `GET` | `/api/v1/my-stores/{store}/products` | 🔐 umkm_admin | Daftar produk toko |
| `POST` | `/api/v1/my-stores/{store}/products` | 🔐 umkm_admin | Tambah produk ke toko |
| `PUT` | `/api/v1/my-stores/{store}/products/{product}` | 🔐 umkm_admin | Perbarui produk |
| `DELETE` | `/api/v1/my-stores/{store}/products/{product}` | 🔐 umkm_admin | Hapus produk |
| `GET` | `/api/v1/my-stores/{store}/stocks` | 🔐 umkm_admin | Monitoring stok (filter low_stock) |
| `PATCH` | `/api/v1/my-stores/{store}/stocks/{stock}` | 🔐 umkm_admin | Update stok (OCC dengan versi) |
| `GET` | `/api/v1/my-stores/{store}/orders` | 🔐 umkm_admin | Pesanan masuk ke toko |
| `PATCH` | `/api/v1/my-stores/{store}/orders/{order}/status` | 🔐 umkm_admin | Perbarui status pesanan |

#### Toko & Produk Publik

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/v1/stores` | Publik | Daftar semua toko approved |
| `GET` | `/api/v1/stores/{store:slug}` | Publik | Detail toko by slug |
| `GET` | `/api/v1/stores/{store:slug}/products` | Publik | Produk dari toko tertentu |
| `GET` | `/api/v1/products` | Publik | Katalog produk (filter, search, sort, paginasi) |
| `GET` | `/api/v1/products/{product}` | Publik | Detail produk |

#### Kategori

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/v1/categories` | Publik | Daftar semua kategori |
| `GET` | `/api/v1/categories/{category}` | Publik | Detail kategori |
| `POST` | `/api/v1/categories` | 🔐 superadmin | Tambah kategori |
| `PUT` | `/api/v1/categories/{category}` | 🔐 superadmin | Perbarui kategori |
| `DELETE` | `/api/v1/categories/{category}` | 🔐 superadmin | Hapus kategori |

#### Keranjang Belanja (`customer`)

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/v1/cart` | 🔐 customer | Lihat keranjang |
| `POST` | `/api/v1/cart/items` | 🔐 customer | Tambah item ke keranjang |
| `PATCH` | `/api/v1/cart/items/{cartItem}` | 🔐 customer | Perbarui kuantitas item |
| `DELETE` | `/api/v1/cart/items/{cartItem}` | 🔐 customer | Hapus item dari keranjang |

#### Alamat Pengiriman (`customer`)

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/v1/addresses` | 🔐 customer | Daftar alamat tersimpan |
| `POST` | `/api/v1/addresses` | 🔐 customer | Tambah alamat baru |
| `PUT` | `/api/v1/addresses/{address}` | 🔐 customer | Perbarui alamat |
| `DELETE` | `/api/v1/addresses/{address}` | 🔐 customer | Hapus alamat |

#### Pesanan & Pembayaran (`customer`)

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `POST` | `/api/v1/orders/checkout` | 🔐 customer | Checkout pesanan (atomik, decrement stok) |
| `GET` | `/api/v1/orders` | 🔐 customer | Riwayat pesanan |
| `GET` | `/api/v1/orders/{order}` | 🔐 Auth | Detail pesanan |
| `POST` | `/api/v1/orders/{order}/cancel` | 🔐 customer | Batalkan pesanan |
| `POST` | `/api/v1/orders/{order}/simulate-payment` | 🔐 customer | Simulasi konfirmasi pembayaran |

#### Admin Panel (`superadmin`)

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| `GET` | `/api/v1/admin/store-applications` | 🔐 superadmin | Daftar pengajuan toko pending |
| `POST` | `/api/v1/admin/stores/{store}/approve` | 🔐 superadmin | Setujui toko |
| `POST` | `/api/v1/admin/stores/{store}/reject` | 🔐 superadmin | Tolak toko |
| `POST` | `/api/v1/admin/stores/{store}/suspend` | 🔐 superadmin | Suspensi toko |
| `POST` | `/api/v1/admin/stores/{store}/activate` | 🔐 superadmin | Aktifkan kembali toko |
| `GET` | `/api/v1/admin/stores` | 🔐 superadmin | Daftar semua toko |
| `GET` | `/api/v1/admin/users` | 🔐 superadmin | Daftar semua pengguna |
| `GET` | `/api/v1/admin/stats` | 🔐 superadmin | Statistik platform |

</details>

### Alur Status Pesanan

```
pending_payment → paid → processing → shipped → completed
                ↘                              ↗
                       cancelled
```

### Alur Status Toko

```
(Pengajuan) → pending → approved → (aktif beroperasi)
                      ↘ rejected
              approved → suspended → approved (re-aktifkan)
```

---

## 7. Menjalankan Test

### Konfigurasi Test

Test dikonfigurasi di [`phpunit.xml`](phpunit.xml) menggunakan **SQLite in-memory** sehingga tidak memerlukan database terpisah dan berjalan sangat cepat. Konfigurasi sudah diterapkan otomatis — tidak perlu membuat `.env.testing`.

### Perintah

```bash
# Jalankan semua test (via Artisan, membersihkan cache konfigurasi terlebih dahulu):
composer run test

# Langsung via Artisan:
php artisan test

# Langsung via PHPUnit:
./vendor/bin/phpunit

# Jalankan test spesifik berdasarkan nama:
php artisan test --filter=AuthApiTest

# Jalankan test dari file tertentu:
php artisan test tests/Feature/AuthApiTest.php

# Jalankan seluruh test suite Feature:
php artisan test --testsuite=Feature

# Dengan output compact (ringkas):
php artisan test --compact
```

### File Test yang Tersedia

| File | Cakupan |
|---|---|
| `tests/Unit/ExampleTest.php` | Unit test dasar |
| `tests/Feature/ExampleTest.php` | Feature test dasar |
| `tests/Feature/AuthApiTest.php` | Autentikasi (register, login, logout, profile) |
| `tests/Feature/ProductApiTest.php` | CRUD produk, paginasi, filter |
| `tests/Feature/ProductWeek3ApiTest.php` | Auth, OCC stok, checkout, error handling |
| `tests/Feature/OrderFlowTest.php` | Alur checkout hingga pembayaran simulasi |
| `tests/Feature/StoreApplicationTest.php` | Pengajuan dan persetujuan toko |
| `tests/Feature/StoreIsolationTest.php` | Isolasi data antar toko |
| `tests/Feature/WebPagesTest.php` | Respons halaman web |

> [!NOTE]
> Komentar di `phpunit.xml` mencatat bahwa **uji konkurensi stok (OCC) sebaiknya dijalankan langsung di MySQL** untuk mensimulasikan kondisi *concurrent* yang sebenarnya, karena SQLite tidak mendukung *row-level locking* seperti MySQL.

### Pemeriksaan Gaya Kode

```bash
# Periksa dan perbaiki otomatis (PSR-12):
./vendor/bin/pint

# Periksa saja tanpa mengubah file:
./vendor/bin/pint --test
```

---

## 8. Struktur Folder

```text
UMKMhub/
├── app/
│   ├── Enums/                    # Enum PHP 8 (UserRole, OrderStatus, StoreStatus, PaymentStatus)
│   ├── Exceptions/               # Custom exception (StockConflictException → HTTP 409)
│   ├── Http/
│   │   ├── Controllers/Api/V1/   # Controller REST API v1 (Auth, Store, Order, dll.)
│   │   │   └── Admin/            # Controller khusus superadmin
│   │   ├── Middleware/           # Middleware (RoleMiddleware, dll.)
│   │   ├── Requests/             # Lapisan validasi Form Request per domain
│   │   └── Resources/            # Transformasi respons JSON (API Resource)
│   ├── Models/                   # Eloquent model (User, Store, Product, Stock, Order, Payment, dll.)
│   ├── Providers/                # Service provider (AppServiceProvider)
│   ├── Services/                 # Lapisan logika bisnis (OrderService, StockService, dll.)
│   └── helpers.php               # Fungsi helper global
├── bootstrap/
│   └── app.php                   # Konfigurasi routing, middleware, dan global exception handler
├── config/                       # File konfigurasi Laravel (app, auth, database, sanctum, dll.)
├── database/
│   ├── factories/                # Factory data untuk testing
│   ├── migrations/               # 17 file migrasi skema database
│   └── seeders/
│       └── DatabaseSeeder.php    # Seeding data demo (admin, toko, produk, pesanan, pembayaran)
├── docs/
│   ├── openapi.yaml              # Kontrak OpenAPI 3.1.0 (single source of truth)
│   ├── api-design.md             # Dokumen rancangan endpoint dan kueri
│   └── postman/                  # Koleksi Postman siap pakai
├── routes/
│   ├── api.php                   # 48 definisi rute API v1
│   ├── console.php               # Perintah Artisan kustom
│   └── web.php                   # Rute web (halaman statis & fallback)
├── tests/
│   ├── Feature/                  # Feature test (8 file)
│   └── Unit/                     # Unit test
├── .env.example                  # Template variabel environment
├── composer.json                 # Dependensi PHP & skrip Composer
├── package.json                  # Dependensi JavaScript & skrip Vite
├── phpunit.xml                   # Konfigurasi PHPUnit (SQLite in-memory)
└── vite.config.js                # Konfigurasi bundler Vite & Tailwind v4
```

---

## 9. Troubleshooting

| Gejala | Penyebab | Solusi |
|---|---|---|
| `No application encryption key has been specified` | `APP_KEY` di `.env` kosong | Jalankan `php artisan key:generate` |
| `Driver [sqlite] not supported` | Ekstensi SQLite PHP belum aktif | Buka `php.ini`, aktifkan `extension=pdo_sqlite` dan `extension=sqlite3`, restart server |
| `General error: 1 no such table: ...` | Migrasi belum dijalankan | Jalankan `php artisan migrate` |
| `SQLSTATE[HY000]: General error: 8 attempt to write a readonly database` | File SQLite tidak bisa ditulis | Pastikan file `database/database.sqlite` ada dan dapat ditulis. Buat dengan: `New-Item database\database.sqlite` (PowerShell) |
| `'vite' is not recognized...` | `npm install` belum dijalankan | Jalankan `npm install` lalu ulangi `npm run build` |
| `Unable to locate file in Vite manifest` | Aset frontend belum di-build | Jalankan `npm run build` atau `npm run dev` |
| HTTP `409 Conflict` — `STOK_VERSION_MISMATCH` | Nilai `version` stok sudah usang | Ambil `version` terbaru via `GET /api/v1/my-stores/{store}/stocks`, lalu ulangi `PATCH` |
| HTTP `409 Conflict` — `INSUFFICIENT_STOCK` | Kuantitas checkout melebihi sisa stok | Kurangi jumlah pesanan atau restok via endpoint stok |
| HTTP `409 Conflict` — `CATEGORY_IN_USE` | Kategori masih memiliki produk | Pindahkan atau hapus produk terkait terlebih dahulu |
| HTTP `403 Forbidden` | Token tidak memiliki role yang sesuai | Gunakan akun dengan peran yang benar (lihat bagian [Data Awal](#4-data-awal-seeder)) |
| Port 8000 sudah digunakan | Proses lain menempati port 8000 | Jalankan dengan port lain: `php artisan serve --port=8001` |

---

## 10. Deployment

Untuk deploy ke server produksi:

1. **Konfigurasi environment:**
   - Set `APP_ENV=production` dan `APP_DEBUG=false`.
   - Gunakan database produksi (MySQL 8.0+ atau PostgreSQL 15+): sesuaikan `DB_CONNECTION`, `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

2. **Instal dependensi (tanpa package dev):**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Build aset:**
   ```bash
   npm ci && npm run build
   ```

4. **Jalankan migrasi:**
   ```bash
   php artisan migrate --force
   ```

5. **Cache konfigurasi dan route:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

6. **Web server:** Arahkan *document root* Nginx/Apache ke folder `/public`. Pastikan folder `storage/` dan `bootstrap/cache/` dapat ditulis oleh user web server.

---

## 11. Kontribusi dan Lisensi

### Kontribusi

1. **Fork** repositori ini.
2. Buat branch sesuai konvensi: `feat/nama-fitur`, `fix/nama-bug`, `refactor/nama-modul`, atau `test/nama-pengujian`.
3. Format kode menggunakan Laravel Pint sebelum commit:
   ```bash
   ./vendor/bin/pint
   ```
4. Pastikan seluruh test lolos:
   ```bash
   php artisan test
   ```
5. Buat **Pull Request** ke branch `main` dengan deskripsi perubahan yang jelas.

### Lisensi

Kode sumber dilisensikan di bawah **MIT License** sebagaimana tercantum dalam `composer.json`. Dokumen kontrak API (`docs/openapi.yaml`) dilisensikan di bawah [Creative Commons Attribution 4.0 International (CC BY 4.0)](https://creativecommons.org/licenses/by/4.0/).

> *Catatan: File `LICENSE` fisik belum tersedia di root direktori proyek.*

---

## Tim Pengembang

Dikembangkan oleh **Kelompok 1 — Mata Kuliah Pemrograman Web Service**:


**Referensi Teknis:**
- Spesifikasi OpenAPI: [`docs/openapi.yaml`](docs/openapi.yaml)
- Koleksi Postman: [`docs/postman/collection.json`](docs/postman/collection.json)
- Desain API: [`docs/api-design.md`](docs/api-design.md)

---

<div align="center">

**Dikembangkan dengan integritas teknis oleh Kelompok 1 — Pemrograman Web Service © 2026**

</div>
