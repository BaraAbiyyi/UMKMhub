# Checklist Kesesuaian Kontrak OpenAPI & Hasil Pengujian API (Aktivitas 4)

**Proyek:** UMKMHub REST API  
**Versi API:** `v1`  
**Dokumen Kontrak Acuan:** `docs/openapi.yaml`  
**Modul Ajar Acuan:** Modul 06 - *Implementasi Laravel REST API & Layered Structure*  
**Tanggal Pengujian:** 28 Oktober 2026  
**Status Kepatuhan Kontrak:** **100% COMPLIANT (14/14 Skenario Lolos)**

---

## 1. Tabel Matriks Kesesuaian Kontrak & Pengujian

Berikut adalah hasil pengujian komprehensif terhadap 14 skenario pengujian (mencakup alur sukses, validasi input, konkurensi OCC, transaksi database, dan penanganan error global) menggunakan Postman Collection v2.1 (`docs/postman/collection.json`) serta Automated Feature Tests Laravel:

| No | Skenario Pengujian | Endpoint & HTTP Method | Parameter / Payload Uji | Status Diharapkan | Status Aktual | Kesesuaian Skema Response Body | Catatan / Referensi Klausul OpenAPI |
|---|---|---|---|:---:|:---:|:---:|---|
| **01** | Autentikasi Admin Login Sukses | `POST /api/v1/auth/login` | `{"email": "admin@umkmhub.id", "password": "password123"}` | `200 OK` | `200 OK` | **SESUAI** | Skema `AuthToken` (`token_type: Bearer`, `access_token`, `expires_in: 86400`). Sesuai Modul 06 Hal. 8 & `components/schemas/AuthToken`. |
| **02** | Autentikasi Login Kredensial Salah | `POST /api/v1/auth/login` | `{"email": "admin@umkmhub.id", "password": "passwordsalah123"}` | `401 Unauthorized` | `401 Unauthorized` | **SESUAI** | Skema `Error` (`code: INVALID_CREDENTIALS`, `message: "Email atau password yang Anda masukkan salah."`). Sesuai kontrak `post /auth/login` responses `401`. |
| **03** | Ambil Daftar Produk Paginasi Default | `GET /api/v1/products` | *(Query default)* | `200 OK` | `200 OK` | **SESUAI** | Envelope pagination `ProductPagination` (`data`, `links`, `meta` dengan `current_page`, `per_page`, `total`). Sesuai `get /products` responses `200`. |
| **04** | Validasi Pagination Melebihi Batas Maksimal | `GET /api/v1/products?per_page=500` | Query: `per_page=500` (maksimal 100) | `422 Unprocessable Content` | `422 Unprocessable Content` | **SESUAI** | Skema Laravel Validation `{"message": "...", "errors": {"per_page": [...]}}`. Validasi Form Request `IndexProductRequest` rule `per_page: integer|min:1|max:100`. |
| **05** | Tambah Produk Baru dengan Otorisasi Admin | `POST /api/v1/products` | Header: `Bearer <token>`<br>Body: `{"category_id": 1, "name": "Keripik Singkong Pedas", "description": "...", "price": 12000, "is_active": true}` | `201 Created` | `201 Created` | **SESUAI** | Skema `ProductDetailResponse` (`data.id`, `category`, `price`, `stock`). Header `Location` disertakan menunjuk URI resource baru (`Location: http://.../api/v1/products/{id}`). Sesuai Modul 06 Hal. 10. |
| **06** | Ambil Semua Kategori Produk | `GET /api/v1/categories` | *(Tanpa parameter)* | `200 OK` | `200 OK` | **SESUAI** | Skema `CategoryListResponse` (`data: [Category]`). Sesuai Modul 06 Hal. 6 & `get /categories` responses `200`. |
| **07** | Monitoring Daftar Stok & Filter Rendah | `GET /api/v1/stocks?low_stock=false&per_page=10` | Header: `Bearer <token>`<br>Query: `low_stock=false`, `per_page=10` | `200 OK` | `200 OK` | **SESUAI** | Skema `StockPagination` (`data`, `links`, `meta`). Menampilkan kuantitas stok, status `is_low_stock`, dan relasi produk. Sesuai `get /stocks`. |
| **08** | Update Kuantitas Stok Berhasil (Optimistic Concurrency) | `PATCH /api/v1/stocks/1` | Header: `Bearer <token>`<br>Body: `{"quantity": 60, "version": 1}` | `200 OK` | `200 OK` | **SESUAI** | Skema `StockResponse` (`data.version` naik dari `1` menjadi `2`, `quantity: 60`). Sesuai Modul 06 Hal. 13-14 (OCC pattern). |
| **09** | Update Stok Versi Usang / Konflik Bersamaan | `PATCH /api/v1/stocks/1` | Header: `Bearer <token>`<br>Body: `{"quantity": 70, "version": 1}` | `409 Conflict` | `409 Conflict` | **SESUAI** | Skema `StockConflictResponse` (`code: STOK_VERSION_MISMATCH`, `message: "Versi stok sudah berubah..."`). Sesuai Modul 06 Hal. 14. |
| **10** | Checkout Pesanan Berhasil (Atomic Transaction) | `POST /api/v1/orders` | Header: `Bearer <token>`<br>Body: `{"customer_id": 1, "items": [{"product_id": 1, "quantity": 2}]}` | `201 Created` | `201 Created` | **SESUAI** | Skema `OrderResponse` (`data.status: "pending"`, `items: [...]`, `total_amount`), Header `Location` disertakan. Menggunakan `DB::transaction` dan `lockForUpdate()`. |
| **11** | Checkout Pesanan Gagal karena Stok Kurang | `POST /api/v1/orders` | Header: `Bearer <token>`<br>Body: `{"customer_id": 1, "items": [{"product_id": 1, "quantity": 9999}]}` | `409 Conflict` | `409 Conflict` | **SESUAI** | Skema `StockConflictResponse` (`code: INSUFFICIENT_STOCK`, `message: "Stok produk tidak mencukupi..."`). Transaksi di-rollback otomatis. |
| **12** | Ambil Riwayat Pesanan Pelanggan Berpaginasi | `GET /api/v1/customers/1/orders` | Header: `Bearer <token>` | `200 OK` | `200 OK` | **SESUAI** | Skema `OrderPagination` (`data`, `links`, `meta`). Memastikan paginasi riwayat pesanan pelanggan sesuai kontrak OpenAPI. |
| **13** | Penanganan Global Error Resource Tidak Ditemukan | `GET /api/v1/nonexistent-endpoint-test` | *(Tanpa payload)* | `404 Not Found` | `404 Not Found` | **SESUAI** | Skema `Error` (`message: "Resource tidak ditemukan."`). `bootstrap/app.php` menangani `NotFoundHttpException` untuk API tanpa stack trace. |
| **14** | Penanganan Global Error Method Tidak Diizinkan | `DELETE /api/v1/stocks` | *(Method DELETE pada endpoint yang hanya mendukung GET)* | `405 Method Not Allowed` | `405 Method Not Allowed` | **SESUAI** | Skema `Error` (`message: "Method tidak diizinkan."`). `bootstrap/app.php` menangani `MethodNotAllowedHttpException` untuk API tanpa stack trace. |

---

## 2. Ringkasan Evaluasi Kepatuhan

- **Total Skenario Diuji:** 14 skenario (melampaui target minimum 10 skenario).
- **Skenario Lolos (Pass):** 14 (100%).
- **Skenario Gagal (Fail):** 0 (0%).
- **Kesesuaian HTTP Status Code:** 100% tepat (`200`, `201`, `401`, `404`, `405`, `409`, `422`).
- **Kesesuaian HTTP Headers:** Header `Location` terpasang pada semua operasi `201 Created` (`POST /products`, `POST /orders`, `POST /categories`, `POST /customers`).
- **Keamanan:** Parameter `APP_DEBUG=false` memastikan tidak ada kebocoran jejak tumpukan (*stack trace*), path direktori server, maupun kredensial database pada respon kegagalan sistem.

