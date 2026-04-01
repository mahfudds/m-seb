# M-SEB Plugin Release Notes - Version 2026040101

## 🇮🇩 Bahasa Indonesia

**Pembaruan Fitur Terbaru (V 2026040101):**

1.  **Kontrol Face Recognition (Sisi Server):**
    *   Sekarang Admin/Guru bisa mengaktifkan atau menonaktifkan fitur deteksi wajah langsung melalui pengaturan kuis di Moodle.
    *   Sangat berguna untuk mengakomodasi siswa yang menggunakan HP dengan spesifikasi rendah (RAM 2GB).
2.  **Batas Waktu Navigasi Aman yang Dapat Diatur (Custom Timeout):**
    *   Menambahkan opsi pengaturan **"NAV SAFE TIMEOUT"** di kuis.
    *   Default diatur ke **60 detik** untuk toleransi maksimal terhadap koneksi internet lambat (EDGE) atau server yang sedang sibuk.
3.  **Optimalisasi Perangkat Spek Rendah:**
    *   Aplikasi Android kini lebih cerdas dalam memproses deteksi wajah (hanya 2 frame per detik) untuk menjaga stabilitas RAM dan baterai.
4.  **Skrip Upgrade Database Otomatis:**
    *   Menambahkan `db/upgrade.php` agar pembaruan kolom database berjalan otomatis saat instalasi diperbarui tanpa harus install ulang plugin.

---

## 🇺🇸 English

**Latest Feature Updates (V 2026040101):**

1.  **Face Recognition Toggle (Server-Side):**
    *   Admins/Teachers can now enable or disable the face detection feature directly through the quiz settings in Moodle.
    *   Crucial for accommodating students using low-specification devices (2GB RAM).
2.  **Adjustable Navigation Safety Timeout (Custom Timeout):**
    *   Added **"NAV SAFE TIMEOUT"** setting in the quiz configuration.
    *   Default is set to **60 seconds** to provide maximum tolerance for slow internet connections (EDGE) or busy server responses.
3.  **Low-Spec Device Optimization:**
    *   The Android app now intelligently processes face detection (limiting to 2 frames per second) to maintain RAM stability and save battery.
4.  **Automatic Database Upgrade Script:**
    *   Added `db/upgrade.php` to ensure database column updates run automatically during the version upgrade process without needing to reinstall the plugin.
