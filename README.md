# Laravel Import CSV 

Membuat **Artisan Command** untuk mentransformasi data dari file `article.csv` ke dalam skema database Laravel yang terstruktur.

---

## 🛠️ Alur Pengembangan (Workflow)

### 1. Instalasi & Koneksi Database
Inisialisasi proyek Laravel dan konfigurasi environment pada file `.env` untuk memastikan koneksi database terjalin dengan benar.

### 2. Migration Manual (Skema Database)
Karena adanya perbedaan versi MariaDB, proyek ini sepenuhnya menggunakan **Laravel Migrations** untuk menjamin portabilitas skema tanpa bergantung pada SQL dump.

### 3. Eloquent Modeling & Relasi
Pembuatan model untuk setiap tabel sebagai jembatan logika program. Menggunakan relasi **Polymorphic** pada tabel `article_meta` untuk menghubungkan entitas secara dinamis:
* `articles` ↔ `reporters`
* `articles` ↔ `sources`
* `articles` ↔ `tags`



### 4. Custom Artisan Command
Inti dari sistem ini adalah command `import:articles` dengan pipeline:
* **CSV Reading:** Membaca file secara efisien.
* **Index Mapping:** Validasi indeks kolom untuk akurasi data.
* **Data Saving:** Persistensi data ke database menggunakan Eloquent.

---

## 🐞 Penanganan Masalah & Optimasi

| Fitur / Masalah | Solusi Teknis |
| :--- | :--- |
| **JSON Extraction** | Menggunakan *Regex* untuk mengambil data dari format JSON yang tidak konsisten di dalam CSV. |
| **Auto-Slug** | Jika slug kosong, sistem men-generate slug otomatis dari `title` atau `name`. |


---

## ✨  Optional (Bonus)

### 🔗 Polymorphic Metadata
Tabel `article_meta` terisi otomatis untuk memetakan relasi antara artikel dengan banyak entitas sekaligus (*reporters, sources, tags*) sesuai standar [Laravel Polymorphic Docs](https://laravel.com/docs/12.x/eloquent-relationships#polymorphic-relationships).

### 🔄 Idempotency
Perintah bersifat **Idempotent**. Sistem melakukan pengecekan data sebelum proses *insert*. Menjalankan ulang perintah yang sama tidak akan menghasilkan duplikasi data di database.

### 🧹  Normalization
Sistem melakukan pembersihan konten artikel: 
* **Clean Junk Content:** Menghapus blok teks "Baca Juga" dan tag HTML pengganggu lainnya (seperti `<p><strong>...`) untuk menghasilkan data yang bersih.

### 🆔 Unique Identifiers
* **Unique Article ID:** Setiap artikel diberikan 10 karakter unik acak.
* **Default State:** Setiap artikel baru otomatis mendapatkan status `published`.

---

## 💻 Cara Penggunaan

1. **Persiapan:** Jalankan migrasi tabel.
   ```bash
   php artisan migrate
2. **Eksekusi:** Letakkan file article.csv di root folder, lalu jalankan:
   ```bash
   php artisan import:articles article.csv
---   
  
