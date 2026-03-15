# M-SEB (Moodle Secure Exam Browser) Quiz Lock

M-SEB adalah plugin Moodle yang dirancang untuk mengamankan pengerjaan kuis di berbagai platform (Android, iOS, dan PC).

## Fitur Utama

- **Android System Lock**: Memaksa penggunaan aplikasi **M-SEB Android** resmi. Memblokir semua browser biasa (Chrome, Samsung, dll).
- **iOS Pro Guard (V1.8)**: Perlindungan tingkat tinggi untuk iPhone/iPad menggunakan metode **Direct JS Injection**. Mendeteksi pindah aplikasi, status bar, dan app switcher dengan sanksi timer hukuman yang berkelanjutan (anti-refresh).
- **PC/Laptop Protection**: Opsional mengizinkan Google Chrome dengan perlindungan JS Guard yang sama dengan versi iOS.
- **Minimum Time Enforcement**: Mencegah siswa mengumpulkan kuis (Submit) sebelum waktu minimal yang ditentukan berlalu.
- **Minimum Answered Questions**: Mencegah pengumpulan kuis jika persentase soal yang dijawab belum mencapai target.
- **Multi-Language Support**: Mendukung Bahasa Indonesia dan Bahasa Inggris secara penuh.

## Update Terbaru (2026-03-15)

1.  **Direct JS Injection**: Menggantikan sistem AMD untuk perlindungan iOS agar aktif seketika (instan) tanpa jeda loading.
2.  **Persistent Penalty**: Sanksi hukuman waktu kini disimpan di `localStorage`. Jika siswa refresh halaman atau tutup browser, waktu hukuman tetap berjalan (tidak reset).
3.  **Refined iOS Logic**: Menggunakan algoritma selisih waktu (`time-diff`) untuk deteksi fokus yang lebih akurat di Safari iOS.
4.  **Localization**: Penyesuaian seluruh teks pengaturan kuis dan pesan error ke dalam Bahasa Indonesia dan Inggris.
5.  **Version Bump**: Update versi plugin ke `2026031500`.

## Instalasi

1.  Ekstrak `mseb.zip`.
2.  Unggah folder `mseb` ke direktori `/local/` di instalasi Moodle Anda.
3.  Masuk ke Moodle sebagai Admin dan lakukan proses upgrade database.
4.  Aktifkan dan atur melalui pengaturan di setiap kuis.

---
© 2024-2026 M-SEB Team.
