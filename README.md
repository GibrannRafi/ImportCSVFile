Import Data Artikel (CSV to Polymorphic Database)
Proyek ini adalah Artisan Command untuk memproses file article.csv menjadi data terstruktur sesuai skema database.

🛠️ Proses Pengerjaan
Migration Manual: Dikarenakan perbedaan versi MariaDB yang menyebabkan kegagalan import SQL dump, saya membangun ulang skema menggunakan Laravel Migrations untuk menjamin kompatibilitas.

Eloquent Modeling: Membuat model dengan relasi Polymorphic pada tabel article_meta untuk menghubungkan articles dengan reporters dan tags.

Data Processing: Membuat command import:articles yang membaca data per baris, memvalidasi index CSV, dan melakukan pembersihan data secara real-time.

🐞 Kendala & Solusi Teknis
Selama proses pengerjaan, saya menyelesaikan beberapa masalah berikut:

Data JSON Inkonsisten: Data author dan editor di CSV tercampur antara string dan JSON.

Solusi: Menggunakan Regex helper untuk mengekstrak data JSON secara akurat tanpa bergantung pada format string kolom.

Integrity Constraint Violation (Error 1451): Kegagalan saat update data karena script mencoba mengubah UUID yang sudah terikat relasi.

Solusi: Memisahkan logika create dan update. UUID hanya dibuat saat data pertama kali masuk, sedangkan proses selanjutnya hanya memperbarui isi konten.

Normalisasi Konten: Konten masih kotor dengan tag <p>, &nbsp;, dan placeholder ``.

Solusi: Implementasi content cleaning menggunakan strip_tags dan html_entity_decode untuk menghasilkan teks murni sesuai instruksi.

✅ Fitur Utama
Idempotent: Command aman dijalankan berkali-kali tanpa menghasilkan duplikasi data.

Polymorphic Relation: Metadata terisi otomatis pada tabel article_meta.

Auto-Generated Identifiers:

article_id: Random 10 karakter unik.

slug: Otomatis dari title (artikel) atau name (tags/reporter).

status: Default "published".

💻 Cara Penggunaan
Jalankan migrasi:

php artisan migrate

Jalankan perintah import:

php artisan import:articles article.csv
