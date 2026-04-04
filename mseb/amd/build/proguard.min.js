define(['core/str'], function(Str) {
    "use strict";

    var init = function(quizid, isios, ismseb, msebenabled, facerecognition, navsafetimeout) {
        if (ismseb && window.Android) {
            if (window.Android.setFaceRecognition) {
                var enabled = (facerecognition === true || facerecognition === 1 || facerecognition === "1");
                window.Android.setFaceRecognition(enabled);
                console.log("M-SEB: Face Recognition Bridge call: " + enabled);
            }
            if (window.Android.setNavSafeTimeout) {
                window.Android.setNavSafeTimeout(parseInt(navsafetimeout || 60, 10));
            }
        }

        var path = location.pathname;
        if (!/\/mod\/quiz\/(attempt|view|summary)\.php/.test(path)) {
            return;
        }

        if (window.MSEB_V17_ACTIVE) {
            return;
        }
        window.MSEB_V17_ACTIVE = true;

        var isAttemptPage = /\/mod\/quiz\/(attempt|summary)\.php/.test(path);
        var navsafe = true;
        var longNavSafeActive = false;
        var blurat = 0;
        var ignoreblur = false;
        var ispenaltyshowing = false;
        
        var attemptid = new URLSearchParams(location.search).get('attempt') || '0';
        var storagekey = 'mseb_v17_viol_' + quizid + '_' + attemptid;
        var penaltykey = storagekey + '_end';

        // Initial grace.
        setTimeout(function() {
            navsafe = false;
            console.log("M-SEB: Initial grace period ended.");
        }, 10000);

        // Load strings.
        var stringRequests = [
            {key: 'js:violation', component: 'local_mseb'},
            {key: 'js:violationcount', component: 'local_mseb'},
            {key: 'js:leavingexam', component: 'local_mseb'},
            {key: 'js:penalty_level', component: 'local_mseb'},
            {key: 'js:sanction_continued', component: 'local_mseb'},
            {key: 'js:desktop_mode', component: 'local_mseb'},
            {key: 'js:is_mobile_blocked', component: 'local_mseb'}
        ];

        Str.get_strings(stringRequests).then(function(results) {
            var strViolation = results[0];
            var strCount = results[1];
            var strLeaving = results[2];
            var strPenaltyLevel = results[3];
            var strContinued = results[4];
            var strDesktop = results[5];
            var strMobile = results[6];

            // A. Desktop Mode Detection.
            var checkBypass = function() {
                if (ismseb) return;
                var ua = navigator.userAgent;
                var touches = navigator.maxTouchPoints || 0;
                var isDesktopMode = false;
                var isMobileDevice = false;

                if (navigator.userAgentData) {
                    isMobileDevice = navigator.userAgentData.mobile;
                    var platformName = navigator.userAgentData.platform;
                    if (!isMobileDevice && (platformName === 'Android' || platformName === 'iOS')) {
                        isDesktopMode = true;
                    }
                }
                
                if (!isMobileDevice && !isDesktopMode) {
                    if (touches > 1 && /Linux/i.test(navigator.platform)) { isMobileDevice = true; isDesktopMode = true; }
                    if (touches > 2 && /Macintosh/i.test(ua)) { isMobileDevice = true; isDesktopMode = true; }
                }

                if (msebenabled && (isMobileDevice || isDesktopMode)) {
                    document.body.innerHTML = '<div style="background:#111;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;padding:20px;">' +
                        '<div style="border:3px solid #e00;padding:40px;border-radius:15px;max-width:450px;background:#222;box-shadow:0 10px 30px rgba(224,60,49,0.4);">' +
                        '<h1 style="color:#e03c31;margin-bottom:20px;font-size:28px;">🛑 AKSES DIBLOKIR</h1>' +
                        '<p style="line-height:1.6;color:#ddd;margin-bottom:30px;font-size:16px;">' + (isDesktopMode ? strDesktop : strMobile) + '</p>' +
                        '<a href="javascript:location.reload()" style="background:#e03c31;color:#fff;padding:12px 30px;border-radius:5px;text-decoration:none;font-weight:bold;display:inline-block;">MUAT ULANG HALAMAN</a>' +
                        '</div></div>';
                    return true;
                }
                return false;
            };

            if (checkBypass()) return;

            // Enforcement Logic.
            var getv = function() { return isAttemptPage ? parseInt(localStorage.getItem(storagekey) || '0', 10) : 0; };
            
            var setv = function(v) {
                localStorage.setItem(storagekey, String(v));
                var el = document.getElementById('mseb-v-count');
                if (el) el.textContent = v;
                var mon = document.getElementById('mseb-monitor');
                if (mon) mon.style.background = ['#0a7d00', '#c9a400', '#ff8800', '#ff3300', '#8b0000'][Math.min(v, 4)];
            };

            var showpenaltyoverlay = function(endtime, reason) {
                if (ispenaltyshowing) return;
                ispenaltyshowing = true;
                var overlay = document.createElement('div');
                overlay.id = "mseb-penalty";
                overlay.style.cssText = 'position:fixed;inset:0;background:rgba(211,47,47,1);color:white;z-index:9999999;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px;font-family:sans-serif;';
                overlay.innerHTML = '<div style="font-size:60px;">🚨</div><h2 style="font-size:24px;margin:15px 0;">' + strViolation + '</h2>' +
                    '<p style="font-size:16px;max-width:300px;margin-bottom:20px;">' + reason + '<br><br>' + strPenaltyLevel + ': ' + getv() + '</p>' +
                    '<div style="font-size:45px;font-weight:bold;background:white;color:#d32f2f;padding:10px 30px;border-radius:15px;"><span id="p-min">00</span>:<span id="p-sec">00</span></div>';
                document.body.appendChild(overlay);
                document.body.style.overflow = 'hidden';

                var ptimer = setInterval(function() {
                    var rem = Math.ceil((endtime - Date.now()) / 1000);
                    if (rem <= 0) { clearInterval(ptimer); overlay.remove(); document.body.style.overflow = ''; ispenaltyshowing = false; localStorage.removeItem(penaltykey); return; }
                    document.getElementById('p-min').textContent = String(Math.floor(rem / 60)).padStart(2, '0');
                    document.getElementById('p-sec').textContent = String(rem % 60).padStart(2, '0');
                }, 1000);
            };

            var triggerviolation = function(reason) {
                if (navsafe || ispenaltyshowing) return;
                var v = getv() + 1;
                setv(v);
                var endtime = Date.now() + (v * 60 * 1000);
                localStorage.setItem(penaltykey, String(endtime));
                showpenaltyoverlay(endtime, reason);
            };

            // Listeners.
            var visibilityTimer = null;
            document.addEventListener('visibilitychange', function() {
                if (document.visibilityState === 'hidden') {
                    ignoreblur = true;
                    // Tunggu 2 detik sebelum trigger — cegah false positive saat navigasi/loading
                    if (visibilityTimer) clearTimeout(visibilityTimer);
                    visibilityTimer = setTimeout(function() {
                        if (document.visibilityState === 'hidden' && !navsafe && !ispenaltyshowing) {
                            triggerviolation(strLeaving);
                        }
                        visibilityTimer = null;
                    }, 2000);
                }
                else {
                    if (visibilityTimer) { clearTimeout(visibilityTimer); visibilityTimer = null; }
                    setTimeout(function() { ignoreblur = false; }, 1000);
                }
            });

            window.addEventListener('blur', function() { if (!ignoreblur) blurat = Date.now(); });
            window.addEventListener('focus', function() { if (blurat && (Date.now() - blurat > 800) && !navsafe) triggerviolation(strLeaving); blurat = 0; });

            var unfocusedCount = 0;
            setInterval(function() {
                if (!document.hasFocus() && !navsafe && !ignoreblur && !ispenaltyshowing) {
                    unfocusedCount++;
                    // Hanya trigger setelah 3x berturut-turut unfocused (4.5 detik)
                    if (unfocusedCount >= 3) {
                        triggerviolation(strLeaving);
                        unfocusedCount = 0;
                    }
                } else {
                    unfocusedCount = 0;
                }
            }, 1500);

            document.addEventListener('click', function(e) {
                navsafe = true;
                if (ismseb && window.Android && window.Android.triggerNavSafe) window.Android.triggerNavSafe();
                setTimeout(function() { if (!longNavSafeActive) navsafe = false; }, 10000);

                var t = e.target.closest('a, button, input[type="submit"], .qnbutton, label, [role="button"], .mod_quiz-next-nav, .submitbtns, .btn, [data-action], .nav-link, .page-link');
                if (t) {
                    longNavSafeActive = true;
                    var timeout = Math.max(navsafetimeout || 60, 120);
                    setTimeout(function() { navsafe = false; longNavSafeActive = false; }, timeout * 1000);
                }
            }, true);

            window.addEventListener('beforeunload', function() {
                navsafe = true;
                longNavSafeActive = true;
                if (ismseb && window.Android && window.Android.triggerNavSafe) window.Android.triggerNavSafe();
            });

            // UI Init.
            var initui = function() {
                if (document.getElementById('mseb-monitor')) return;
                var m = document.createElement('div');
                m.id = 'mseb-monitor';
                m.style.cssText = 'position:fixed;top:10px;right:10px;z-index:999998;background:#0a7d00;color:#fff;padding:8px 15px;border-radius:10px;font-size:12px;font-weight:bold;pointer-events:none;';
                m.innerHTML = strCount + ': <span id="mseb-v-count">0</span>';
                document.body.appendChild(m);
                setv(getv());
                var send = parseInt(localStorage.getItem(penaltykey) || '0', 10);
                if (send > Date.now()) showpenaltyoverlay(send, strContinued);
            };

            if (document.readyState === 'complete') initui(); else window.addEventListener('load', initui);

            // Anti-interaction.
            document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
            ['copy', 'cut', 'paste'].forEach(function(v) { document.addEventListener(v, function(e) { e.preventDefault(); }); });
        });
    };

    return {
        init: init
    };
});
