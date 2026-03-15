<?php
/**
 * Language strings for local_mseb (Indonesian).
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Kuncian Kuis M-SEB';
$string['plugindescription'] = 'M-SEB memastikan pengerjaan kuis aman di Android via aplikasi M-SEB, dengan pengamanan tambahan JS untuk iOS dan PC.';

$string['msebheader'] = 'M-SEB PROCTORING';
$string['mseb_enabled'] = 'AKTIFKAN KUNCIAN M-SEB';
$string['mseb_enabled_desc'] = 'Paksa penggunaan aplikasi M-SEB (Android) atau blokir browser biasa.';
$string['mseb_allowpc'] = 'IZINKAN LAPTOP / PC (Chrome)';
$string['mseb_allowpc_desc'] = 'Izinkan akses melalui Google Chrome di Laptop.';
$string['mseb_protectpc'] = 'AKTIFKAN PENGAMANAN JS DI PC';
$string['mseb_protectpc_desc'] = 'Gunakan Timer Hukuman jika PC pindah tab.';
$string['mseb_allowios'] = 'IZINKAN iOS (SAFARI/CHROME)';
$string['mseb_allowios_desc'] = 'Izinkan iPhone dengan Perlindungan Pro Guard JS (Hukuman Timer).';
$string['mseb_mintime'] = 'MINIMAL WAKTU PENGERJAAN (MENIT)';
$string['mseb_mintime_help'] = 'Siswa tidak bisa mengklik tombol Selesai Ujian sebelum X menit berlalu. Isi 0 untuk mematikan.';
$string['mseb_minanswered'] = 'MINIMAL SOAL TERJAWAB (%)';
$string['mseb_minanswered_help'] = 'Minimal persentase soal yang harus dijawab sebelum tombol submit muncul (0-100).';

$string['js:violation'] = 'PELANGGARAN DETEKSI';
$string['js:violationcount'] = 'Pelanggaran';
$string['js:leavingexam'] = 'Keluar area ujian';
$string['js:autotranslate'] = 'Terjemahan otomatis terdeteksi';
$string['js:penalty_level'] = 'Level Hukuman';
$string['js:sanction_continued'] = 'Sanksi Lanjutan';

$string['blocked_generic'] = 'Kuis ini hanya boleh dikerjakan melalui aplikasi resmi <b>M-SEB</b> (Android).';
$string['blocked_ios_seb'] = 'Untuk iOS (iPhone/iPad), kuis ini harus dikerjakan melalui aplikasi <b>Safe Exam Browser</b>.';
$string['blocked_android'] = 'MAAF! Kuis ini Wajib dikerjakan melalui Aplikasi <b>M-SEB</b>. Anda terdeteksi menggunakan browser biasa.';
$string['blocked_pc'] = 'Kuis ini tidak boleh dikerjakan melalui browser laptop biasa. Gunakan <b>Safe Exam Browser (SEB)</b> atau Aplikasi M-SEB.';
$string['blocked_title'] = 'AKSES DICEKAL';
$string['blocked_back'] = 'KEMBALI';
$string['blocked_locked'] = 'TERKUNCI';
$string['blocked_launch_seb'] = 'BUKA DI SAFE EXAM BROWSER';
