# Skenario Demo UAS - Sistem Perpustakaan Digital Kampus

Panduan urutan demo untuk penilaian project. Estimasi 8-10 menit.

## Persiapan Sebelum Demo

1. Reset database ke kondisi demo:

   ```bash
   php artisan migrate:fresh --seed
   ```

   Hasil: 3 user, 5 kategori, 20 buku, 15 anggota, 10 peminjaman (2 berstatus `dipinjam`).

2. Nyalakan **dua** server (wajib - Dashboard & Laporan memanggil API internal lewat `Http::get()`):

   ```bash
   # Terminal 1 - untuk browser
   php artisan serve --port=8000

   # Terminal 2 - khusus dipanggil dari dalam aplikasi sendiri
   php artisan serve --port=8011
   ```

3. Buka `http://127.0.0.1:8000`, siapkan akun:

   | Role | Email | Password |
   |---|---|---|
   | Admin | admin@pens.ac.id | password |
   | Petugas | petugas1@pens.ac.id | password |

4. Buka juga Postman untuk bagian terakhir.

## Urutan Demo

### 1. Login

- Buka `/login`, login `admin@pens.ac.id` / `password`.
- Poin bicara: password disimpan sebagai hash (bukan plain text), redirect setelah login menuju **Dashboard**, nama petugas ("Bambang Sutrisno") tampil di navbar.
- Percobaan salah password dulu untuk menunjukkan validasi error.

### 2. Dashboard

- Halaman `/` menampilkan tiga kartu: **Total Buku 20**, **Total Anggota 15**, **Peminjaman Aktif 2**.
- Poin bicara: angka diambil dari `GET /api/stats` lewat Laravel HTTP Client di `DashboardController`, bukan query Eloquent langsung. Lihat `http://127.0.0.1:8011/api/stats` di browser untuk membuktikan sumber angkanya sama persis.

### 3. Tambah Kategori

- Menu **Kategori** (hanya muncul untuk role admin - tunjukkan bahwa petugas tidak punya menu ini).
- Tambah kategori baru, misalnya `Filsafat` dengan deskripsi singkat.
- Poin bicara: middleware `admin` membatasi route kategori, jadi bukan cuma menu yang disembunyikan.

### 4. Tambah Buku

- Menu **Buku** -> `+ Tambah Buku`. Isi: judul, penulis, penerbit, tahun terbit, ISBN, stok, kategori (pilih `Filsafat` yang baru dibuat).
- Percobaan submit tanpa mengisi form dulu untuk menunjukkan pesan validasi.
- Poin bicara: `category_id` adalah foreign key ke tabel `categories`; data 20 buku awal berasal dari `BookFactory` dengan judul dan penerbit yang dikurasi manual (bukan teks acak).

### 5. Tambah Anggota

- Menu **Anggota** -> `+ Tambah Anggota`. Isi nama, NIM, email `@pens.ac.id`, nomor telepon, alamat, status `aktif`.
- Poin bicara: 15 anggota awal dibuat `MemberFactory` dengan `APP_FAKER_LOCALE=id_ID`, jadi nama dan alamat bergaya Indonesia.

### 6. Buat Peminjaman

- Menu **Peminjaman** -> `+ Tambah Peminjaman`.
- Pilih anggota, isi tanggal pinjam (hari ini) dan tanggal kembali (misalnya +7 hari), pilih **beberapa** buku sekaligus.
- Poin bicara: satu transaksi bisa memuat banyak buku lewat tabel pivot `loan_items` (relasi many-to-many dengan data tambahan) - bukan satu baris per buku.

### 7. Lihat Daftar Peminjaman Aktif

- Menu **Peminjaman**, tunjukkan transaksi yang baru dibuat berstatus `dipinjam`.
- Poin bicara: badge status (`dipinjam`/`dikembalikan`/`terlambat`), dan pagination di bawah tabel tampil rapi berupa teks/link karena view pagination bawaan Tailwind sudah diganti dengan versi tanpa Tailwind.
- Tunjukkan juga `/books?page=2` untuk membuktikan pagination benar-benar jalan (data 20 buku = 2 halaman).

### 8. Proses Pengembalian

- Pada baris transaksi yang masih `dipinjam`, klik **Kembalikan**.
- Poin bicara: status berubah jadi `dikembalikan` dan `tanggal_dikembalikan` terisi otomatis dengan tanggal hari ini.

### 9. Tampilkan Laporan

- Klik tombol **Lihat Laporan Peminjaman** di Dashboard, atau menu **Laporan** (`/loans/report`).
- Tunjukkan tabel berisi ID, anggota, petugas, daftar buku, tanggal, dan status.
- Poin bicara: halaman ini **tidak** query database langsung - datanya dari `GET /api/loans` (JSON pagination) sehingga tampilan hanya mengonsumsi array, bukan objek Eloquent.
- Demo tahan banting: matikan server port `8011` lalu refresh Dashboard dan Laporan. **Kedua halaman tetap tampil** (statistik `0`, tabel kosong), bukan error 500, karena `try/catch ConnectionException`. Nyalakan lagi server `8011`, refresh, angka kembali normal.

### 10. Demo API di Postman

Semua endpoint publik (tanpa token), method dan URL:

| Method | URL | Keterangan |
|---|---|---|
| GET | `http://127.0.0.1:8011/api/books` | daftar buku (paginated) |
| GET | `http://127.0.0.1:8011/api/books/1` | detail satu buku |
| POST | `http://127.0.0.1:8011/api/books` | tambah buku (JSON body) |
| PUT | `http://127.0.0.1:8011/api/books/1` | ubah buku |
| DELETE | `http://127.0.0.1:8011/api/books/1` | hapus buku |
| GET | `http://127.0.0.1:8011/api/members` | daftar anggota |
| GET | `http://127.0.0.1:8011/api/members/1` | detail anggota |
| GET | `http://127.0.0.1:8011/api/loans` | daftar peminjaman + relasi |
| POST | `http://127.0.0.1:8011/api/loans` | buat peminjaman |
| PUT | `http://127.0.0.1:8011/api/loans/1/kembalikan` | proses pengembalian |
| GET | `http://127.0.0.1:8011/api/stats` | statistik ringkas |

Contoh body `POST /api/books` (header `Content-Type: application/json`):

```json
{
  "judul": "Filsafat Ilmu",
  "penulis": "Jujun S. Suriasumantri",
  "penerbit": "Pustaka Sinar Harapan",
  "tahun_terbit": 2016,
  "isbn": "9789794161234",
  "stok": 3,
  "category_id": 1
}
```

Contoh body `POST /api/loans`:

```json
{
  "member_id": 1,
  "user_id": 1,
  "tanggal_pinjam": "2026-11-05",
  "tanggal_kembali": "2026-11-12",
  "book_ids": [1, 2]
}
```

Poin bicara: response memakai API Resource sehingga bentuknya konsisten (`data` + `meta` untuk pagination), dan endpoint yang sama dipakai oleh halaman Blade maupun klien luar - satu sumber data, banyak konsumen.

## Kalau Waktu Tersisa

- Tunjukkan `routes/web.php` dan `routes/api.php` berdampingan: route web pakai middleware `auth`/`admin`, route API terbuka.
- Tunjukkan `DatabaseSeeder` untuk menjelaskan urutan seeding yang wajib (User -> Category -> Book -> Member -> Loan) agar foreign key tidak kosong.
