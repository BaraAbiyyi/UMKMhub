# Checklist Kesesuaian Kontrak OpenAPI & Hasil Pengujian API (v2)

**Proyek:** UMKMHub — Marketplace UMKM (Simulasi Transaksi)  
**Versi API:** `v2` (`/api/v1`)  
**Dokumen Kontrak Acuan:** `docs/openapi.yaml` (dihasilkan dari kode melalui Scramble)  
**Koleksi Postman:** `docs/postman/collection.json`  
**Status Kepatuhan:** seluruh skenario otomatis lulus (`php artisan test`, 46 test).

---

## 1. Matriks Kesesuaian Kontrak & Pengujian

| No | Skenario | Endpoint | Status Diharapkan | Status Aktual | Catatan |
|---|---|---|---|---|---|
| 01 | Registrasi pembeli | `POST /api/v1/auth/register` | `201` | `201` | Token Sanctum + data user. |
| 02 | Login semua peran | `POST /api/v1/auth/login` | `200` | `200` | Mengembalikan `role` dan `role_label`. |
| 03 | Login kredensial salah | `POST /api/v1/auth/login` | `401` | `401` | `code: INVALID_CREDENTIALS`. |
| 04 | Pendaftaran UMKM | `POST /api/v1/store-applications` | `201` | `201` | Toko `pending` + akun `umkm_admin`. |
| 05 | Katalog publik | `GET /api/v1/products` | `200` | `200` | Hanya produk aktif dari toko `approved`. |
| 06 | Validasi paginasi | `GET /api/v1/products?per_page=500` | `422` | `422` | Maksimal 100. |
| 07 | Filter dan urut katalog | `GET /api/v1/products?search=...&sort=price` | `200` | `200` | Whitelist `sort`. |
| 08 | Detail produk nonaktif | `GET /api/v1/products/{id}` | `404` | `404` | Produk tersembunyi dari publik. |
| 09 | Isolasi toko | `GET /api/v1/my-stores/{store}/products` | `403` | `403` | `code: FORBIDDEN_STORE_ACCESS`. |
| 10 | Tambah produk toko sendiri | `POST /api/v1/my-stores/{store}/products` | `201` | `201` | Header `Location`, stok awal `0`. |
| 11 | Update stok OCC | `PATCH /api/v1/my-stores/{store}/stocks/{stock}` | `200` | `200` | `version` naik. |
| 12 | Konflik versi stok | `PATCH /api/v1/my-stores/{store}/stocks/{stock}` | `409` | `409` | `code: STOK_VERSION_MISMATCH`. |
| 13 | Checkout memecah keranjang per toko | `POST /api/v1/orders/checkout` | `201` | `201` | Satu pesanan per toko, stok terpotong atomik. |
| 14 | Checkout stok kurang | `POST /api/v1/orders/checkout` | `409` | `409` | `code: INSUFFICIENT_STOCK`, transaksi rollback. |
| 15 | Pembayaran simulasi berhasil | `POST /api/v1/orders/{id}/simulate-payment` | `200` | `200` | Status `paid`. |
| 16 | Pembayaran gagal | `POST /api/v1/orders/{id}/simulate-payment` | `200` | `200` | Status `cancelled`, stok dikembalikan. |
| 17 | Batal pesanan dibayar | `POST /api/v1/orders/{id}/cancel` | `200` | `200` | Stok dikembalikan. |
| 18 | Pesanan kedaluwarsa | scheduler `orders:expire` | — | — | Status `cancelled`, stok dikembalikan. |
| 19 | Akses pesanan pembeli lain | `GET /api/v1/orders/{id}` | `403` | `403` | Otorisasi per pemilik/toko/peran. |
| 20 | Transisi status tidak valid | `PATCH /api/v1/my-stores/{store}/orders/{order}/status` | `422` | `422` | `code: INVALID_STATUS_TRANSITION`. |
| 21 | Persetujuan toko | `POST /api/v1/admin/stores/{id}/approve` | `200` | `200` | Hanya Superadmin. |
| 22 | Penolakan tanpa alasan | `POST /api/v1/admin/stores/{id}/reject` | `422` | `422` | `rejection_reason` wajib. |
| 23 | Akses admin oleh pembeli | `GET /api/v1/admin/stats` | `403` | `403` | `code: FORBIDDEN_ROLE_ACCESS`. |
| 24 | Route tidak ditemukan | `GET /api/v1/nonexistent` | `404` | `404` | JSON tanpa stack trace. |
| 25 | Method tidak diizinkan | `DELETE /api/v1/products` | `405` | `405` | JSON tanpa stack trace. |
| 26 | Halaman web publik & dashboard | 10 halaman Blade | `200` | `200` | Diuji pada `WebPagesTest`. |
| 27 | Alur web login → checkout → bayar | route web | redirect + `200` | sesuai | `WebPagesTest`. |

---

## 2. Ringkasan

- **Total automated test:** 46 lulus, 0 gagal.
- **Kontrak OpenAPI:** valid (Redocly), 39 path dan 48 operasi, termasuk security per operasi.
- **Postman:** 48 request dalam 13 folder, autentikasi Bearer `{{token}}` diisi otomatis oleh request login.
- **Mode simulasi:** tidak ada payment gateway; hasil pembayaran dipilih melalui endpoint simulasi.
- **Keamanan:** Bearer token Sanctum, policy per toko, middleware peran, validasi Form Request, dan error global tanpa stack trace.
