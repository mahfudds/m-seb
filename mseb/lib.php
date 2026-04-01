<?php
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
 * Library functions for local_mseb.
 *
 * @package    local_mseb
 * @copyright  2024 M-SEB
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add M-SEB configuration fields to the quiz course module form.
 *
 * @param object $formwrapper The wrapper around the form.
 * @param MoodleQuickForm $mform The form being built.
 */
function local_mseb_coursemodule_standard_elements($formwrapper, $mform) {
    global $DB;
    if ($formwrapper->get_coursemodule() && $formwrapper->get_coursemodule()->modname === 'quiz') {
        $quizid = $formwrapper->get_coursemodule()->instance;
        $config = $DB->get_record('local_mseb', ['quizid' => $quizid]);

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
            ['size' => '5']
        );
        $mform->addHelpButton('mseb_mintime', 'mseb_mintime', 'local_mseb');
        $mform->setType('mseb_mintime', PARAM_INT);
        $mform->setDefault('mseb_mintime', $config ? $config->mintime : 0);

        $mform->addElement(
            'text',
            'mseb_minanswered',
            get_string('mseb_minanswered', 'local_mseb'),
            ['size' => '5']
        );
        $mform->addHelpButton('mseb_minanswered', 'mseb_minanswered', 'local_mseb');
        $mform->setType('mseb_minanswered', PARAM_INT);
        $mform->setDefault('mseb_minanswered', $config ? $config->minanswered : 0);

        $mform->addElement(
            'advcheckbox',
            'mseb_facerecognition',
            get_string('mseb_facerecognition', 'local_mseb'),
            get_string('mseb_facerecognition_desc', 'local_mseb')
        );
        $mform->setType('mseb_facerecognition', PARAM_INT);
        $mform->setDefault('mseb_facerecognition', $config ? $config->facerecognition : 0);

        $mform->addElement(
            'text',
            'mseb_navsafetimeout',
            get_string('mseb_navsafetimeout', 'local_mseb'),
            ['size' => '5']
        );
        $mform->addHelpButton('mseb_navsafetimeout', 'mseb_navsafetimeout', 'local_mseb');
        $mform->setType('mseb_navsafetimeout', PARAM_INT);
        $mform->setDefault('mseb_navsafetimeout', $config ? $config->navsafetimeout : 60);
    }
}

/**
 * Save settings after quiz edit.
 *
 * @param object $data The submitted form data.
 * @param object $course The course object.
 * @return object The data object.
 */
function local_mseb_coursemodule_edit_post_actions($data, $course) {
    global $DB;
    if ($data->modulename === 'quiz') {
        $quizid = $data->instance;
        $msebenabled = isset($data->mseb_enabled) ? (int) $data->mseb_enabled : 0;
        $mseballowpc = isset($data->mseb_allowpc) ? (int) $data->mseb_allowpc : 0;
        $msebprotectpc = isset($data->mseb_protectpc) ? (int) $data->mseb_protectpc : 0;
        $mseballowios = isset($data->mseb_allowios) ? (int) $data->mseb_allowios : 0;
        $msebmintime = isset($data->mseb_mintime) ? (int) $data->mseb_mintime : 0;
        $msebminanswered = isset($data->mseb_minanswered) ? (int) $data->mseb_minanswered : 0;
        $msebfacerecognition = isset($data->mseb_facerecognition) ? (int) $data->mseb_facerecognition : 0;
        $msebnavsafetimeout = isset($data->mseb_navsafetimeout) ? (int) $data->mseb_navsafetimeout : 60;

        $record = $DB->get_record('local_mseb', ['quizid' => $quizid]);
        if ($record) {
            $record->enabled = $msebenabled;
            $record->allowpc = $mseballowpc;
            $record->protectpc = $msebprotectpc;
            $record->allowios = $mseballowios;
            $record->mintime = $msebmintime;
            $record->minanswered = $msebminanswered;
            $record->facerecognition = $msebfacerecognition;
            $record->navsafetimeout = $msebnavsafetimeout;
            $DB->update_record('local_mseb', $record);
        } else {
            $newrecord = new stdClass();
            $newrecord->quizid = $quizid;
            $newrecord->enabled = $msebenabled;
            $newrecord->allowpc = $mseballowpc;
            $newrecord->protectpc = $msebprotectpc;
            $newrecord->allowios = $mseballowios;
            $newrecord->mintime = $msebmintime;
            $newrecord->minanswered = $msebminanswered;
            $newrecord->facerecognition = $msebfacerecognition;
            $newrecord->navsafetimeout = $msebnavsafetimeout;
            $DB->insert_record('local_mseb', $newrecord);
        }
    }
    return $data;
}

/**
 * Main Enforcement Logic.
 */
function local_mseb_extend_navigation() {
    global $DB, $PAGE;

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/mod/quiz/') === false) {
        return;
    }

    // Resolve IDs.
    $cmid = optional_param('id', 0, PARAM_INT) ?: (optional_param('cmid', 0, PARAM_INT) ?: 0);
    $attemptid = optional_param('attempt', 0, PARAM_INT);

    if (!$cmid && $attemptid) {
        $qid = $DB->get_field('quiz_attempts', 'quiz', ['id' => $attemptid]);
        if ($qid) {
            $cm = get_coursemodule_from_instance('quiz', $qid);
            if ($cm) {
                $cmid = $cm->id;
            }
        }
    }

    if (!$cmid) {
        return;
    }
    $cm = get_coursemodule_from_id('quiz', $cmid);
    if (!$cm) {
        return;
    }

    // Admin Bypass.
    $context = context_module::instance($cm->id);
    if (has_capability('mod/quiz:manage', $context) || has_capability('mod/quiz:preview', $context) || is_siteadmin()) {
        return;
    }

    $quizid = $cm->instance;
    $config = $DB->get_record('local_mseb', ['quizid' => $quizid]);
    if (!$config) {
        return;
    }

    // 1. Minimum Time & Answered Logic (AMD remains for UI).
    $isatsum = (strpos($script, 'attempt.php') !== false || strpos($script, 'summary.php') !== false);
    if ($isatsum && ($config->mintime > 0 || $config->minanswered > 0)) {
        $timestart = $DB->get_field('quiz_attempts', 'timestart', ['id' => $attemptid, 'quiz' => $quizid]);
        if ($timestart) {
            $PAGE->requires->js_call_amd(
                'local_mseb/mintime',
                'init',
                [$timestart, $config->mintime, $config->minanswered, time()]
            );
        }
    }

    // 2. M-SEB Lock & ProGuard Logic (Enhanced Protection).
    $useragent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $ismseb = (strpos($useragent, 'm-seb-android') !== false);
    $isseb  = (strpos($useragent, 'seb') !== false);
    $isios  = (strpos($useragent, 'iphone') !== false) ||
              (strpos($useragent, 'ipad') !== false) ||
              (strpos($useragent, 'ipod') !== false) ||
              (strpos($useragent, 'macintosh') !== false && strpos($useragent, 'mobile') !== false);
    $isandroid = (strpos($useragent, 'android') !== false);

    $blocked = false;
    $inject = false;
    $reason = "";

    if ($isandroid) {
        // Force M-SEB app if enabled.
        if ($config->enabled && !$ismseb) {
            $blocked = true;
            $reason = "blocked_android";
        }
    } else if ($isios) {
        // iOS handling.
        if ($config->allowios) {
            $inject = true;
        } else {
            $blocked = true;
            $reason = "blocked_ios_seb";
        }
    } else {
        // PC handling.
        if (!$config->allowpc && !$isseb) {
            $blocked = true;
            $reason = "blocked_pc";
        }
    }

    // Pro Guard Injection Logic:
    // - Inject if protectpc is ON (Independent protection).
    // - Inject if enabled is ON (To detect "Desktop Mode" bypassers).
    // - Inject if it's an iOS device being allowed.
    if (!$blocked) {
        if ($config->protectpc || $config->enabled || ($isios && $config->allowios)) {
            $inject = true;
        }
    }

    if ($blocked) {
        local_mseb_show_blocked_page($reason);
    } else if ($inject && (strpos($script, 'attempt.php') !== false || strpos($script, 'summary.php') !== false || strpos($script, 'view.php') !== false)) {
        // Initialize ProGuard with extended parameters.
        $PAGE->requires->js_call_amd('local_mseb/proguard', 'init', [
            (int) $quizid,
            (bool) $isios,
            (bool) $ismseb,
            (bool) $config->enabled,
            (bool) $config->facerecognition,
            (int) ($config->navsafetimeout ?? 60)
        ]);
    }
}

/**
 * Display block page.
 *
 * @param string $key The language string key for the error message.
 */
function local_mseb_show_blocked_page($key) {
    while (ob_get_level()) {
        ob_end_clean();
    }
    http_response_code(403);

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
        body{background:#111;color:#fff;font-family:sans-serif;display:flex;align-items:center;
            justify-content:center;height:100vh;margin:0;text-align:center;}
        .box{border:3px solid #e00;padding:40px;border-radius:15px;max-width:450px;background:#222;
            box-shadow:0 10px 30px rgba(224,60,49,0.4);}
        h1{color:#e03c31;margin-bottom:20px;}
        p{line-height:1.6;color:#ddd;margin-bottom:30px;}
        .btn{background:#e03c31;color:#fff;padding:12px 30px;border-radius:5px;
            text-decoration:none;font-weight:bold;display:inline-block;}
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
