/**
 * M-SEB Pro Guard module — JS-based proctoring (Ultra Precise Version)
 *
 * @module   local_mseb/proguard
 * @package  local_mseb
 */

export const init = (quizId, isIos) => {
    // 1. Instan Check
    if (!/\/mod\/quiz\/(attempt|view)\.php/.test(location.pathname)) return;
    if (window.MSEB_V17_ACTIVE) return;
    window.MSEB_V17_ACTIVE = true;

    console.log("M-SEB ProGuard V1.7: ACTIVATED INSTANTLY");

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

    // 2. ATTACH LISTENERS IMMEDIATELY (NO AWAIT)
    
    // Visibility Change - Paling ampuh di iOS
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'hidden') {
            hiddenAt = Date.now();
            ignoreBlur = true;
            if (!navSafe) triggerViolation("Pindah Tab / Keluar Browser");
        } else {
            hiddenAt = 0;
            setTimeout(() => { ignoreBlur = false; }, 1000);
        }
    });

    // Blur & Focus - Akurat untuk App Switcher / Status Bar
    window.addEventListener('blur', () => {
        if (ignoreBlur) return;
        blurAt = Date.now();
    });

    window.addEventListener('focus', () => {
        if (!blurAt) return;
        const diff = Date.now() - blurAt;
        if (diff > 800 && !navSafe) {
            triggerViolation("Pindah Aplikasi / Status Bar");
        }
        blurAt = 0;
    });

    // Heartbeat - Cek setiap detik (Agresif)
    setInterval(() => {
        if (!document.hasFocus() && !navSafe && !ignoreBlur && !isPenaltyShowing) {
            triggerViolation("Fokus Hilang (Heartbeat)");
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
            <h2 style="font-size:24px;margin:15px 0;">PELANGGARAN DETEKSI</h2>
            <p style="font-size:16px;max-width:300px;margin-bottom:20px;">
                ${reason}<br><br>
                Anda melakukan pergantian layar. Kuis terkunci sementara sebagai sanksi.
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
        m.innerHTML = `Pelanggaran: <span id="mseb-v-count">0</span>`;
        document.body.appendChild(m);
        setV(getV());
        
        // Cek sisa penalti refresh.
        const sEnd = parseInt(localStorage.getItem(penaltyKey) || '0', 10);
        if (sEnd > Date.now()) showPenaltyOverlay(0, sEnd, "Melanjutkan Penalti");
    };

    if (document.readyState === 'complete') initUI();
    else window.addEventListener('load', initUI);

    // Anti interaksi.
    document.addEventListener('contextmenu', e => e.preventDefault());
    ['copy', 'cut', 'paste'].forEach(v => document.addEventListener(v, e => e.preventDefault()));
};
