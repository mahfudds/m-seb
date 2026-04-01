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
 * M-SEB Pro Guard module — JS-based proctoring (Ultra Precise Version)
 *
 * @module   local_mseb/proguard
 * @package
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string as getString} from 'core/str';

/**
 * Initialise the ProGuard enforcement.
 *
 * @param {number} quizid The quiz ID.
 * @param {boolean} isios Is iOS device.
 * @param {boolean} ismseb Is M-SEB app.
 * @param {boolean} msebenabled Is M-SEB Lock enabled.
 * @param {boolean} facerecognition Is Face Recognition enabled.
 * @param {number} navsafetimeout Navigation safety timeout in seconds.
 */
export const init = async (quizid, isios, ismseb, msebenabled, facerecognition, navsafetimeout) => {
    if (ismseb && window.Android) {
        if (window.Android.setFaceRecognition) {
            // Explicitly cast to boolean for the Android Bridge
            const enabled = (facerecognition === true || facerecognition === 1 || facerecognition === "1");
            window.Android.setFaceRecognition(enabled);
            console.log("M-SEB: Face Recognition Bridge call: " + enabled);
        }
        if (window.Android.setNavSafeTimeout) {
            window.Android.setNavSafeTimeout(parseInt(navsafetimeout || 60, 10));
        }
    }

    // 1. Instant Check.
    const path = location.pathname;
    if (!/\/mod\/quiz\/(attempt|view|summary)\.php/.test(path)) {
        return;
    }
    const isAttemptPage = /\/mod\/quiz\/(attempt|summary)\.php/.test(path);
    
    if (window.MSEB_V17_ACTIVE) {
        return;
    }
    window.MSEB_V17_ACTIVE = true;

    // A. Desktop Mode Detection & Mobile Bypass Prevention.
    const checkBypass = async () => {
        if (ismseb) return;
        
        let isMobileDevice = false;
        let isDesktopMode = false;

        // Detection Heuristics.
        const ua = navigator.userAgent;
        const platform = navigator.platform;
        const touches = navigator.maxTouchPoints || 0;

        // 1. UserAgentData (Most reliable for modern Chrome).
        if (navigator.userAgentData) {
            isMobileDevice = navigator.userAgentData.mobile;
            const platformName = navigator.userAgentData.platform;
            // If UAData says NOT mobile but platform is Android/iOS, it's Desktop Mode.
            if (!isMobileDevice && (platformName === 'Android' || platformName === 'iOS')) {
                isDesktopMode = true;
            }
        }

        // 2. Fallbacks for older browsers or "Desktop Mode" trickery.
        if (!isMobileDevice && !isDesktopMode) {
            // Android Desktop Mode usually reports "Linux x86_64" in UA, but has touches.
            if (touches > 1 && /Linux/i.test(platform)) {
                isMobileDevice = true;
                isDesktopMode = true;
            }
            // iPad Desktop Mode reports "MacIntel" but has touches.
            if (touches > 2 && /Macintosh/i.test(ua)) {
                isMobileDevice = true;
                isDesktopMode = true;
            }
        }

        // Action.
        if (msebenabled && (isMobileDevice || isDesktopMode)) {
            const strDesktop = await getString('js:desktop_mode', 'local_mseb');
            const strMobile = await getString('js:is_mobile_blocked', 'local_mseb');
            
            document.body.innerHTML = `
                <div style="background:#111;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;">
                    <div style="border:3px solid #e00;padding:40px;border-radius:15px;max-width:450px;background:#222;box-shadow:0 10px 30px rgba(224,60,49,0.4);">
                        <h1 style="color:#e03c31;margin-bottom:20px;font-size:28px;">🛑 AKSES DIBLOKIR</h1>
                        <p style="line-height:1.6;color:#ddd;margin-bottom:30px;font-size:16px;">${isDesktopMode ? strDesktop : strMobile}</p>
                        <a href='javascript:location.reload()' style='background:#e03c31;color:#fff;padding:12px 30px;border-radius:5px;text-decoration:none;font-weight:bold;display:inline-block;'>MUAT ULANG HALAMAN</a>
                    </div>
                </div>
            `;
            throw new Error("M-SEB: Mobile bypass detected and blocked.");
        }
    };

    await checkBypass();

    // Load strings.
    const strViolation = await getString('js:violation', 'local_mseb');
    const strCount = await getString('js:violationcount', 'local_mseb');
    const strLeaving = await getString('js:leavingexam', 'local_mseb');
    const strPenaltyLevel = await getString('js:penalty_level', 'local_mseb');
    const strContinued = await getString('js:sanction_continued', 'local_mseb');

    // State.
    const attemptid = new URLSearchParams(location.search).get('attempt') || '0';
    const storagekey = `mseb_v17_viol_${quizid}_${attemptid}`;
    const penaltykey = `${storagekey}_end`;
    // Initial grace period for page load settling.
    let navsafe = true;
    setTimeout(() => {
        navsafe = false;
        console.log("M-SEB: Initial grace period ended.");
    }, 10000); // 10 seconds of safety on page start

    let blurat = 0;
    let ignoreblur = false;
    let ispenaltyshowing = false;

    // Helper Storage.
    const getv = () => (isAttemptPage ? parseInt(localStorage.getItem(storagekey) || '0', 10) : 0);

    /**
     * Update the monitor UI with violation count.
     *
     * @param {number} v violation count.
     */
    const updatemonitorstyle = (v) => {
        const mon = document.getElementById('mseb-monitor');
        if (!mon) {
            return;
        }
        const cols = ['#0a7d00', '#c9a400', '#ff8800', '#ff3300', '#8b0000'];
        mon.style.background = cols[Math.min(v, 4)];
    };

    const setv = (v) => {
        localStorage.setItem(storagekey, String(v));
        const el = document.getElementById('mseb-v-count');
        if (el) {
            el.textContent = v;
        }
        updatemonitorstyle(v);
    };

    /**
     * Show penalty overlay.
     *
     * @param {number} endtime unix timestamp.
     * @param {string} reason reason text.
     */
    const showpenaltyoverlay = (endtime, reason) => {
        if (ispenaltyshowing) {
            return;
        }
        ispenaltyshowing = true;

        const overlay = document.createElement('div');
        overlay.id = "mseb-penalty";
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(211,47,47,1);color:white;z-index:9999999;' +
            'display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;' +
            'padding:20px;font-family:sans-serif;';
        overlay.innerHTML = `
            <div style="font-size:60px;">🚨</div>
            <h2 style="font-size:24px;margin:15px 0;">${strViolation}</h2>
            <p style="font-size:16px;max-width:300px;margin-bottom:20px;">
                ${reason}<br><br>
                ${strPenaltyLevel}: ${getv()}
            </p>
            <div style="font-size:45px;font-weight:bold;background:white;color:#d32f2f;padding:10px 30px;border-radius:15px;">
                <span id="p-min">00</span>:<span id="p-sec">00</span>
            </div>
        `;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const utimer = () => {
            const rem = Math.ceil((endtime - Date.now()) / 1000);
            if (rem <= 0) {
                clearInterval(ptimer);
                overlay.remove();
                document.body.style.overflow = '';
                ispenaltyshowing = false;
                localStorage.removeItem(penaltykey);
                return;
            }
            const m = Math.floor(rem / 60);
            const s = rem % 60;
            document.getElementById('p-min').textContent = String(m).padStart(2, '0');
            document.getElementById('p-sec').textContent = String(s).padStart(2, '0');
        };
        const ptimer = setInterval(utimer, 1000);
        utimer();
    };

    const triggerviolation = (reason) => {
        if (navsafe || ispenaltyshowing) {
            return;
        }

        const v = getv() + 1;
        setv(v);

        const duration = v * 60 * 1000;
        const endtime = Date.now() + duration;
        localStorage.setItem(penaltykey, String(endtime));

        showpenaltyoverlay(endtime, reason);
    };

    // 2. ATTACH LISTENERS.

    // Visibility Change.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            ignoreblur = true;
            if (!navsafe) {
                triggerviolation(strLeaving);
            }
        } else {
            setTimeout(() => {
                ignoreblur = false;
            }, 1000);
        }
    });

    // Blur & Focus.
    window.addEventListener('blur', () => {
        if (ignoreblur) {
            return;
        }
        blurat = Date.now();
    });

    window.addEventListener('focus', () => {
        if (!blurat) {
            return;
        }
        const diff = Date.now() - blurat;
        if (diff > 800 && !navsafe) {
            triggerviolation(strLeaving);
        }
        blurat = 0;
    });

    // Heartbeat.
    setInterval(() => {
        if (!document.hasFocus() && !navsafe && !ignoreblur && !ispenaltyshowing) {
            triggerviolation(strLeaving);
        }
    }, 1500);

    // Nav Safe (Legit Klik).
    let longNavSafeActive = false;
    document.addEventListener('click', (e) => {
        // Mode 1: Singkat (Untuk interaksi biasa — misal: Klik gambar, pilih jawaban)
        navsafe = true;
        if (ismseb && window.Android && window.Android.triggerNavSafe) {
            window.Android.triggerNavSafe();
        }
        
        // Timer singkat (10 detik) untuk interaksi biasa
        setTimeout(() => {
            // Hanya kembalikan ke false jika tidak ada navsafe panjang yang aktif
            if (!longNavSafeActive) {
                navsafe = false;
            }
        }, 10000);

        // Mode 2: Panjang (Untuk navigasi soal — klik tombol/link)
        const t = e.target.closest('a, button, input[type="submit"], .qnbutton, label');
        if (t) {
            longNavSafeActive = true;
            // Tingkatkan batas minimum dari plugin menjadi 120 detik jika koneksi ekstrem lambat
            const timeout = Math.max(navsafetimeout || 60, 120);
            setTimeout(() => {
                navsafe = false;
                longNavSafeActive = false;
            }, timeout * 1000); 
        }
    }, true);
    
    // Kunci status navigasi aman saat halaman mulai berpindah (Unload).
    window.addEventListener('beforeunload', () => {
        navsafe = true;
        longNavSafeActive = true;
        if (ismseb && window.Android && window.Android.triggerNavSafe) {
            window.Android.triggerNavSafe();
        }
    });


    const initui = () => {
        if (document.getElementById('mseb-monitor')) {
            return;
        }
        const m = document.createElement('div');
        m.id = 'mseb-monitor';
        m.style.cssText = 'position:fixed;top:10px;right:10px;z-index:999998;background:#0a7d00;color:#fff;' +
            'padding:8px 15px;border-radius:10px;font-size:12px;font-weight:bold;pointer-events:none;';
        m.innerHTML = `${strCount}: <span id="mseb-v-count">0</span>`;
        document.body.appendChild(m);
        setv(getv());

        // Cek sisa penalti refresh.
        const send = parseInt(localStorage.getItem(penaltykey) || '0', 10);
        if (send > Date.now()) {
            showpenaltyoverlay(send, strContinued);
        }
    };

    if (document.readyState === 'complete') {
        initui();
    } else {
        window.addEventListener('load', initui);
    }

    // Anti interaksi.
    document.addEventListener('contextmenu', (e) => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach((v) => document.addEventListener(v, (e) => e.preventDefault()));
};
