<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <https://www.gnu.org/licenses/>.

/**
 * Language strings for local_mseb (Indonesian).
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['blocked_android'] = 'MAAF! Kuis ini harus dikerjakan melalui aplikasi resmi <b>M-SEB</b>. Anda terdeteksi menggunakan browser biasa.';
$string['blocked_back'] = 'KEMBALI';
$string['blocked_generic'] = 'Kuis ini hanya boleh dikerjakan melalui aplikasi resmi <b>M-SEB</b> (Android).';
$string['blocked_ios_seb'] = 'Untuk iOS (iPhone/iPad), kuis ini harus dikerjakan melalui aplikasi <b>Safe Exam Browser</b>.';
$string['blocked_launch_seb'] = 'BUKA DI SAFE EXAM BROWSER';
$string['blocked_locked'] = 'TERKUNCI';
$string['blocked_pc'] = 'Kuis ini tidak dapat dikerjakan melalui browser laptop biasa. Silakan gunakan <b>Safe Exam Browser (SEB)</b> atau aplikasi M-SEB.';
$string['blocked_title'] = 'AKSES DIBLOKIR';
$string['error_access_denied'] = 'M-SEB: Akses Ditolak. Token keamanan tidak valid.';
$string['error_quiz_not_found'] = 'M-SEB: Kuis tidak ditemukan.';
$string['js:autotranslate'] = 'Terdeteksi terjemahan otomatis';
$string['js:desktop_mode'] = 'Desktop Mode Terdeteksi! Silakan matikan "Situs Desktop" pada browser Anda atau gunakan aplikasi M-SEB.';
$string['js:is_mobile_blocked'] = 'Perangkat Mobile Terdeteksi! Silakan gunakan aplikasi M-SEB.';
$string['js:leavingexam'] = 'Meninggalkan area ujian';
$string['js:penalty_level'] = 'Tingkat Penalti';
$string['js:sanction_continued'] = 'Sanksi Berlanjut';
$string['js:violation'] = 'PELANGGARAN DETEKSI';
$string['js:violationcount'] = 'Pelanggaran';
$string['mseb_allowios'] = 'IZINKAN iOS (SAFARI/CHROME)';
$string['mseb_allowios_desc'] = 'Izinkan pengguna iPhone dengan proteksi Pro Guard JS (Penalty Timer).';
$string['mseb_allowpc'] = 'IZINKAN LAPTOP / PC (Chrome)';
$string['mseb_allowpc_desc'] = 'Izinkan akses melalui Google Chrome di Laptop.';
$string['mseb_enabled'] = 'AKTIFKAN KUNCI M-SEB';
$string['mseb_enabled_desc'] = 'Wajibkan penggunaan aplikasi M-SEB (Android) atau blokir browser biasa. Jika aktif, mode desktop juga akan diblokir.';
$string['mseb_minanswered'] = 'MINIMUM PERTANYAAN TERJAWAB (%)';
$string['mseb_minanswered_help'] = 'Persentase minimum soal yang harus dijawab sebelum tombol selesai muncul (0-100).';
$string['mseb_mintime'] = 'WAKTU PENGERJAAN MINIMUM (MENIT)';
$string['mseb_mintime_help'] = 'Siswa tidak dapat menyelesaikan kuis sebelum X menit berlalu. Set ke 0 untuk menonaktifkan.';
$string['mseb_protectpc'] = 'AKTIFKAN JS GUARD DI PC';
$string['mseb_protectpc_desc'] = 'Gunakan Penalty Timer jika PC pindah tab.';
$string['msebheader'] = 'M-SEB PROCTORING';
$string['plugindescription'] = 'M-SEB (Moodle Secure Exam Browser) mewajibkan kuis yang aman di Android melalui aplikasi M-SEB, dengan proctoring opsional berbasis JS untuk iOS dan PC.';
$string['pluginname'] = 'Kunci Kuis M-SEB';
$string['privacy:metadata:local_mseb'] = 'Plugin M-SEB hanya menyimpan pengaturan konfigurasi untuk kuis dan tidak menyimpan data pribadi apa pun tentang pengguna.';
