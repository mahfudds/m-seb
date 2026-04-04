# Release Notes v23.9 (2026040401)

**Tanggal:** 4 April 2026  
**Focus:** Fix False-Positive Violation Detection saat Pindah Soal

## Masalah yang Diperbaiki

HP siswa terdeteksi pelanggaran (violation) saat pindah soal, padahal tidak melakukan kecurangan. Terutama terjadi pada HP dengan spesifikasi rendah (RAM 2-3 GB) dan koneksi internet lambat.

## Perubahan

### Plugin Moodle (`proguard.js`)

#### 1. Debounce pada Visibility Change
- **Sebelum:** `visibilitychange` ke `hidden` langsung trigger violation
- **Sesudah:** Ditambahkan delay 2 detik. Jika halaman kembali `visible` sebelum 2 detik (misal karena sedang loading soal baru), violation dibatalkan
- **Alasan:** WebView yang sedang load halaman bisa memicu `visibilitychange` secara internal

#### 2. Debounce pada Focus Polling  
- **Sebelum:** Pengecekan `document.hasFocus()` setiap 1.5 detik langsung trigger violation
- **Sesudah:** Butuh 3x berturut-turut unfocused (total 4.5 detik) sebelum trigger violation
- **Alasan:** HP lambat bisa kehilangan focus sesaat saat transisi halaman

#### 3. Selector Click Handler Diperluas
- **Sebelum:** Hanya `a, button, input[type="submit"], .qnbutton, label`
- **Sesudah:** Ditambahkan `[role="button"], .mod_quiz-next-nav, .submitbtns, .btn, [data-action], .nav-link, .page-link`
- **Alasan:** Beberapa element navigasi Moodle (terutama Moodle 4.x) tidak menggunakan tag standar, sehingga grace period panjang (120 detik) tidak aktif

### Aplikasi Android (`ExamActivity.kt`)

#### 4. Debounce pada onPause() Violation
- **Sebelum:** `onPause()` langsung trigger `APP_SWITCHING` violation
- **Sesudah:** Violation di-delay 3 detik via `Handler.postDelayed()`. Jika `onResume()` terpanggil sebelum 3 detik (artinya bukan benar-benar keluar app), violation otomatis dibatalkan
- **Alasan:** Beberapa HP Android (Xiaomi, Samsung low-end, Realme) memicu `onPause()` saat system dialog muncul atau WebView sedang memproses navigasi berat

## Versi

- Plugin: `2026040401` / Release `23.9`
- App: versionCode `57` / versionName `23.9`

## File yang Diubah

- `mseb/amd/src/proguard.js`
- `mseb/amd/build/proguard.min.js`
- `mseb/version.php`
- `app/src/main/java/com/moodle/seb/presentation/ExamActivity.kt`
- `app/build.gradle`
