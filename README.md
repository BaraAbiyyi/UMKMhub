# UMKMHub API

**Proyek:** UMKMHub API (Integrasi Katalog dan Pesanan UMKM)  
**Mata Kuliah:** Pemrograman Web Service - Minggu 2  

REST API berbasis Laravel untuk katalog produk UMKM dengan implementasi in-memory array dan dukungan cache persisten.

---

## Daftar Endpoint API

Semua request dan response menggunakan format **JSON** dengan base prefix `/api/v1`.

| Method | Endpoint | Deskripsi | Status Code |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/products` | Mengambil seluruh katalog produk UMKM | `200 OK` |
| `POST` | `/api/v1/products` | Menambahkan produk baru | `201 Created` |
| `GET` | `/api/v1/products/{id}` | Mengambil detail produk berdasarkan ID | `200 OK` / `404 Not Found` |

---

## Format Payload & Response

### 1. `GET /api/v1/products`
**Response (200 OK):**
```json
{
  "status": "success",
  "message": "Daftar produk UMKM berhasil diambil",
  "data": [
    {
      "id": 1,
      "name": "Keripik Tempe Renyah",
      "category": "Makanan Ringan",
      "price": 15000,
      "stock": 50
    }
  ]
}
```

### 2. `POST /api/v1/products`
**Header:** `Content-Type: application/json`, `Accept: application/json`  
**Request Body:**
```json
{
  "name": "Madu Hutan Asli Sumbawa",
  "category": "Kesehatan",
  "price": 85000,
  "stock": 15
}
```

**Response (201 Created):**
```json
{
  "status": "success",
  "message": "Produk UMKM berhasil dibuat",
  "data": {
    "id": 6,
    "name": "Madu Hutan Asli Sumbawa",
    "category": "Kesehatan",
    "price": 85000,
    "stock": 15
  }
}
```

### 3. `GET /api/v1/products/{id}`
- **Jika ditemukan (200 OK):**
```json
{
  "status": "success",
  "message": "Detail produk UMKM berhasil ditemukan",
  "data": {
    "id": 1,
    "name": "Keripik Tempe Renyah",
    "category": "Makanan Ringan",
    "price": 15000,
    "stock": 50
  }
}
```
- **Jika tidak ditemukan (404 Not Found):**
```json
{
  "status": "error",
  "message": "Produk tidak ditemukan"
}
```

---

## Cara Menjalankan Proyek

1. **Clone repository:**
   ```bash
   git clone https://github.com/BaraAbiyyi/UMKMhub.git
   cd UMKMhub
   ```

2. **Install dependensi PHP:**
   ```bash
   composer install
   ```

3. **Salin environment file dan generate application key:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Jalankan development server:**
   ```bash
   php artisan serve
   ```
   Aplikasi akan berjalan di `http://127.0.0.1:8000`.

---

## Pengujian Otomatis (Testing)

Jalankan pengujian unit/fitur menggunakan PHPUnit:
```bash
php artisan test --compact tests/Feature/ProductApiTest.php
```
Semua test menguji skenario:
- Pengambilan seluruh daftar produk
- Penambahan produk baru dan persistensi data
- Pengambilan produk berdasarkan ID yang valid
- Penanganan error 404 ketika ID tidak ditemukan
