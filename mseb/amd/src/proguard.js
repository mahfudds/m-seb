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
 * M-SEB Pro Guard module — JS-based proctoring for iOS and PC.
 *
 * Detects tab switching, minimising, auto-translation, and blocks
 * right-click / copy-paste. Applies an escalating penalty timer overlay
 * for each violation.
 *
 * @module     local_mseb/proguard
 * @package    local_mseb
 * @copyright  2024 M-SEB Kemenag
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string} from 'core/str';

/**
 * Initialise the Pro Guard proctoring module.
 *
 * @param {number} quizId   The quiz instance ID.
 */
export const init = async(quizId) => {
    // Only run on attempt or view pages.
    if (!/\/mod\/quiz\/(attempt|view)\.php/.test(window.location.pathname)) {
        return;
    }

    // Pre-fetch language strings.
    const strings = {
        violation: await get_string('js:violation', 'local_mseb'),
        violationCount: await get_string('js:violationcount', 'local_mseb'),
        leavingExam: await get_string('js:leavingexam', 'local_mseb'),
        autoTranslate: await get_string('js:autotranslate', 'local_mseb'),
    };

    // Anti-Google-Translate attributes.
    document.documentElement.setAttribute('translate', 'no');
    document.documentElement.classList.add('notranslate');
    if (document.body) {
        document.body.setAttribute('translate', 'no');
        document.body.classList.add('notranslate');
    }

    // Observe DOM for translation artefacts.
    const translateObserver = new MutationObserver(() => {
        if (document.querySelector('font[style*="background"]')) {
            addViolation(strings.autoTranslate);
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        translateObserver.observe(document.body, {childList: true, subtree: true});
    });

    // Session-based violation counter.
    const params = new URLSearchParams(window.location.search);
    const attemptId = params.get('attempt') || '0';
    const storageKey = `seb_v15_violation_${quizId}_${attemptId}`;
    const SLEEP_IGNORE_MS = 15000;

    let navSafe = false;
    let hiddenAt = 0;
    let ignoreBlur = false;
    let isPenaltyRunning = false;

    /**
     * @returns {number} Current violation count.
     */
    const getViolations = () => parseInt(window.sessionStorage.getItem(storageKey) || '0', 10);

    /**
     * @param {number} v New violation count.
     */
    const setViolations = (v) => {
        window.sessionStorage.setItem(storageKey, String(v));
        updateMonitor(v);
    };

    // Violation monitor UI.
    let monitorEl = null;

    document.addEventListener('DOMContentLoaded', () => {
        monitorEl = document.createElement('div');
        monitorEl.style.cssText =
            'position:fixed;top:15px;right:15px;z-index:999999;' +
            'background:#0a7d00;color:#fff;padding:12px 18px;' +
            'border-radius:12px;font-size:14px;font-weight:700;' +
            'pointer-events:none;box-shadow:0 4px 6px rgba(0,0,0,0.3);';
        monitorEl.innerHTML = `${strings.violationCount}: <span id="monV">0</span>`;
        document.body.appendChild(monitorEl);
        updateMonitor(getViolations());
    });

    /**
     * Update the monitor badge colour and count.
     *
     * @param {number} v Current violation count.
     */
    const updateMonitor = (v) => {
        if (!monitorEl) {
            return;
        }
        const spanV = document.getElementById('monV');
        if (spanV) {
            spanV.textContent = v;
        }
        const colours = ['#0a7d00', '#c9a400', '#ff8800', '#ff3300', '#8b0000'];
        monitorEl.style.background = colours[Math.min(v, colours.length - 1)];
    };

    /**
     * Play an audible beep.
     *
     * @param {number} freq Frequency in Hz.
     * @param {number} dur  Duration in ms.
     */
    const beep = (freq = 900, dur = 150) => {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.frequency.value = freq;
            gain.gain.value = 0.3;
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            setTimeout(() => {
                osc.stop();
                ctx.close();
            }, dur);
        } catch {
            // Audio not available — ignore.
        }
    };

    /**
     * Record a violation and show the penalty overlay.
     *
     * @param {string} reason Human-readable reason for the violation.
     */
    const addViolation = async(reason) => {
        if (navSafe || isPenaltyRunning) {
            return;
        }

        const v = getViolations() + 1;
        setViolations(v);
        beep(1200, 200);

        isPenaltyRunning = true;
        let penaltySeconds = v * 60;

        const detectedStr = await get_string('js:detected', 'local_mseb', reason);
        const penaltyStr = await get_string('js:penaltynotice', 'local_mseb', v);

        const overlay = document.createElement('div');
        overlay.style.cssText =
            'position:fixed;inset:0;background:rgba(211,47,47,0.98);' +
            'color:white;z-index:9999999;display:flex;flex-direction:column;' +
            'align-items:center;justify-content:center;font-family:sans-serif;text-align:center;padding:20px;';

        overlay.innerHTML = `
            <div style="font-size:80px;margin-bottom:20px;">🚨</div>
            <h1 style="font-size:28px;margin-bottom:15px;font-weight:bold;">${strings.violation}</h1>
            <p style="font-size:16px;margin-bottom:20px;">
                ${detectedStr}<br><br>${penaltyStr}
            </p>
            <div style="font-size:55px;font-weight:bold;background:white;color:#d32f2f;padding:15px 35px;border-radius:15px;">
                <span id="mseb-min">00</span>:<span id="mseb-sec">00</span>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const penaltyTimer = setInterval(() => {
            penaltySeconds--;
            const mins = Math.floor(penaltySeconds / 60);
            const secs = penaltySeconds % 60;

            const minEl = document.getElementById('mseb-min');
            const secEl = document.getElementById('mseb-sec');
            if (minEl) {
                minEl.textContent = mins < 10 ? '0' + mins : String(mins);
            }
            if (secEl) {
                secEl.textContent = secs < 10 ? '0' + secs : String(secs);
            }

            if (penaltySeconds <= 0) {
                clearInterval(penaltyTimer);
                overlay.remove();
                document.body.style.overflow = '';
                isPenaltyRunning = false;
            }
        }, 1000);
    };

    // Safe navigation detection — avoid false positives on legitimate clicks.
    document.addEventListener('click', (e) => {
        const t = e.target;
        if (
            t.closest('#responseform') ||
            t.closest('.submitbtns') ||
            t.closest('#mod_quiz_navblock') ||
            t.closest('.qnbutton') ||
            t.tagName === 'BUTTON' ||
            (t.tagName === 'INPUT' && t.type === 'submit') ||
            t.tagName === 'A'
        ) {
            navSafe = true;
            setTimeout(() => {
                navSafe = false;
            }, 2000);
        }
    }, true);

    // Detect tab switching / minimising.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
            ignoreBlur = true;
            if (!navSafe) {
                addViolation(strings.leavingExam);
            }
        } else if (document.visibilityState === 'visible' && hiddenAt) {
            const diff = Date.now() - hiddenAt;
            if (diff > SLEEP_IGNORE_MS) {
                // Device may have been asleep — don't double-count.
            }
            hiddenAt = 0;
            setTimeout(() => {
                ignoreBlur = false;
            }, 1000);
        }
    });

    // Block right-click and clipboard operations.
    document.addEventListener('contextmenu', (e) => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach((evt) => {
        document.addEventListener(evt, (e) => e.preventDefault());
    });
};
