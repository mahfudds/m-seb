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

        $mform->addElement('header', 'msebheader', get_string('msebheader', 'local_mseb'));

        $mform->addElement(
            'advcheckbox',
            'mseb_enabled',
            get_string('mseb_enabled', 'local_mseb'),
            get_string('mseb_enabled_desc', 'local_mseb')
        );
        $mform->setType('mseb_enabled', PARAM_INT);
        $mform->setDefault('mseb_enabled', $config ? $config->enabled : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_allowpc',
            get_string('mseb_allowpc', 'local_mseb'),
            get_string('mseb_allowpc_desc', 'local_mseb')
        );
        $mform->setType('mseb_allowpc', PARAM_INT);
        $mform->setDefault('mseb_allowpc', $config ? $config->allowpc : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_protectpc',
            get_string('mseb_protectpc', 'local_mseb'),
            get_string('mseb_protectpc_desc', 'local_mseb')
        );
        $mform->setType('mseb_protectpc', PARAM_INT);
        $mform->setDefault('mseb_protectpc', $config ? $config->protectpc : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_allowios',
            get_string('mseb_allowios', 'local_mseb'),
            get_string('mseb_allowios_desc', 'local_mseb')
        );
        $mform->setType('mseb_allowios', PARAM_INT);
        $mform->setDefault('mseb_allowios', $config ? $config->allowios : 1);

        $mform->addElement(
            'text',
            'mseb_mintime',
            get_string('mseb_mintime', 'local_mseb'),
            array('size' => '5')
        );
        $mform->addHelpButton('mseb_mintime', 'mseb_mintime', 'local_mseb');
        $mform->setType('mseb_mintime', PARAM_INT);
        $mform->setDefault('mseb_mintime', $config ? $config->mintime : 0);

        $mform->addElement(
            'text',
            'mseb_minanswered',
            get_string('mseb_minanswered', 'local_mseb'),
            array('size' => '5')
        );
        $mform->addHelpButton('mseb_minanswered', 'mseb_minanswered', 'local_mseb');
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
        } else if ($inject && (strpos($script, 'attempt.php') !== false || strpos($script, 'summary.php') !== false)) {
            $PAGE->requires->js_call_amd('local_mseb/proguard', 'init', [(int)$quizid, (bool)$is_ios]);
        }
    }
}

/**
 * Display block page.
 */
function local_mseb_show_blocked_page($key) {
    global $FULLME, $USER, $CFG;
    while (ob_get_level()) ob_end_clean();
    header("HTTP/1.1 403 Forbidden");
    
    $msg = get_string($key, 'local_mseb');
    $title = get_string('blocked_locked', 'local_mseb');
    $heading = get_string('blocked_title', 'local_mseb');
    $backbutton = get_string('blocked_back', 'local_mseb');

    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>{$title} 🛑</title>
    <style>
        body{background:#111;color:#fff;font-family:sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;text-align:center;}
        .box{border:3px solid #e00;padding:40px;border-radius:15px;max-width:450px;background:#222;box-shadow:0 10px 30px rgba(224,60,49,0.4);}
        h1{color:#e03c31;margin-bottom:20px;}
        p{line-height:1.6;color:#ddd;margin-bottom:30px;}
        .btn{background:#e03c31;color:#fff;padding:12px 30px;border-radius:5px;text-decoration:none;font-weight:bold;display:inline-block;}
    </style>
</head>
<body>
    <div class='box'>
        <h1>{$heading}</h1>
        <p>{$msg}</p>
        <a href='javascript:history.back()' class='btn'>{$backbutton}</a>
    </div>
</body>
</html>
HTML;
    die();
}
