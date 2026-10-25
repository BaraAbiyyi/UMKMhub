# Modul 04: API Design Document v1

## A. IDENTITAS DOKUMEN

* **Nama Proyek:** UMKMHub API  
* **Kelompok:** Kelompok 1  
* **Anggota Kelompok:**  
  1. Bara Abiyyi Muslimin (Products \- Penyusun Akhir)  
  2. Afsani Wahyu Mawardi (Categories \- Reviewer Status Code)  
  3. Arif Nur Syifa (Stocks \- Specialist Concurrency Control)  
  4. Agil Syeh Febukhori (Customers \- Reviewer Format JSON)  
  5. Yesa Anggit Prayugo (Orders & Order Items)  
* **Base URL:** `/api/v1`  
* **Bahasa Penamaan:** Huruf kecil dipisahkan tanda hubung (-)  
* **Format Exchange Data:** `application/json`

---

## B. PEMODELAN RESOURCE & RELASI (Aktivitas 1\)

Sistem UMKMHub mengelola resource dan sub-resource yang diturunkan dari kebutuhan fungsional proyek:

| No | Resource (Jamak) | Atribut Utama | Relasi | Jenis |
| :---: | :---- | :---- | :---- | :---- |
| 1 | `products` | id, name, description, price, category\_id, is\_active, created\_at | Milik satu `categories`; Punya satu `stocks` | Collection & Item |
| 2 | `categories` | id, name, description | Punya banyak `products` | Collection & Item |
| 3 | `stocks` | id, product\_id, quantity, version, updated\_at | Milik satu `products` | Collection & Item |
| 4 | `customers` | id, name, phone, address, created\_at | Punya banyak `orders` | Collection & Item |
| 5 | `orders` | id, customer\_id, total\_amount, status, created\_at | Milik satu `customers`; Berisi banyak `order-items` | Collection & Item |
| 6 | `order-items` | id, order\_id, product\_id, quantity, price, subtotal | Milik satu `orders`; Merujuk ke `products` | Sub-resource |

---

## C. ENDPOINT MATRIX (Aktivitas 2 \- 15 Endpoint)

| No | Method | URI Endpoint | Fungsi / Deskripsi | Aktor | Sukses | Gagal |
| :---: | :---: | :---- | :---- | :---: | :---: | :---: |
| 1 | POST | `/api/v1/auth/login` | Autentikasi user & penerbitan Bearer token | Semua | 200 OK | 400, 401 |
| 2 | GET | `/api/v1/products` | Daftar produk katalog (filter, search, sort, pagination) | Semua | 200 OK | 400, 422 |
| 3 | GET | `/api/v1/products/{id}` | Detail spesifik suatu produk | Semua | 200 OK | 404 |
| 4 | POST | `/api/v1/products` | Tambah produk baru ke katalog | Admin | 201 Created | 400, 401, 403, 422 |
| 5 | PATCH | `/api/v1/products/{id}` | Perbarui sebagian data produk | Admin | 200 OK | 401, 403, 404, 422 |
| 6 | DELETE | `/api/v1/products/{id}` | Hapus produk dari katalog | Admin | 204 No Content | 401, 403, 404, 409 |
| 7 | GET | `/api/v1/categories` | Daftar seluruh kategori produk | Semua | 200 OK | \- |
| 8 | POST | `/api/v1/categories` | Tambah kategori baru | Admin | 201 Created | 401, 403, 409, 422 |
| 9 | GET | `/api/v1/stocks` | Monitoring jumlah stok seluruh produk | Admin | 200 OK | 401, 403 |
| 10 | PATCH | `/api/v1/stocks/{id}` | Update jumlah stok dengan Optimistic Locking (`version`) | Admin | 200 OK | 401, 403, 404, 409, 422 |
| 11 | POST | `/api/v1/customers` | Pendaftaran pelanggan baru | Umum | 201 Created | 400, 422 |
| 12 | GET | `/api/v1/customers/{id}/orders` | Riwayat pesanan milik pelanggan tertentu (Nested Resource) | Pelanggan | 200 OK | 401, 403, 404 |
| 13 | POST | `/api/v1/orders` | Checkout pesanan baru (validasi stok & potong otomatis) | Pelanggan | 201 Created | 400, 401, 409, 422 |
| 14 | GET | `/api/v1/orders/{id}` | Detail rincian pesanan dan item yang dipesan | Pelanggan | 200 OK | 401, 403, 404 |
| 15 | PATCH | `/api/v1/orders/{id}/status` | Mengubah status transaksi pesanan | Admin | 200 OK | 401, 403, 404, 422 |

---

## D. DESAIN QUERY (Aktivitas 3\)

| Endpoint | Filter | Search | Sort (default) | Pagination (default/maks) |
| :---- | :---- | :---- | :---- | :---- |
| `GET /api/v1/products` | `category_id`, `is_active` | `q` (name, description) | `price`, `-price`, `name`, `-created_at` (Default: `-created_at`) | Page-based (`page=1`, `per_page=15`, Max: 100\) |
| `GET /api/v1/stocks` | `low_stock` (stok \<= 10\) | \- | `quantity`, `-quantity`, `updated_at` (Default: `quantity`) | Page-based (`page=1`, `per_page=15`, Max: 100\) |

---

## E. REPRESENTASI DATA (REPRESENTATION & ENVELOPE)

### 1\. Item Response (`GET /api/v1/products/1`) – 200 OK

{

  "data": {

    "id": 1,

    "name": "Keripik Tempe Renyah",

    "description": "Keripik tempe gurih renyah kemasan 200 gram khas Malang",

    "price": 15000,

    "category": {

      "id": 1,

      "name": "Makanan Ringan"

    },

    "is\_active": true,

    "created\_at": "2026-10-12T08:00:00Z"

  }

}

### 2\. Collection Response (`GET /api/v1/products?page=1&per_page=2`) – 200 OK

{

  "data": \[

    {

      "id": 1,

      "name": "Keripik Tempe Renyah",

      "price": 15000,

      "category\_id": 1,

      "is\_active": true

    },

    {

      "id": 2,

      "name": "Kopi Robusta Dampit 250g",

      "price": 35000,

      "category\_id": 2,

      "is\_active": true

    }

  \],

  "links": {

    "first": "/api/v1/products?page=1\&per\_page=2",

    "last": "/api/v1/products?page=3\&per\_page=2",

    "prev": null,

    "next": "/api/v1/products?page=2\&per\_page=2"

  },

  "meta": {

    "current\_page": 1,

    "last\_page": 3,

    "per\_page": 2,

    "total": 5

  }

}

---

## F. STANDAR FORMAT ERROR RESPONSE

* **422 Unprocessable Content (Validasi Gagal):**  
    
  {  
    
    "message": "Data yang dikirimkan tidak valid.",  
    
    "errors": {  
    
      "price": \["Harga produk harus bernilai positif."\]  
    
    }  
    
  }  
    
* **409 Conflict (Konflik Data / Versi Stok Berubah):**  
    
  {  
    
    "code": "STOK\_VERSION\_MISMATCH",  
    
    "message": "Stok telah diperbarui oleh pengguna lain. Silakan muat ulang data terbaru."  
    
  }

---

## G. AKSI NON-CRUD & PROSES BISNIS

Perubahan status pesanan dimodelkan sebagai *Resource Attribute Update* melalui endpoint `PATCH /api/v1/orders/{id}/status` dengan pilihan nilai status: `pending`, `processing`, `completed`, `cancelled`.

---

## H. CATATAN KEPUTUSAN DESAIN (DESIGN DECISIONS)

1. **Penggunaan PATCH Dibandingkan PUT:** Tim sepakat menggunakan `PATCH` untuk operasi update sebagian data guna meminimalkan risiko pengiriman data parsial yang tidak disengaja.  
2. **Pencatatan Versi Stok (Optimistic Locking):** Resource stok membawa atribut `version` untuk mencegah ketersediaan stok yang tidak acak saat diakses bersamaan (*concurrency issue*).  
3. **Pembungkusan Envelope Data:** Seluruh response sukses dibungkus dalam kunci utama `"data"` untuk memisahkan konten bisnis dari metadata pagination.

---

## I. PEER REVIEW CHECKLIST (Aktivitas 4\)

| No | Butir Pemeriksaan | Ya | Tidak | Catatan |
| :---: | :---- | :---: | :---: | :---- |
| 1 | Semua URI memakai kata benda jamak, huruf kecil, dan tanpa kata kerja | \[x\] |  | Sesuai konvensi REST API |
| 2 | Prefix `/api/v1` dipakai konsisten | \[x\] |  | Diterapkan pada seluruh 15 endpoint |
| 3 | Method sesuai operasi; `GET` tidak mengubah data | \[x\] |  | Kepatuhan semantik HTTP method |
| 4 | `POST` \-\> 201, `DELETE` \-\> 204, dan status gagal relevan dicantumkan | \[x\] |  | Lengkap tercatat di Endpoint Matrix |
| 5 | Nesting maksimal 2 tingkat | \[x\] |  | Contoh: `/api/v1/customers/{id}/orders` |
| 6 | Setiap collection memiliki pagination dengan batas maksimum | \[x\] |  | Menggunakan `page` & `per_page` (max 100\) |
| 7 | Filter/sort memakai query parameter dengan whitelist | \[x\] |  | Diterapkan pada endpoint produk dan stok |
| 8 | Format error seragam | \[x\] |  | Menggunakan struktur `message` dan `errors` |

---

*Dokumen ini disusun sebagai bagian dari tugas kelompok Minggu 4 mata kuliah Pemrograman Web Service.*