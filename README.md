# App Perpustakaan

## Deskripsi

App Perpustakaan adalah aplikasi Sistem Perpustakaan Digital Kampus yang digunakan untuk membantu pengelolaan data buku, anggota, dan transaksi peminjaman.

Project ini dibuat menggunakan Laravel 12 sebagai bagian dari pembelajaran framework PHP (tugas mata kuliah Pemrograman Berbasis Framework).

## Tujuan Aplikasi

Tujuan aplikasi ini adalah mempermudah pengelolaan aktivitas perpustakaan secara digital, mulai dari pengelolaan buku, data anggota, hingga proses peminjaman dan pengembalian buku.

## Fitur

- Autentikasi login/logout dengan dua role: `admin` dan `petugas` (password di-hash).
- CRUD Kategori (khusus admin), Buku, Anggota, dan Peminjaman.
- Satu transaksi peminjaman bisa memuat banyak buku (tabel pivot `loan_items`).
- Proses pengembalian buku dengan status `dipinjam`, `dikembalikan`, dan `terlambat`.
- Dashboard statistik dan halaman Laporan Peminjaman.
- REST API (`/api/books`, `/api/members`, `/api/loans`, `/api/stats`) dengan API Resource.

## Cara Menjalankan Project

### 1. Clone repository

```bash
git clone https://github.com/hawalinajwan/app-perpustakaan.git
cd app-perpustakaan
```

### 2. Install dependency

```bash
composer install
```

### 3. Salin file environment dan generate key

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Konfigurasi database

Atur konfigurasi database pada file `.env`. Contoh untuk MySQL:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_perpustakaan
DB_USERNAME=root
DB_PASSWORD=
```

Atau pakai SQLite (paling cepat, tanpa server database):

```env
DB_CONNECTION=sqlite
```

lalu buat file `database/database.sqlite` kosong.

### 5. Konfigurasi API internal

Dashboard dan Laporan Peminjaman mengambil data lewat HTTP Client (`Http::get()`) ke instance server kedua, bukan query database langsung. Variabel ini **wajib** ada di `.env`:

```env
INTERNAL_API_URL=http://127.0.0.1:8011
```

Port-nya harus berbeda dari port server utama. Nilainya dibaca lewat `config/services.php` -> `internal_api.base_url`.

### 6. Migrate dan seed database

```bash
php artisan migrate --seed
```

Untuk reset bersih ke kondisi data demo:

```bash
php artisan migrate:fresh --seed
```

Seeder mengisi: 3 user, 5 kategori, 20 buku, 15 anggota, dan 10 peminjaman. Faker dikonfigurasi `APP_FAKER_LOCALE=id_ID` sehingga nama/alamat bergaya Indonesia.

Akun hasil seeding:

| Role | Email | Password |
|---|---|---|
| Admin | admin@pens.ac.id | password |
| Petugas | petugas1@pens.ac.id | password |
| Petugas | petugas2@pens.ac.id | password |

### 7. Jalankan aplikasi (dua server)

`php artisan serve` memproses satu request pada satu waktu. Karena aplikasi memanggil API miliknya sendiri, self-request ke port yang sama akan **deadlock** (halaman hang lalu timeout). Karena itu jalankan dua instance di terminal terpisah:

```bash
# Terminal 1 - melayani browser
php artisan serve --port=8000

# Terminal 2 - menerima panggilan API internal dari aplikasi
php artisan serve --port=8011
```

Buka `http://127.0.0.1:8000`, login dengan akun di atas.

Kalau server port `8011` tidak dinyalakan, aplikasi tetap jalan: Dashboard menampilkan angka `0` dan Laporan menampilkan tabel kosong (ditangani `try/catch ConnectionException`), bukan error 500.

Di server produksi (Nginx + PHP-FPM atau Laravel Octane) masalah ini tidak muncul karena request diproses paralel, dan `INTERNAL_API_URL` cukup diarahkan ke domain aplikasi.

## Struktur Penting

```text
app/Http/Controllers/          Controller web (Dashboard, Book, Member, Loan, Category, Auth)
app/Http/Controllers/Api/      Controller REST API (Book, Member, Loan, Stats)
app/Http/Resources/            Bentuk response JSON API
app/Http/Requests/             Validasi form (StoreBookRequest, dsb.)
database/seeders/              UserSeeder, CategorySeeder, BookSeeder, MemberSeeder, LoanSeeder
database/factories/            BookFactory, MemberFactory, UserFactory
resources/views/               Blade: dashboard, books, categories, members, loans, loans/report
routes/web.php                 Route web (middleware auth & admin)
routes/api.php                 Route REST API (terbuka tanpa token)
```

## API

Semua endpoint mengembalikan JSON, terbuka tanpa token (cocok untuk demo Postman).

| Method | URL | Keterangan |
|---|---|---|
| GET | `/api/books` | daftar buku (paginated, filter `judul` & `category_id`) |
| GET | `/api/books/{id}` | detail buku |
| POST | `/api/books` | tambah buku |
| PUT | `/api/books/{id}` | ubah buku |
| DELETE | `/api/books/{id}` | hapus buku |
| GET | `/api/members` | daftar anggota |
| GET | `/api/members/{id}` | detail anggota |
| GET | `/api/loans` | daftar peminjaman beserta relasi |
| POST | `/api/loans` | buat peminjaman |
| PUT | `/api/loans/{id}/kembalikan` | proses pengembalian |
| GET | `/api/stats` | statistik ringkas (`total_buku`, `total_anggota`, `peminjaman_aktif`) |

## Skenario Demo

Urutan demo untuk penilaian ada di [`DEMO.md`](./DEMO.md).

## Pemahaman MVC

- **Model:** Mengurus **data** dan berhubungan langsung dengan *database*.
- **View:** Mengurus **tampilan** (UI) yang dilihat oleh pengguna.
- **Controller:** Menjadi **penghubung**; menerima perintah dari *View*, meminta *Model* memproses data, lalu mengirimkan hasilnya kembali ke *View*.
