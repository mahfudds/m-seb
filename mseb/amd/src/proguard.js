/**
 * M-SEB Pro Guard module — JS-based proctoring for iOS and PC.
 *
 * Detects tab switching, minimising, auto-translation, and blocks
 * right-click / copy-paste. Applies an escalating penalty timer overlay
 * for each violation.
 *
 * @module   local_mseb/proguard
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {get_string} from 'core/str';

/**
 * Initialise the Pro Guard proctoring module.
 *
 * @param {number} quizId  The quiz instance ID.
 * @param {boolean} isIos Whether the user is on an iOS device.
 */
export const init = async(quizId, isIos) => {
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
        detected: await get_string('js:detected', 'local_mseb', 'App Switch / Focus Loss'),
        penaltynotice: await get_string('js:penaltynotice', 'local_mseb', 1),
    };

    // Helper to ensure DOM is ready.
    const onReady = (fn) => {
        if (document.readyState !== 'loading') {
            fn();
        } else {
            document.addEventListener('DOMContentLoaded', fn);
        }
    };

    // Anti-Google-Translate attributes.
    document.documentElement.setAttribute('translate', 'no');
    document.documentElement.classList.add('notranslate');
    onReady(() => {
        if (document.body) {
            document.body.setAttribute('translate', 'no');
            document.body.classList.add('notranslate');
        }
    });

    // Observe DOM for translation artefacts.
    const translateObserver = new MutationObserver(() => {
        if (document.querySelector('font[style*="background"]')) {
            addViolation(strings.autoTranslate);
        }
    });

    onReady(() => {
        translateObserver.observe(document.body, {childList: true, subtree: true});
    });

    // Session-based violation counter.
    const params = new URLSearchParams(window.location.search);
    const attemptId = params.get('attempt') || '0';
    const storageKey = `seb_v15_violation_${quizId}_${attemptId}`;
    const penaltyEndTimeKey = `${storageKey}_endtime`;
    const SLEEP_IGNORE_MS = 15000;

    let navSafe = false;
    let blurAt = 0;
    let hiddenAt = 0;
    let ignoreBlur = false;
    let ignoreFocusAfterSleep = false;
    let isPenaltyRunning = false;

    /**
     * @returns {number} Current violation count.
     */
    const getViolations = () => parseInt(window.localStorage.getItem(storageKey) || '0', 10);

    /**
     * @param {number} v New violation count.
     */
    const setViolations = (v) => {
        window.localStorage.setItem(storageKey, String(v));
        updateMonitor(v);
    };

    // Violation monitor UI.
    let monitorEl = null;

    onReady(() => {
        if (document.getElementById('mseb-monitor')) {
            return;
        }
        monitorEl = document.createElement('div');
        monitorEl.id = 'mseb-monitor';
        monitorEl.style.cssText =
            'position:fixed;top:15px;right:15px;z-index:999999;' +
            'background:#0a7d00;color:#fff;padding:12px 18px;' +
            'border-radius:12px;font-size:14px;font-weight:700;' +
            'pointer-events:none;box-shadow:0 4px 6px rgba(0,0,0,0.3);';
        monitorEl.innerHTML = `${strings.violationCount}: <span id="monV">0</span>`;
        document.body.appendChild(monitorEl);
        updateMonitor(getViolations());
        checkResumePenalty();
    });

    /**
     * Update the monitor badge colour and count.
     *
     * @param {number} v Current violation count.
     */
    const updateMonitor = (v) => {
        const spanV = document.getElementById('monV');
        if (spanV) {
            spanV.textContent = v;
        }
        if (monitorEl) {
            const colours = ['#0a7d00', '#c9a400', '#ff8800', '#ff3300', '#8b0000'];
            monitorEl.style.background = colours[Math.min(v, colours.length - 1)];
        }
    };

    /**
     * Play an audible beep.
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
        } catch { }
    };

    /**
     * Resume penalty if it was running.
     */
    const checkResumePenalty = () => {
        const endTime = parseInt(window.localStorage.getItem(penaltyEndTimeKey) || '0', 10);
        const now = Date.now();
        if (endTime > now) {
            const remaining = Math.ceil((endTime - now) / 1000);
            showPenaltyOverlay(remaining, endTime);
        }
    };

    /**
     * Record a violation and show the penalty overlay.
     */
    const addViolation = async (reason) => {
        if (navSafe || isPenaltyRunning) {
            return;
        }

        const v = getViolations() + 1;
        setViolations(v);
        beep(1200, 200);

        const now = Date.now();
        const penaltyDuration = v * 60 * 1000;
        const endTime = now + penaltyDuration;
        window.localStorage.setItem(penaltyEndTimeKey, String(endTime));

        showPenaltyOverlay(v * 60, endTime);
    };

    /**
     * Display the penalty overlay with a countdown.
     */
    const showPenaltyOverlay = async (seconds, endTime) => {
        if (isPenaltyRunning) return;
        isPenaltyRunning = true;

        const v = getViolations();
        const detStr = (typeof strings.detected === 'string') ? strings.detected : 'Violation Detected';
        const penStr = (typeof strings.penaltynotice === 'string') 
            ? strings.penaltynotice.replace('{$a}', v) 
            : `Penalty level ${v}`;

        const overlay = document.createElement('div');
        overlay.id = 'mseb-penalty-overlay';
        overlay.style.cssText =
            'position:fixed;inset:0;background:rgba(211,47,47,0.98);' +
            'color:white;z-index:9999999;display:flex;flex-direction:column;' +
            'align-items:center;justify-content:center;font-family:sans-serif;text-align:center;padding:20px;';

        overlay.innerHTML = `
            <div style="font-size:80px;margin-bottom:20px;">🚨</div>
            <h1 style="font-size:28px;margin-bottom:15px;font-weight:bold;">${strings.violation}</h1>
            <p style="font-size:16px;margin-bottom:20px;">
                ${detStr}<br><br>${penStr}
            </p>
            <div style="font-size:55px;font-weight:bold;background:white;color:#d32f2f;padding:15px 35px;border-radius:15px;">
                <span id="mseb-min">00</span>:<span id="mseb-sec">00</span>
            </div>
        `;

        document.body.appendChild(overlay);
        document.body.style.overflow = 'hidden';

        const updateTimer = () => {
            const now = Date.now();
            const remaining = Math.ceil((endTime - now) / 1000);
            
            if (remaining <= 0) {
                clearInterval(penaltyTimer);
                overlay.remove();
                document.body.style.overflow = '';
                isPenaltyRunning = false;
                window.localStorage.removeItem(penaltyEndTimeKey);
                return;
            }

            const mins = Math.floor(remaining / 60);
            const secs = remaining % 60;
            const minEl = document.getElementById('mseb-min');
            const secEl = document.getElementById('mseb-sec');
            if (minEl) minEl.textContent = mins < 10 ? '0' + mins : String(mins);
            if (secEl) secEl.textContent = secs < 10 ? '0' + secs : String(secs);
        };

        const penaltyTimer = setInterval(updateTimer, 1000);
        updateTimer();
    };

    // Safe navigation detection.
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
            setTimeout(() => { navSafe = false; }, 1000);
        }
    }, true);

    // Visibility / Sleep Check.
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
            ignoreBlur = true;
            if (!navSafe) addViolation(strings.leavingExam);
        } else if (document.visibilityState === 'visible' && hiddenAt) {
            const diff = Date.now() - hiddenAt;
            if (diff > SLEEP_IGNORE_MS) ignoreFocusAfterSleep = true;
            hiddenAt = 0;
            setTimeout(() => { ignoreBlur = false; }, 1000);
        }
    });

    // Reactive Focus/Blur check (Works best on iOS Safari/SEB).
    window.addEventListener('blur', () => {
        if (ignoreBlur) return;
        blurAt = Date.now();
    });

    window.addEventListener('focus', () => {
        if (ignoreFocusAfterSleep) {
            ignoreFocusAfterSleep = false;
            blurAt = 0;
            return;
        }
        if (!blurAt) return;
        const diff = Date.now() - blurAt;
        if (diff > 800 && !navSafe) {
            addViolation(strings.leavingExam);
        }
        blurAt = 0;
    });

    // Heartbeat check for focus on iOS.
    if (isIos) {
        setInterval(() => {
            if (!document.hasFocus() && !navSafe && !ignoreBlur && !isPenaltyRunning) {
                addViolation(strings.leavingExam);
            }
        }, 2500);
    }

    // Block Split Screen.
    const checkWindowSize = () => {
        if (navSafe || isPenaltyRunning) return;
        const widthRatio = window.innerWidth / window.screen.availWidth;
        const heightRatio = window.innerHeight / window.screen.availHeight;
        if (widthRatio < 0.8 || heightRatio < 0.8) {
            addViolation("Split Screen / Small Window");
        }
    };
    window.addEventListener('resize', checkWindowSize);

    // Block right-click and clipboard operations.
    document.addEventListener('contextmenu', (e) => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach((evt) => {
        document.addEventListener(evt, (e) => e.preventDefault());
    });
};
