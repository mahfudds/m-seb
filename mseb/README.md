# M-SEB (Moodle Secure Exam Browser) Quiz Lock

M-SEB adalah plugin Moodle yang dirancang untuk mengamankan pengerjaan kuis di berbagai platform (Android, iOS, dan PC).

## Fitur Utama

- **Android System Lock**: Memaksa penggunaan aplikasi **M-SEB Android** resmi. Memblokir semua browser biasa (Chrome, Samsung, dll).
- **iOS Pro Guard (V1.8)**: Perlindungan tingkat tinggi untuk iPhone/iPad menggunakan metode **Direct JS Injection**. Mendeteksi pindah aplikasi, status bar, dan app switcher dengan sanksi timer hukuman yang berkelanjutan (anti-refresh).
- **PC/Laptop Protection**: Opsional mengizinkan Google Chrome dengan perlindungan JS Guard yang sama dengan versi iOS.
- **Minimum Time Enforcement**: Mencegah siswa mengumpulkan kuis (Submit) sebelum waktu minimal yang ditentukan berlalu.
- **Minimum Answered Questions**: Mencegah pengumpulan kuis jika persentase soal yang dijawab belum mencapai target.
- **Multi-Language Support**: Mendukung Bahasa Indonesia dan Bahasa Inggris secara penuh.

## Update Terbaru (2026-03-20)

1.  **AMD Build Fix**: Memperbaiki build artifact AMD (`amd/build/`) yang sebelumnya tidak dikompilasi dengan benar melalui pipeline grunt Moodle (Babel + UglifyJS). Build kini menghasilkan file `.min.js` dan `.min.js.map` yang sesuai standar Moodle Prechecker.
2.  **Persistent Penalty**: Sanksi hukuman waktu disimpan di `localStorage` — tetap berjalan meski siswa refresh atau tutup browser.
3.  **Minimum Time & Answered Enforcement**: Mencegah submit kuis sebelum waktu minimal dan persentase jawaban terpenuhi.
4.  **Multi-Language Support**: Mendukung penuh Bahasa Indonesia dan Bahasa Inggris.
5.  **Version Bump**: Update versi plugin ke `2026032001` (release `23.3`).

## Instalasi

1.  Ekstrak `local_mseb_2026032001.zip`.
2.  Unggah folder `mseb` ke direktori `/local/` di instalasi Moodle Anda.
3.  Masuk ke Moodle sebagai Admin dan lakukan proses upgrade database.
4.  Aktifkan dan atur melalui pengaturan di setiap kuis.

---
© 2024-2026 M-SEB Team.
