# Dokumentasi API - Perpustakaan Digital

**Base URL:** `http://127.0.0.1:8000/api`

**Header Wajib:**
- `Accept: application/json`
- `Content-Type: application/json` (untuk POST/PUT)

---

## Daftar Endpoint

| # | Method | URL | Body (jika ada) | Contoh Response (sukses) | Status Gagal |
|---|---|---|---|---|---|
| 1 | GET | `/api/books` | - | `200` `{"data":[{"id":3,"judul":"Bumi Manusia","category":{"id":2,"nama_kategori":"Fiksi"}}],"links":{...},"meta":{"current_page":1,"per_page":10,"total":12}}` | - |
| 2 | GET | `/api/books/{id}` | - | `200` `{"data":{"id":4,"judul":"Clean Code","penulis":"Robert C. Martin","category":{"id":3,"nama_kategori":"Teknologi"}}}` | `404` `{"message":"No query results for model [App\\Models\\Book] 99999"}` |
| 3 | POST | `/api/books` | `{"judul":"Test API","penulis":"Author Test","penerbit":"Publisher Test","tahun_terbit":2024,"isbn":"1234567890","stok":5,"category_id":1}` | `201` `{"data":{"id":13,"judul":"Test API",...}}` | `422` `{"message":"Judul buku wajib diisi. (and 5 more errors)","errors":{"judul":["Judul buku wajib diisi."],...}}` |
| 4 | PUT | `/api/books/{id}` | `{"judul":"Clean Code - Updated","penulis":"Robert C. Martin","penerbit":"Prentice Hall","tahun_terbit":2008,"stok":10,"category_id":3}` | `200` `{"data":{"id":4,"judul":"Clean Code - Updated","stok":10,...}}` | `404` (id tidak ada), `422` (validasi gagal) |
| 5 | DELETE | `/api/books/{id}` | - | `200` `{"message":"Buku berhasil dihapus."}` | `404` (id tidak ada) |
| 6 | GET | `/api/members` | - | `200` `{"data":[{"id":1,"nama":"Siti Aminah","nim":"2310501001","status":"aktif",...}],"links":{...},"meta":{"total":13}}` | - |
| 7 | GET | `/api/members/{id}` | - | `200` `{"data":{"id":1,"nama":"Siti Aminah","nim":"2310501001","email":"siti.aminah@pens.ac.id",...}}` | `404` (id tidak ada) |
| 8 | GET | `/api/loans` | - | `200` `{"data":[{"id":5,"member":{"id":1,"nama":"Siti Aminah"},"petugas":{"id":1,"name":"Test User"},"status":"dikembalikan","books":[{"id":4,"judul":"Clean Code"}]}],"meta":{"total":8}}` | - |
| 9 | POST | `/api/loans` | `{"member_id":1,"user_id":1,"tanggal_pinjam":"2026-10-23","tanggal_kembali":"2026-10-30","book_ids":[3,4]}` | `201` `{"data":{"id":8,"status":"dipinjam","tanggal_dikembalikan":null,"books":[{"id":3,"judul":"Bumi Manusia"},{"id":4,"judul":"Clean Code"}]}}` | `422` `{"errors":{"member_id":["Anggota wajib dipilih."],"user_id":["Petugas (user_id) wajib diisi."],"book_ids":["Pilih minimal satu buku."]}}` |
| 10 | PUT | `/api/loans/{id}/kembalikan` | - | `200` `{"data":{"id":8,"status":"dikembalikan","tanggal_dikembalikan":"2026-10-23"}}` | `404` (id tidak ada) |
| 11 | GET | `/api/stats` | - | `200` `{"total_buku":12,"total_anggota":13,"peminjaman_aktif":0}` | - |

**Rangkuman status code:** `200` baca/ubah/hapus sukses · `201` create sukses · `404` resource tidak ada · `422` validasi gagal · `500` error server.

---

## Books

### GET /api/books

**Deskripsi:** List buku dengan pagination (10 per halaman), support filter `?judul=` (pencarian sebagian) dan `?category_id=` (exact match).

**Query Parameters:**
- `judul` (optional): string - filter judul (LIKE)
- `category_id` (optional): integer - filter kategori

**Response 200 OK:**
```json
{
  "data": [
    {
      "id": 3,
      "judul": "Bumi Manusia",
      "penulis": "Pramoedya Ananta Toer",
      "penerbit": "Hasta Mitra",
      "tahun_terbit": 1980,
      "isbn": "9789794330746",
      "stok": 3,
      "sampul": null,
      "category": {
        "id": 2,
        "nama_kategori": "Fiksi"
      },
      "created_at": "2026-09-24T02:59:11.000000Z",
      "updated_at": "2026-09-24T02:59:11.000000Z"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8123/api/books?page=1",
    "last": "http://127.0.0.1:8123/api/books?page=2",
    "prev": null,
    "next": "http://127.0.0.1:8123/api/books?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 10,
    "to": 10,
    "total": 12
  }
}
```

**Contoh Filter:**
```bash
GET /api/books?judul=clean
# Return: 1 buku dengan judul mengandung "clean"

GET /api/books?category_id=2
# Return: buku dengan category_id = 2 (Fiksi)
```

---

### GET /api/books/{id}

**Deskripsi:** Detail satu buku berdasarkan ID.

**Response 200 OK:**
```json
{
  "data": {
    "id": 4,
    "judul": "Clean Code",
    "penulis": "Robert C. Martin",
    "penerbit": "Prentice Hall",
    "tahun_terbit": 2008,
    "isbn": "9780132350884",
    "stok": 7,
    "sampul": null,
    "category": {
      "id": 3,
      "nama_kategori": "Teknologi"
    },
    "created_at": "2026-09-24T02:59:11.000000Z",
    "updated_at": "2026-09-24T02:59:11.000000Z"
  }
}
```

**Response 404 Not Found:**
```json
{
  "message": "No query results for model [App\\Models\\Book] 99999"
}
```

---

### POST /api/books

**Deskripsi:** Tambah buku baru.

**Request Body:**
```json
{
  "judul": "Test API",
  "penulis": "Author Test",
  "penerbit": "Publisher Test",
  "tahun_terbit": 2024,
  "isbn": "1234567890",
  "stok": 5,
  "category_id": 1
}
```

**Validasi Rules:**
- `judul`: required, string, max 200
- `penulis`: required, string, max 100
- `penerbit`: required, string, max 100
- `tahun_terbit`: required, integer, min 1900, max tahun sekarang
- `isbn`: nullable, string, max 20
- `stok`: required, integer, min 0
- `category_id`: required, integer, exists di tabel categories

**Response 201 Created:**
```json
{
  "data": {
    "id": 13,
    "judul": "Test API",
    "penulis": "Author Test",
    "penerbit": "Publisher Test",
    "tahun_terbit": 2024,
    "isbn": "1234567890",
    "stok": 5,
    "sampul": null,
    "category": {
      "id": 1,
      "nama_kategori": "Pendidikan"
    },
    "created_at": "2026-10-23T07:28:00.000000Z",
    "updated_at": "2026-10-23T07:28:00.000000Z"
  }
}
```

**Response 422 Unprocessable Content (body kosong):**
```json
{
  "message": "Judul buku wajib diisi. (and 5 more errors)",
  "errors": {
    "judul": ["Judul buku wajib diisi."],
    "penulis": ["Nama penulis wajib diisi."],
    "penerbit": ["Nama penerbit wajib diisi."],
    "tahun_terbit": ["Tahun terbit wajib diisi."],
    "stok": ["Stok wajib diisi."],
    "category_id": ["Kategori wajib dipilih."]
  }
}
```

---

### PUT /api/books/{id}

**Deskripsi:** Update data buku.

**Request Body:** (sama dengan POST, semua field wajib)

**Response 200 OK:**
```json
{
  "data": {
    "id": 4,
    "judul": "Clean Code - Updated",
    "penulis": "Robert C. Martin",
    "penerbit": "Prentice Hall",
    "tahun_terbit": 2008,
    "isbn": "9780132350884",
    "stok": 10,
    "sampul": null,
    "category": {
      "id": 3,
      "nama_kategori": "Teknologi"
    },
    "created_at": "2026-09-24T02:59:11.000000Z",
    "updated_at": "2026-10-23T07:28:30.000000Z"
  }
}
```

**Response 404:** (jika id tidak ada)

---

### DELETE /api/books/{id}

**Deskripsi:** Hapus buku.

**Response 200 OK:**
```json
{
  "message": "Buku berhasil dihapus."
}
```

**Response 404:** (jika id tidak ada)

---

## Members

### GET /api/members

**Deskripsi:** List anggota dengan pagination (10 per halaman).

**Response 200 OK:**
```json
{
  "data": [
    {
      "id": 1,
      "nama": "Siti Aminah",
      "nim": "2310501001",
      "email": "siti.aminah@pens.ac.id",
      "nomor_telepon": "081234567890",
      "alamat": "Jl. Ketintang Surabaya",
      "status": "aktif",
      "created_at": "2026-09-24T02:58:19.000000Z",
      "updated_at": "2026-09-24T02:59:25.000000Z"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8123/api/members?page=1",
    "last": "http://127.0.0.1:8123/api/members?page=2",
    "prev": null,
    "next": "http://127.0.0.1:8123/api/members?page=2"
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 2,
    "per_page": 10,
    "to": 10,
    "total": 13
  }
}
```

---

### GET /api/members/{id}

**Deskripsi:** Detail satu anggota berdasarkan ID.

**Response 200 OK:**
```json
{
  "data": {
    "id": 1,
    "nama": "Siti Aminah",
    "nim": "2310501001",
    "email": "siti.aminah@pens.ac.id",
    "nomor_telepon": "081234567890",
    "alamat": "Jl. Ketintang Surabaya",
    "status": "aktif",
    "created_at": "2026-09-24T02:58:19.000000Z",
    "updated_at": "2026-09-24T02:59:25.000000Z"
  }
}
```

**Response 404:** (jika id tidak ada)

---

## Loans

### GET /api/loans

**Deskripsi:** List transaksi peminjaman dengan pagination (10 per halaman). Setiap item menyertakan data nested `member`, `petugas`, dan `books` yang dipinjam.

**Response 200 OK:**
```json
{
  "data": [
    {
      "id": 5,
      "member": {
        "id": 1,
        "nama": "Siti Aminah"
      },
      "petugas": {
        "id": 1,
        "name": "Test User"
      },
      "tanggal_pinjam": "2026-10-01",
      "tanggal_kembali": "2026-10-10",
      "tanggal_dikembalikan": "2026-10-01",
      "status": "dikembalikan",
      "books": [
        {
          "id": 4,
          "judul": "Clean Code"
        }
      ]
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8123/api/loans?page=1",
    "last": "http://127.0.0.1:8123/api/loans?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 8
  }
}
```

---

### POST /api/loans

**Deskripsi:** Catat transaksi peminjaman baru (1 transaksi bisa banyak buku).

**Request Body:**
```json
{
  "member_id": 1,
  "user_id": 1,
  "tanggal_pinjam": "2026-10-23",
  "tanggal_kembali": "2026-10-30",
  "book_ids": [3, 4]
}
```

**Validasi Rules:**
- `member_id`: required, integer, exists di tabel members
- `user_id`: required, integer, exists di tabel users (petugas yang mencatat)
- `tanggal_pinjam`: required, date
- `tanggal_kembali`: required, date, after_or_equal tanggal_pinjam
- `book_ids`: required, array, min 1 item
- `book_ids.*`: integer, exists di tabel books

**Response 201 Created:**
```json
{
  "data": {
    "id": 8,
    "member": {
      "id": 1,
      "nama": "Siti Aminah"
    },
    "petugas": {
      "id": 1,
      "name": "Test User"
    },
    "tanggal_pinjam": "2026-10-23",
    "tanggal_kembali": "2026-10-30",
    "tanggal_dikembalikan": null,
    "status": "dipinjam",
    "books": [
      {
        "id": 3,
        "judul": "Bumi Manusia"
      },
      {
        "id": 4,
        "judul": "Clean Code"
      }
    ]
  }
}
```

**Catatan Penting:**
- `status` langsung `"dipinjam"` (bukan `null`) karena `refresh()` dipanggil sebelum response
- `user_id` wajib dikirim di body (berbeda dengan web yang pakai `auth()->id()`)

**Response 422:** (validasi gagal, format sama dengan books)

---

### PUT /api/loans/{id}/kembalikan

**Deskripsi:** Tandai transaksi peminjaman sebagai dikembalikan. Set `status` = `"dikembalikan"` dan `tanggal_dikembalikan` = tanggal hari ini.

**Request Body:** (tidak ada)

**Response 200 OK:**
```json
{
  "data": {
    "id": 8,
    "member": {
      "id": 1,
      "nama": "Siti Aminah"
    },
    "petugas": {
      "id": 1,
      "name": "Test User"
    },
    "tanggal_pinjam": "2026-10-23",
    "tanggal_kembali": "2026-10-30",
    "tanggal_dikembalikan": "2026-10-23",
    "status": "dikembalikan",
    "books": [
      {
        "id": 3,
        "judul": "Bumi Manusia"
      },
      {
        "id": 4,
        "judul": "Clean Code"
      }
    ]
  }
}
```

**Response 404:** (jika id tidak ada)

---

## Stats

### GET /api/stats

**Deskripsi:** Ringkasan statistik aplikasi (total buku, total anggota, peminjaman aktif).

**Response 200 OK:**
```json
{
  "total_buku": 12,
  "total_anggota": 13,
  "peminjaman_aktif": 0
}
```

**Catatan:**
- `peminjaman_aktif` = count loans dengan `status = 'dipinjam'`
- Endpoint ini tidak pakai API Resource (return JSON langsung)

---

## HTTP Status Codes

| Code | Arti | Kapan Muncul |
|---|---|---|
| 200 | OK | GET/PUT/DELETE berhasil |
| 201 | Created | POST berhasil membuat resource baru |
| 404 | Not Found | Resource dengan ID tidak ditemukan |
| 422 | Unprocessable Content | Validasi gagal (body request tidak valid) |
| 500 | Internal Server Error | Error server (bug kode) |

---

## Testing dengan cURL

**GET books:**
```bash
curl -s "http://127.0.0.1:8000/api/books" -H "Accept: application/json"
```

**GET dengan filter:**
```bash
curl -s "http://127.0.0.1:8000/api/books?judul=clean" -H "Accept: application/json"
```

**POST book:**
```bash
curl -X POST "http://127.0.0.1:8000/api/books" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "judul": "Test",
    "penulis": "Author",
    "penerbit": "Publisher",
    "tahun_terbit": 2024,
    "stok": 5,
    "category_id": 1
  }'
```

**POST loan:**
```bash
curl -X POST "http://127.0.0.1:8000/api/loans" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "member_id": 1,
    "user_id": 1,
    "tanggal_pinjam": "2026-10-23",
    "tanggal_kembali": "2026-10-30",
    "book_ids": [3, 4]
  }'
```

**PUT kembalikan:**
```bash
curl -X PUT "http://127.0.0.1:8000/api/loans/8/kembalikan" \
  -H "Accept: application/json"
```

---

**Diverifikasi:** 23 Oktober 2026 08:31 WIB  
**Framework:** Laravel 12  
**Database:** SQLite  
**Total Endpoints:** 11

**Semua endpoint diuji live (curl) - 14/14 PASS:**
- `GET /api/books` → 200 (pagination, 10 item)
- `GET /api/books?judul=clean` → 200 (filter)
- `GET /api/books/{id}` → 200 (detail)
- `GET /api/books/99999` → 404
- `POST /api/books` valid → 201
- `POST /api/books` body `{}` → 422 (6 errors)
- `PUT /api/books/{id}` → 200
- `DELETE /api/books/{id}` → 200
- `GET /api/members` → 200
- `GET /api/members/{id}` → 200
- `GET /api/loans` → 200 (nested member/petugas/books)
- `POST /api/loans` → 201 (`status: "dipinjam"`)
- `PUT /api/loans/{id}/kembalikan` → 200 (`status: "dikembalikan"`, tanggal auto)
- `GET /api/stats` → 200 (`total_buku`, `total_anggota`, `peminjaman_aktif`)
