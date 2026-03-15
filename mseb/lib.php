<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

/**
 * Library functions for local_mseb.
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB 
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add M-SEB configuration fields to the quiz course module form.
 */
function local_mseb_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;
    if ($formwrapper->get_coursemodule() && $formwrapper->get_coursemodule()->modname === 'quiz') {
        $quizid = $formwrapper->get_coursemodule()->instance;
        $config = $DB->get_record('local_mseb', array('quizid' => $quizid));

        $mform->addElement('header', 'msebheader', 'M-SEB PROCTORING');

        $mform->addElement(
            'advcheckbox',
            'mseb_enabled',
            'AKTIFKAN KUNCIAN M-SEB',
            'Paksa penggunaan aplikasi M-SEB (Android) atau blokir browser biasa.'
        );
        $mform->setType('mseb_enabled', PARAM_INT);
        $mform->setDefault('mseb_enabled', $config ? $config->enabled : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_allowpc',
            'IZINKAN LAPTOP / PC (Chrome)',
            'Izinkan akses melalui Google Chrome di Laptop.'
        );
        $mform->setType('mseb_allowpc', PARAM_INT);
        $mform->setDefault('mseb_allowpc', $config ? $config->allowpc : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_protectpc',
            'AKTIFKAN PENGAMANAN JS DI PC',
            'Gunakan Timer Hukuman jika PC pindah tab.'
        );
        $mform->setType('mseb_protectpc', PARAM_INT);
        $mform->setDefault('mseb_protectpc', $config ? $config->protectpc : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_allowios',
            'IZINKAN iOS (SAFARI/CHROME)',
            'Izinkan iPhone dengan Perlindungan Pro Guard JS (Hukuman Timer).'
        );
        $mform->setType('mseb_allowios', PARAM_INT);
        $mform->setDefault('mseb_allowios', $config ? $config->allowios : 1);

        $mform->addElement(
            'text',
            'mseb_mintime',
            'MINIMAL WAKTU PENGERJAAN (MENIT)',
            array('size' => '5')
        );
        $mform->setType('mseb_mintime', PARAM_INT);
        $mform->setDefault('mseb_mintime', $config ? $config->mintime : 0);

        $mform->addElement(
            'text',
            'mseb_minanswered',
            'MINIMAL SOAL TERJAWAB (%)',
            array('size' => '5')
        );
        $mform->setType('mseb_minanswered', PARAM_INT);
        $mform->setDefault('mseb_minanswered', $config ? $config->minanswered : 0);
    }
}

/**
 * Save settings after quiz edit.
 */
function local_mseb_coursemodule_edit_post_actions($data, $course) {
    global $DB;
    if ($data->modulename === 'quiz') {
        $quizid = $data->instance;
        $mseb_enabled = isset($data->mseb_enabled) ? (int) $data->mseb_enabled : 0;
        $mseb_allowpc = isset($data->mseb_allowpc) ? (int) $data->mseb_allowpc : 0;
        $mseb_protectpc = isset($data->mseb_protectpc) ? (int) $data->mseb_protectpc : 0;
        $mseb_allowios = isset($data->mseb_allowios) ? (int) $data->mseb_allowios : 0;
        $mseb_mintime = isset($data->mseb_mintime) ? (int) $data->mseb_mintime : 0;
        $mseb_minanswered = isset($data->mseb_minanswered) ? (int) $data->mseb_minanswered : 0;

        $record = $DB->get_record('local_mseb', array('quizid' => $quizid));
        if ($record) {
            $record->enabled = $mseb_enabled;
            $record->allowpc = $mseb_allowpc;
            $record->protectpc = $mseb_protectpc;
            $record->allowios = $mseb_allowios;
            $record->mintime = $mseb_mintime;
            $record->minanswered = $mseb_minanswered;
            $DB->update_record('local_mseb', $record);
        } else {
            $newrecord = new stdClass();
            $newrecord->quizid = $quizid;
            $newrecord->enabled = $mseb_enabled;
            $newrecord->allowpc = $mseb_allowpc;
            $newrecord->protectpc = $mseb_protectpc;
            $newrecord->allowios = $mseb_allowios;
            $newrecord->mintime = $mseb_mintime;
            $newrecord->minanswered = $mseb_minanswered;
            $DB->insert_record('local_mseb', $newrecord);
        }
    }
    return $data;
}

/**
 * Main Enforcement Logic.
 */
function local_mseb_extend_navigation() {
    global $DB, $PAGE, $USER, $CFG;

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/mod/quiz/') === false) return;

    // Resolve IDs
    $cmid = optional_param('id', 0, PARAM_INT) ?: (optional_param('cmid', 0, PARAM_INT) ?: 0);
    $attemptid = optional_param('attempt', 0, PARAM_INT);

    if (!$cmid && $attemptid) {
        $q_id = $DB->get_field('quiz_attempts', 'quiz', array('id' => $attemptid));
        if ($q_id) {
            $cm = get_coursemodule_from_instance('quiz', $q_id);
            if ($cm) $cmid = $cm->id;
        }
    }

    if (!$cmid) return;
    $cm = get_coursemodule_from_id('quiz', $cmid);
    if (!$cm) return;

    // Admin Bypass
    $context = context_module::instance($cm->id);
    if (has_capability('mod/quiz:manage', $context) || has_capability('mod/quiz:preview', $context) || is_siteadmin()) {
        return;
    }

    $quizid = $cm->instance;
    $config = $DB->get_record('local_mseb', array('quizid' => $quizid));
    if (!$config) return;

    // 1. Minimum Time & Answered Logic (AMD remains for UI)
    $is_at_sum = (strpos($script, 'attempt.php') !== false || strpos($script, 'summary.php') !== false);
    if ($is_at_sum && ($config->mintime > 0 || $config->minanswered > 0)) {
        $timestart = $DB->get_field('quiz_attempts', 'timestart', array('id' => $attemptid, 'quiz' => $quizid));
        if ($timestart) {
            $PAGE->requires->js_call_amd('local_mseb/mintime', 'init', [$timestart, $config->mintime, $config->minanswered, time()]);
        }
    }

    // 2. M-SEB Lock & ProGuard Logic
    if ($config->enabled) {
        $useragent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
        $is_mseb = (strpos($useragent, 'm-seb-android') !== false);
        $is_seb  = (strpos($useragent, 'seb') !== false);
        $is_ios  = (strpos($useragent, 'iphone')!==false) || (strpos($useragent, 'ipad')!==false) || (strpos($useragent, 'ipod')!==false) || (strpos($useragent, 'macintosh')!==false && strpos($useragent, 'mobile')!==false);
        $is_mobile = $is_ios || (strpos($useragent, 'android') !== false);

        $blocked = false;
        $inject = false;
        $reason = "";

        if ($is_ios) {
            if ($config->allowios) $inject = true;
            else { $blocked = true; $reason = "blocked_ios_seb"; }
        } else if ($is_mobile) {
            if (!$is_mseb) { $blocked = true; $reason = "blocked_android"; }
        } else { // PC
            if (!$config->allowpc && !$is_seb) { $blocked = true; $reason = "blocked_pc"; }
            else if ($config->protectpc) $inject = true;
        }

        if ($blocked) {
            local_mseb_show_blocked_page($reason);
        } else if ($inject && strpos($script, 'attempt.php') !== false) {
            // SUNTIKAN SCRIPT LANGSUNG (METHOD BAPAK YANG JALAN)
            $js = "
            (function(){
                'use strict';
                console.log('M-SEB ProGuard: Active');
                const KEY = 'mseb_v18_viol_{$quizid}_' + (new URLSearchParams(location.search).get('attempt') || '0');
                const PEN_KEY = KEY + '_end';
                let navSafe = false, blurAt = 0, hiddenAt = 0, ignoreBlur = false, isPen = false;

                const getV=()=>parseInt(localStorage.getItem(KEY)||'0',10);
                const setV=(v)=>{ 
                    localStorage.setItem(KEY,v);
                    const mon = document.getElementById('monV');
                    if(mon) mon.innerText = v;
                };

                // Listeners instan
                document.addEventListener('visibilitychange', ()=>{
                    if(document.visibilityState === 'hidden'){
                        hiddenAt = Date.now(); ignoreBlur = true;
                        if(!navSafe) handleViol('Pindah Tab');
                    } else {
                        hiddenAt = 0; setTimeout(()=>{ignoreBlur=false;}, 1000);
                    }
                });
                window.addEventListener('blur', ()=>{ if(!ignoreBlur) blurAt=Date.now(); });
                window.addEventListener('focus', ()=>{
                    if(!blurAt) return;
                    if((Date.now()-blurAt) > 800 && !navSafe) handleViol('Pindah Aplikasi');
                    blurAt = 0;
                });
                setInterval(()=>{ if(!document.hasFocus() && !navSafe && !ignoreBlur && !isPen) handleViol('Hilang Fokus'); }, 2000);

                function handleViol(r){
                    if(navSafe || isPen) return;
                    let v = getV() + 1; setV(v);
                    const endTime = Date.now() + (v * 60 * 1000);
                    localStorage.setItem(PEN_KEY, endTime);
                    showPen(endTime, r);
                }

                function showPen(endTime, r){
                    if(isPen) return; isPen = true;
                    const ov = document.createElement('div');
                    ov.style.cssText = 'position:fixed;inset:0;background:rgba(200,0,0,0.98);color:#fff;z-index:9999999;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;font-family:sans-serif;padding:20px;';
                    ov.innerHTML = `<h1>PELANGGARAN DETEKSI</h1><p>\${r}<br>Penalty Level: \${getV()}</p><div style='font-size:50px;font-weight:bold;background:#fff;color:#c00;padding:10px 30px;border-radius:10px;' id='p-t'>00:00</div>`;
                    document.body.appendChild(ov);
                    document.body.style.overflow = 'hidden';
                    const t = setInterval(()=>{
                        const rem = Math.ceil((endTime - Date.now())/1000);
                        if(rem <= 0){ clearInterval(t); ov.remove(); document.body.style.overflow=''; isPen=false; localStorage.removeItem(PEN_KEY); return; }
                        const m=Math.floor(rem/60), s=rem%60;
                        document.getElementById('p-t').innerText = String(m).padStart(2,'0')+':'+String(s).padStart(2,'0');
                    }, 1000);
                }

                document.addEventListener('click', e=>{ if(e.target.closest('a,button,input')) { navSafe=true; setTimeout(()=>navSafe=false,1000); } }, true);
                
                // Init UI
                const init = ()=>{
                    if(document.getElementById('mseb-mon')) return;
                    const m = document.createElement('div'); m.id='mseb-mon';
                    m.style.cssText = 'position:fixed;top:10px;right:10px;z-index:999998;background:#0a0;color:#fff;padding:10px;border-radius:8px;font-weight:bold;pointer-events:none;';
                    m.innerHTML = 'Pelanggaran: <span id=\"monV\">0</span>';
                    document.body.appendChild(m); setV(getV());
                    const sEnd = parseInt(localStorage.getItem(PEN_KEY)||'0',10);
                    if(sEnd > Date.now()) showPen(sEnd, 'Sanksi Lanjutan');
                };
                if(document.readyState==='complete') init(); else window.addEventListener('load', init);
                document.addEventListener('contextmenu', e=>e.preventDefault());
                ['copy','cut','paste'].forEach(ev=>document.addEventListener(ev, e=>e.preventDefault()));
            })();
            ";
            $PAGE->requires->js_init_code($js);
        }
    }
}

/**
 * Display block page.
 */
function local_mseb_show_blocked_page($key) {
    while (ob_get_level()) ob_end_clean();
    header("HTTP/1.1 403 Forbidden");
    $msg = get_string($key, 'local_mseb');
    echo "<!DOCTYPE html><html><head><title>🛑 ACCESS BLOCKED</title><style>body{background:#111;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;} .box{border:2px solid #e00;padding:40px;border-radius:15px;max-width:400px;}</style></head>
    <body><div class='box'><h1>🛑 TERKUNCI!</h1><p>$msg</p><a href='javascript:history.back()' style='color:#e00;font-weight:bold;text-decoration:none;'>[ KEMBALI ]</a></div></body></html>";
    die();
}
