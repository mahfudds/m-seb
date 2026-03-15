/**
 * M-SEB Pro Guard module — JS-based proctoring for iOS and PC.
 *
 * @module   local_mseb/proguard
 * @package  local_mseb
 * @copyright 2024 M-SEB
 */

import {get_string} from 'core/str';

export const init = async (quizId, isIos) => {
    // Jalankan hanya di halaman kuis.
    if (!/\/mod\/quiz\/(attempt|view)\.php/.test(location.pathname)) return;
    
    // Cegah inisialisasi ganda.
    if (window.MSEB_ACTIVE) return;
    window.MSEB_ACTIVE = true;

    console.log("M-SEB ProGuard: Initializing...");

    // Pre-fetch strings dengan fallback.
    const strings = {
        violation: await get_string('js:violation', 'local_mseb').catch(()=>"Pelanggaran"),
        violationCount: await get_string('js:violationcount', 'local_mseb').catch(()=>"Jumlah Pelanggaran"),
        leavingExam: await get_string('js:leavingexam', 'local_mseb').catch(()=>"Pindah Aplikasi / Tab"),
    };

    const params = new URLSearchParams(location.search);
    const attemptId = params.get('attempt') || '0';
    const storageKey = `mseb_v16_viol_${quizId}_${attemptId}`;
    const penaltyEndTimeKey = `${storageKey}_end`;
    const SLEEP_MS = 15000;

    let navSafe = false;
    let blurAt = 0;
    let hiddenAt = 0;
    let ignoreBlur = false;
    let isPenaltyRunning = false;

    const getV = () => parseInt(localStorage.getItem(storageKey) || '0', 10);
    const setV = (v) => {
        localStorage.setItem(storageKey, String(v));
        const monV = document.getElementById('mseb-vcount');
        if (monV) monV.textContent = v;
        updateMonitorStyle(v);
    };

    const updateMonitorStyle = (v) => {
        const mon = document.getElementById('mseb-monitor');
        if (!mon) return;
        const colors = ['#0a7d00', '#c9a400', '#ff8800', '#ff3300', '#8b0000'];
        mon.style.background = colors[Math.min(v, colors.length - 1)];
    };

    // UI Monitor.
    const createMonitor = () => {
        if (document.getElementById('mseb-monitor')) return;
        const mon = document.createElement('div');
        mon.id = 'mseb-monitor';
        mon.style.cssText = 'position:fixed;top:15px;right:15px;z-index:999999;background:#0a7d00;color:#fff;padding:12px 18px;border-radius:12px;font-size:14px;font-weight:700;pointer-events:none;box-shadow:0 4px 10px rgba(0,0,0,0.3);';
        mon.innerHTML = `${strings.violationCount}: <span id="mseb-vcount">0</span>`;
        document.body.appendChild(mon);
        setV(getV());
    };

    if (document.readyState !== 'loading') createMonitor();
    else document.addEventListener('DOMContentLoaded', createMonitor);

    const addViolation = async (reason) => {
        if (navSafe || isPenaltyRunning) return;
        
        console.warn("M-SEB Violation:", reason);
        const v = getV() + 1;
        setV(v);

        // Simpan waktu akhir penalti.
        const duration = v * 60 * 1000;
        const endTime = Date.now() + duration;
        localStorage.setItem(penaltyEndTimeKey, String(endTime));

        showPenalty(v * 60, endTime, reason);
    };

    const showPenalty = async (seconds, endTime, reason) => {
        if (isPenaltyRunning) return;
        isPenaltyRunning = true;

        const overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(211,47,47,0.98);color:white;z-index:9999999;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px;font-family:sans-serif;';
        overlay.innerHTML = `
            <div style="font-size:70px;margin-bottom:10px;">🚨</div>
            <h2 style="font-size:24px;margin-bottom:10px;">${strings.violation}</h2>
            <p style="margin-bottom:20px;">${reason}<br>Penalti aktif karena keluar dari area ujian.</p>
            <div style="font-size:50px;font-weight:bold;background:white;color:#d32f2f;padding:10px 30px;border-radius:15px;">
                <span id="p-min">00</span>:<span id="p-sec">00</span>
            </div>
        `;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const timer = setInterval(() => {
            const remain = Math.ceil((endTime - Date.now()) / 1000);
            if (remain <= 0) {
                clearInterval(timer);
                overlay.remove();
                document.body.style.overflow = '';
                isPenaltyRunning = false;
                localStorage.removeItem(penaltyEndTimeKey);
                return;
            }
            const m = Math.floor(remain / 60);
            const s = remain % 60;
            document.getElementById('p-min').textContent = String(m).padStart(2, '0');
            document.getElementById('p-sec').textContent = String(s).padStart(2, '0');
        }, 1000);
    };

    // Deteksi Pindah Tab / Minimalize.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
            ignoreBlur = true;
            if (!navSafe) addViolation(strings.leavingExam);
        } else {
            const diff = Date.now() - hiddenAt;
            hiddenAt = 0;
            setTimeout(() => { ignoreBlur = false; }, 1000);
        }
    });

    // Deteksi Pindah Aplikasi (Logic Bapak yang Jalan).
    window.addEventListener('blur', () => {
        if (ignoreBlur) return;
        blurAt = Date.now();
    });

    window.addEventListener('focus', () => {
        if (!blurAt) return;
        const diff = Date.now() - blurAt;
        if (diff > 800 && !navSafe) {
            addViolation(strings.leavingExam);
        }
        blurAt = 0;
    });

    // Heartbeat untuk iOS App Switcher.
    setInterval(() => {
        if (!document.hasFocus() && !navSafe && !ignoreBlur && !isPenaltyRunning) {
            addViolation(strings.leavingExam);
        }
    }, 2500);

    // Navigasi aman.
    document.addEventListener('click', (e) => {
        const t = e.target.closest('a, button, input[type="submit"], .qnbutton');
        if (t) {
            navSafe = true;
            setTimeout(() => { navSafe = false; }, 1000);
        }
    }, true);

    // Cek apakah kuis harus lanjut penalti dari refresh sebelumnya.
    const savedEnd = parseInt(localStorage.getItem(penaltyEndTimeKey) || '0', 10);
    if (savedEnd > Date.now()) {
        showPenalty(Math.ceil((savedEnd - Date.now())/1000), savedEnd, "Melanjutkan Penalti");
    }

    // Blokir interaksi.
    document.addEventListener('contextmenu', e => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach(ev => document.addEventListener(ev, e => e.preventDefault()));
};
