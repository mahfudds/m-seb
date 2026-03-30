# M-SEB (Moodle Secure Exam Browser) Quiz Lock

M-SEB adalah plugin Moodle yang dirancang untuk mengamankan pengerjaan kuis di berbagai platform (Android, iOS, dan PC).

## Fitur Utama

- **Android System Lock**: Memaksa penggunaan aplikasi **M-SEB Android** resmi. Memblokir semua browser biasa (Chrome, Samsung, dll).
- **iOS Pro Guard (V1.8)**: Perlindungan tingkat tinggi untuk iPhone/iPad menggunakan metode **Direct JS Injection**. Mendeteksi pindah aplikasi, status bar, dan app switcher dengan sanksi timer hukuman yang berkelanjutan (anti-refresh).
- **PC/Laptop Protection**: Opsional mengizinkan Google Chrome dengan perlindungan JS Guard yang sama dengan versi iOS.
- **Minimum Time Enforcement**: Mencegah siswa mengumpulkan kuis (Submit) sebelum waktu minimal yang ditentukan berlalu.
- **Minimum Answered Questions**: Mencegah pengumpulan kuis jika persentase soal yang dijawab belum mencapai target.
- **Multi-Language Support**: Mendukung Bahasa Indonesia dan Bahasa Inggris secara penuh.

## Update Terbaru (2026-03-31)

1.  **Desktop Mode Detection**: Menambahkan deteksi cerdas untuk memblokir "Situs Desktop" (Desktop Mode) pada browser mobile yang mencoba melewati proteksi M-SEB.
2.  **Independent Pro Guard**: Modul JS Guard (Pro Guard) kini dapat tetap aktif meskipun Kunci M-SEB (Android Lock) dinonaktifkan, selama proteksi JS diizinkan.
3.  **UI/UX Improvement**: Halaman peringatan pemblokiran yang lebih jelas dan informatif bagi siswa.
4.  **Version Bump**: Update versi plugin ke `2026033101` (release `23.5`).

## Instalasi

1.  Ekstrak `local_mseb_2026033101.zip`.
2.  Unggah folder `mseb` ke direktori `/local/` di instalasi Moodle Anda.
3.  Masuk ke Moodle sebagai Admin dan lakukan proses upgrade database.
4.  Aktifkan dan atur melalui pengaturan di setiap kuis.

---
© 2024-2026 M-SEB Team.
