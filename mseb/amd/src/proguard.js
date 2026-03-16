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
 */
export const init = async (quizid) => {
    // 1. Instant Check.
    if (!/\/mod\/quiz\/(attempt|view)\.php/.test(location.pathname)) {
        return;
    }
    if (window.MSEB_V17_ACTIVE) {
        return;
    }
    window.MSEB_V17_ACTIVE = true;

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
    let navsafe = false;
    let blurat = 0;
    let ignoreblur = false;
    let ispenaltyshowing = false;

    // Helper Storage.
    const getv = () => parseInt(localStorage.getItem(storagekey) || '0', 10);

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
    document.addEventListener('click', (e) => {
        const t = e.target.closest('a, button, input[type="submit"], .qnbutton');
        if (t) {
            navsafe = true;
            setTimeout(() => {
                navsafe = false;
            }, 800);
        }
    }, true);

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
