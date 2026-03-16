// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * M-SEB Pro Guard module — JS-based proctoring (Ultra Precise Version)
 *
 * @module   local_mseb/proguard
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string} from 'core/str';

export const init = async (quizId, isIos) => {
    // 1. Instant Check
    if (!/\/mod\/quiz\/(attempt|view)\.php/.test(location.pathname)) return;
    if (window.MSEB_V17_ACTIVE) return;
    window.MSEB_V17_ACTIVE = true;

    console.log("M-SEB ProGuard V1.7: ACTIVATED");

    // Load strings
    const strViolation = await get_string('js:violation', 'local_mseb');
    const strCount = await get_string('js:violationcount', 'local_mseb');
    const strLeaving = await get_string('js:leavingexam', 'local_mseb');
    const strPenaltyLevel = await get_string('js:penalty_level', 'local_mseb');
    const strContinued = await get_string('js:sanction_continued', 'local_mseb');
    // We can add more strings if needed.

    // State
    const storageKey = `mseb_v17_viol_${quizId}_${new URLSearchParams(location.search).get('attempt')||'0'}`;
    const penaltyKey = `${storageKey}_end`;
    let navSafe = false;
    let blurAt = 0;
    let hiddenAt = 0;
    let ignoreBlur = false;
    let isPenaltyShowing = false;

    // Helper Storage
    const getV = () => parseInt(localStorage.getItem(storageKey) || '0', 10);
    const setV = (v) => {
        localStorage.setItem(storageKey, String(v));
        const el = document.getElementById('mseb-v-count');
        if (el) el.textContent = v;
        updateMonitorStyle(v);
    };

    // 2. ATTACH LISTENERS
    
    // Visibility Change
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
            ignoreBlur = true;
            if (!navSafe) triggerViolation(strLeaving);
        } else {
            hiddenAt = 0;
            setTimeout(() => { ignoreBlur = false; }, 1000);
        }
    });

    // Blur & Focus
    window.addEventListener('blur', () => {
        if (ignoreBlur) return;
        blurAt = Date.now();
    });

    window.addEventListener('focus', () => {
        if (!blurAt) return;
        const diff = Date.now() - blurAt;
        if (diff > 800 && !navSafe) {
            triggerViolation(strLeaving);
        }
        blurAt = 0;
    });

    // Heartbeat
    setInterval(() => {
        if (!document.hasFocus() && !navSafe && !ignoreBlur && !isPenaltyShowing) {
            triggerViolation(strLeaving);
        }
    }, 1500);

    // Nav Safe (Legit Klik)
    document.addEventListener('click', (e) => {
        const t = e.target.closest('a, button, input[type="submit"], .qnbutton');
        if (t) {
            navSafe = true;
            setTimeout(() => { navSafe = false; }, 800);
        }
    }, true);

    // 3. UI & VIOLATION LOGIC
    
    const triggerViolation = (reason) => {
        if (navSafe || isPenaltyShowing) return;
        
        console.error("M-SEB VIOLATION:", reason);
        const v = getV() + 1;
        setV(v);

        const duration = v * 60 * 1000;
        const endTime = Date.now() + duration;
        localStorage.setItem(penaltyKey, String(endTime));

        showPenaltyOverlay(v * 60, endTime, reason);
    };

    const showPenaltyOverlay = (seconds, endTime, reason) => {
        if (isPenaltyShowing) return;
        isPenaltyShowing = true;

        const overlay = document.createElement('div');
        overlay.id = "mseb-penalty";
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(211,47,47,1);color:white;z-index:9999999;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px;font-family:sans-serif;';
        overlay.innerHTML = `
            <div style="font-size:60px;">🚨</div>
            <h2 style="font-size:24px;margin:15px 0;">${strViolation}</h2>
            <p style="font-size:16px;max-width:300px;margin-bottom:20px;">
                ${reason}<br><br>
                ${strPenaltyLevel}: ${getV()}
            </p>
            <div style="font-size:45px;font-weight:bold;background:white;color:#d32f2f;padding:10px 30px;border-radius:15px;">
                <span id="p-min">00</span>:<span id="p-sec">00</span>
            </div>
        `;
        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const uTimer = () => {
            const rem = Math.ceil((endTime - Date.now()) / 1000);
            if (rem <= 0) {
                clearInterval(pTimer);
                overlay.remove();
                document.body.style.overflow = '';
                isPenaltyShowing = false;
                localStorage.removeItem(penaltyKey);
                return;
            }
            const m = Math.floor(rem / 60);
            const s = rem % 60;
            document.getElementById('p-min').textContent = String(m).padStart(2, '0');
            document.getElementById('p-sec').textContent = String(s).padStart(2, '0');
        };
        const pTimer = setInterval(uTimer, 1000);
        uTimer();
    };

    const updateMonitorStyle = (v) => {
        const mon = document.getElementById('mseb-monitor');
        if (!mon) return;
        const cols = ['#0a7d00', '#c9a400', '#ff8800', '#ff3300', '#8b0000'];
        mon.style.background = cols[Math.min(v, 4)];
    };

    const initUI = () => {
        if (document.getElementById('mseb-monitor')) return;
        const m = document.createElement('div');
        m.id = 'mseb-monitor';
        m.style.cssText = 'position:fixed;top:10px;right:10px;z-index:999998;background:#0a7d00;color:#fff;padding:8px 15px;border-radius:10px;font-size:12px;font-weight:bold;pointer-events:none;';
        m.innerHTML = `${strCount}: <span id="mseb-v-count">0</span>`;
        document.body.appendChild(m);
        setV(getV());
        
        // Cek sisa penalti refresh.
        const sEnd = parseInt(localStorage.getItem(penaltyKey) || '0', 10);
        if (sEnd > Date.now()) showPenaltyOverlay(0, sEnd, strContinued);
    };

    if (document.readyState === 'complete') initUI();
    else window.addEventListener('load', initUI);

    // Anti interaksi.
    document.addEventListener('contextmenu', e => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach(v => document.addEventListener(v, e => e.preventDefault()));
};
