div align="center">Laravel Article Importer</div>

<p align="center"><strong>CSV to Polymorphic Database</strong></p>

Proyek ini adalah sistem Artisan Command khusus untuk memproses file article.csv menjadi data terstruktur dalam skema database Laravel. Sistem ini menangani normalisasi data, pembersihan konten, dan hubungan database polimorfik.

🛠️ Proses & Arsitektur

1. Migration Manual

Mengingat adanya perbedaan versi MariaDB yang sering menyebabkan kegagalan pada SQL dump tradisional, proyek ini menggunakan Laravel Migrations. Hal ini menjamin kompatibilitas penuh dan integritas skema terlepas dari versi database yang digunakan.

2. Eloquent Modeling & Polimorfisme

Menggunakan relasi Polymorphic pada tabel article_meta. Tabel ini berfungsi sebagai jembatan fleksibel untuk menghubungkan:

<ul>
<li><code>articles</code> ↔ <code>reporters</code></li>
<li><code>articles</code> ↔ <code>tags</code></li>
</ul>

3. Data Processing Pipeline

Command import:articles bekerja dengan alur berikut:

📑 Stream Reading: Membaca data CSV baris demi baris (memory efficient).

🔍 Validation: Validasi indeks kolom untuk mencegah error undefined offset.

🧹 Sanitization: Pembersihan data secara real-time sebelum proses persistensi.

🐞 Kendala & Solusi Teknis

Masalah

Solusi

Data JSON Inkonsisten

Menggunakan Regex helper untuk mendeteksi dan mengekstrak data JSON secara akurat tanpa bergantung pada format string kolom.

Konten Kotor

Implementasi content cleaning menggunakan strip_tags() dan html_entity_decode() untuk menghasilkan teks murni sesuai spesifikasi.

✨ Fitur Utama (Bonus)

✅ Idempotent: Command aman dijalankan berkali-kali tanpa duplikasi data.

🔗 Auto-Generated Metadata: Tabel article_meta terisi otomatis via Eloquent.

🆔 Unique Article ID: 10 karakter unik acak untuk setiap artikel.

🏷️ Auto-Slug: Slug otomatis menggunakan Str::slug() dari judul atau nama.

🚀 Default State: Status artikel otomatis diset ke published.

💻 Cara Penggunaan

Persiapan Database
Pastikan konfigurasi .env sudah benar, lalu jalankan migrasi:

php artisan migrate


Eksekusi Import
Letakkan file article.csv di direktori root proyek, lalu jalankan:

php artisan import:articles article.csv


<div align="center">
<sub>Dikembangkan sebagai solusi automasi data artikel yang robust dan scalable.</sub>
</div>
