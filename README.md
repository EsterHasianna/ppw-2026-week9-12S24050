# SIPUS-Del: Sistem Informasi Perpustakaan Kampus Del

Tugas Mandiri Praktikum Minggu 5, Pemrograman & Pengujian Web (12S3101), Institut Teknologi Del.
Dibangun dengan Laravel 11, Eloquent ORM, Blade Component, dan Bootstrap 5.

## Fitur
- CRUD Buku dan Kategori (Route::resource + Implicit Route Model Binding)
- Relasi Eloquent: Kategori hasMany Buku, Buku belongsTo Kategori
- Validasi server-side (ISBN unik, judul minimal 5 karakter, stok minimal 0) dan proteksi CSRF
- Master layout `<x-layout>` bertema Del Purple (#4C1D95), pagination, flash message, `@forelse`
- Seeder dan Factory (5 kategori, 20 buku) dengan Faker
- Audit kueri SQL untuk membuktikan Eager Loading

## Instalasi
1. `git clone <url-repositori>` lalu `cd` ke folder proyek
2. `composer install`
3. `copy .env.example .env`
4. `php artisan key:generate`
5. Pastikan `DB_CONNECTION=sqlite` di `.env`, lalu buat file `database/database.sqlite` jika belum ada
6. `php artisan migrate:fresh --seed`
7. `php artisan serve`, lalu buka http://localhost:8000

## Bukti Eager Loading (N+1)
Listener `DB::listen` di `AppServiceProvider` mencatat setiap kueri ke `storage/logs/laravel.log`.
Cuplikan log tersedia di `docs/sql-audit-eager-loading.log`.

Halaman `/buku` memakai `Buku::with('kategori')->latest()->paginate(10)`:
- 1 kueri `count` (pagination)
- 1 kueri daftar buku
- 1 kueri kategori (`where id in (...)`), bukan 1 kueri per buku

Perbandingan di tinker: lazy loading menghasilkan 20 kueri (1 kueri `bukus` + 19 kueri `kategoris`), sedangkan eager loading (`Buku::with('kategori')->get()`) hanya 2 kueri.

## Struktur
- Model: `app/Models` (Buku, Kategori)
- Controller: `app/Http/Controllers` (BukuController, KategoriController)
- View: `resources/views` (components/layout, buku, kategori)
- Migrasi, Factory, Seeder: `database/`