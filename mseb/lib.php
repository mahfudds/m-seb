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
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * Library functions for local_mseb.
 *
 * Provides callbacks for the course module form (to add M-SEB settings
 * to quiz edit forms) and navigation extension (to enforce browser
 * locking and JS-based proctoring).
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB 
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add M-SEB configuration fields to the quiz course module form.
 *
 * @param moodleform_mod $formwrapper The form wrapper.
 * @param MoodleQuickForm $mform   The Moodle QuickForm instance.
 */
function local_mseb_coursemodule_standard_elements($formwrapper, $mform) {
  global $DB;

  if (!$formwrapper->get_coursemodule() || $formwrapper->get_coursemodule()->modname !== 'quiz') {
    return;
  }

  $quizid = $formwrapper->get_coursemodule()->instance;
  $config = $DB->get_record('local_mseb', ['quizid' => $quizid]);

  $mform->addElement('header', 'msebheader', get_string('msebheader', 'local_mseb'));

  // Enable M-SEB lock.
  $mform->addElement(
    'advcheckbox',
    'mseb_enabled',
    get_string('mseb_enabled', 'local_mseb'),
    get_string('mseb_enabled_desc', 'local_mseb')
  );
  $mform->setType('mseb_enabled', PARAM_INT);
  $mform->setDefault('mseb_enabled', $config ? $config->enabled : 0);

  // Allow regular PC.
  $mform->addElement(
    'advcheckbox',
    'mseb_allowpc',
    get_string('mseb_allowpc', 'local_mseb'),
    get_string('mseb_allowpc_desc', 'local_mseb')
  );
  $mform->setType('mseb_allowpc', PARAM_INT);
  $mform->setDefault('mseb_allowpc', $config ? $config->allowpc : 0);

  // Enable JS guard on PC.
  $mform->addElement(
    'advcheckbox',
    'mseb_protectpc',
    get_string('mseb_protectpc', 'local_mseb'),
    get_string('mseb_protectpc_desc', 'local_mseb')
  );
  $mform->setType('mseb_protectpc', PARAM_INT);
  $mform->setDefault('mseb_protectpc', $config ? $config->protectpc : 0);

  // Allow iOS users.
  $mform->addElement(
    'advcheckbox',
    'mseb_allowios',
    get_string('mseb_allowios', 'local_mseb'),
    get_string('mseb_allowios_desc', 'local_mseb')
  );
  $mform->setType('mseb_allowios', PARAM_INT);
  $mform->setDefault('mseb_allowios', $config ? $config->allowios : 1);

  // Minimum working time.
  $mform->addElement(
    'text',
    'mseb_mintime',
    get_string('mseb_mintime', 'local_mseb'),
    ['size' => '5']
  );
  $mform->addHelpButton('mseb_mintime', 'mseb_mintime', 'local_mseb');
  $mform->setType('mseb_mintime', PARAM_INT);
  $mform->setDefault('mseb_mintime', $config ? $config->mintime : 0);

  // Minimum answered percentage.
  $mform->addElement(
    'text',
    'mseb_minanswered',
    get_string('mseb_minanswered', 'local_mseb'),
    ['size' => '5']
  );
  $mform->addHelpButton('mseb_minanswered', 'mseb_minanswered', 'local_mseb');
  $mform->setType('mseb_minanswered', PARAM_INT);
  $mform->setDefault('mseb_minanswered', $config && isset($config->minanswered) ? $config->minanswered : 0);
}

/**
 * Process form data after a quiz course module is saved.
 *
 * @param stdClass $data  The submitted form data.
 * @param stdClass $course The course object.
 * @return stdClass The (possibly modified) data object.
 */
function local_mseb_coursemodule_edit_post_actions($data, $course) {
  global $DB;

  if ($data->modulename !== 'quiz') {
    return $data;
  }

  $quizid     = $data->instance;
  $mseb_enabled  = isset($data->mseb_enabled)  ? (int) $data->mseb_enabled  : 0;
  $mseb_allowpc  = isset($data->mseb_allowpc)  ? (int) $data->mseb_allowpc  : 0;
  $mseb_protectpc = isset($data->mseb_protectpc) ? (int) $data->mseb_protectpc : 0;
  $mseb_allowios = isset($data->mseb_allowios) ? (int) $data->mseb_allowios : 0;
  $mseb_mintime  = isset($data->mseb_mintime)  ? (int) $data->mseb_mintime  : 0;
  $mseb_minanswered = isset($data->mseb_minanswered) ? (int) $data->mseb_minanswered : 0;

  $record = $DB->get_record('local_mseb', ['quizid' => $quizid]);
  if ($record) {
    $record->enabled   = $mseb_enabled;
    $record->allowpc   = $mseb_allowpc;
    $record->protectpc  = $mseb_protectpc;
    $record->allowios  = $mseb_allowios;
    $record->mintime   = $mseb_mintime;
    $record->minanswered = $mseb_minanswered;
    $DB->update_record('local_mseb', $record);
  } else {
    $newrecord = new stdClass();
    $newrecord->quizid   = $quizid;
    $newrecord->enabled   = $mseb_enabled;
    $newrecord->allowpc   = $mseb_allowpc;
    $newrecord->protectpc  = $mseb_protectpc;
    $newrecord->allowios  = $mseb_allowios;
    $newrecord->mintime   = $mseb_mintime;
    $newrecord->minanswered = $mseb_minanswered;
    $DB->insert_record('local_mseb', $newrecord);
  }

  return $data;
}

/**
 * Extend navigation to enforce M-SEB browser locking and inject
 * proctoring JavaScript on quiz pages.
 */
function local_mseb_extend_navigation() {
  global $DB, $PAGE, $USER, $CFG;

  $script = $_SERVER['SCRIPT_NAME'] ?? '';

  // Only act on quiz view, attempt, summary, and processattempt pages.
  $quizpages = [
    '/mod/quiz/view.php',
    '/mod/quiz/attempt.php',
    '/mod/quiz/summary.php',
    '/mod/quiz/processattempt.php',
  ];

  $onquizpage = false;
  foreach ($quizpages as $page) {
    if (strpos($script, $page) !== false) {
      $onquizpage = true;
      break;
    }
  }

  if (!$onquizpage) {
    return;
  }

  // Resolve the course module ID.
  $cmid = optional_param('id', 0, PARAM_INT);
  if (!$cmid) {
    $cmid = optional_param('cmid', 0, PARAM_INT);
  }

  $attemptid = optional_param('attempt', 0, PARAM_INT);

  if (!$cmid && $attemptid) {
    $quizidfrom = $DB->get_field('quiz_attempts', 'quiz', ['id' => $attemptid]);
    if ($quizidfrom) {
      $cm = get_coursemodule_from_instance('quiz', $quizidfrom);
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

  // Bypass for admins and teachers.
  $context = context_module::instance($cm->id);
  if (has_capability('mod/quiz:manage', $context) || has_capability('mod/quiz:preview', $context) || is_siteadmin()) {
    return;
  }

  $quizid = $cm->instance;
  $config = $DB->get_record('local_mseb', ['quizid' => $quizid]);

  if (!$config) {
    return;
  }

  // ==========================================
  // 1. Minimum Time & Minimum Answered — AMD module.
  // ==========================================
  $mintime   = isset($config->mintime) ? (int) $config->mintime : 0;
  $minanswered = isset($config->minanswered) ? (int) $config->minanswered : 0;

  $isattemptorsummary = (strpos($script, '/mod/quiz/attempt.php') !== false
            || strpos($script, '/mod/quiz/summary.php') !== false);

  if (($mintime > 0 || $minanswered > 0) && $isattemptorsummary) {
    if (!$attemptid) {
      $attemptid = optional_param('attempt', 0, PARAM_INT);
    }
    if ($attemptid) {
      try {
        $timestart = $DB->get_field('quiz_attempts', 'timestart', ['id' => $attemptid, 'quiz' => $quizid]);
        if ($timestart) {
          $servernow = time();
          $PAGE->requires->js_call_amd(
            'local_mseb/mintime',
            'init',
            [$timestart, $mintime, $minanswered, $servernow]
          );
        }
      } catch (\Exception $e) {
        // Silently catch DB errors.
        debugging('local_mseb: mintime init error - ' . $e->getMessage(), DEBUG_DEVELOPER);
      }
    }
  }

  // ==========================================
  // 2. M-SEB Browser Lock.
  // ==========================================
  if (!$config->enabled) {
    return;
  }

  $useragent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
  $ismsebapp = (strpos($useragent, 'm-seb-android') !== false);
  $ispcseb  = (strpos($useragent, 'seb') !== false);

  $isios  = (strpos($useragent, 'iphone') !== false)
       || (strpos($useragent, 'ipad') !== false)
       || (strpos($useragent, 'ipod') !== false)
       || (strpos($useragent, 'macintosh') !== false && strpos($useragent, 'mobile') !== false);
  $isandroid = (strpos($useragent, 'android') !== false);
  $ismobile = $isandroid || $isios || (strpos($useragent, 'mobile') !== false);

    $isiosseb  = (strpos($useragent, 'seb') !== false) && $isios;

    $blocked   = false;
    $injectjs  = false;
    $messagekey = 'blocked_generic';
    $showsebbutton = false;

    if ($isios) {
        if ($isiosseb) {
            // Inside SEB on iOS, allow but keep JS Guard active as a secondary layer.
            $injectjs = true; 
        } else {
            // Outside SEB on iOS, block and show SEB launch button.
            $blocked    = true;
            $messagekey = 'blocked_ios_seb';
            $showsebbutton = true;
        }
    } else if ($ismobile) {
    if (!$ismsebapp) {
      $blocked  = true;
      $messagekey = 'blocked_android';
    }
  } else {
    // PC/Laptop.
    if (!$config->allowpc && !$ispcseb) {
      $blocked  = true;
      $messagekey = 'blocked_pc';
    } else if ($config->protectpc) {
      $injectjs = true;
    }
  }

    if ($blocked) {
        local_mseb_show_blocked_page($messagekey, $showsebbutton);
    } else if ($injectjs) {
    $PAGE->requires->js_call_amd(
      'local_mseb/proguard',
      'init',
      [$quizid, $isios]
    );
  }
}

/**
 * Display a full-page block screen and terminate execution.
 *
 * @param string $messagekey The language string key for the block message.
 * @param bool $showsebbutton Whether to show the 'Open in SEB' button.
 */
function local_mseb_show_blocked_page($messagekey, $showsebbutton = false) {
    global $FULLME;
    $message    = get_string($messagekey, 'local_mseb');
  $title   = get_string('blocked_locked', 'local_mseb');
  $heading  = get_string('blocked_title', 'local_mseb');
  $backbutton = get_string('blocked_back', 'local_mseb');

  while (ob_get_level()) {
    ob_end_clean();
  }
  header("HTTP/1.1 403 Forbidden");
  header("Content-Type: text/html; charset=utf-8");
  echo <<<HTML
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{$title} 🛑</title>
  <style>
    body {
      font-family: sans-serif; background: #111; margin: 0;
      display: flex; align-items: center; justify-content: center;
      height: 100vh; color: #fff; text-align: center;
    }
    .box {
      background: #222; padding: 40px; border-radius: 12px;
      border: 3px solid #e03c31; width: 85%; max-width: 450px;
      box-shadow: 0 10px 30px rgba(224, 60, 49, 0.4);
    }
    .icon { font-size: 80px; margin-bottom: 20px; }
    h1 { color: #e03c31; font-size: 26px; margin-bottom: 15px; }
    p { font-size: 16px; line-height: 1.6; color: #ddd; margin-bottom: 30px; }
    .btn {
      background: #e03c31; color: #fff; padding: 12px 30px;
      border-radius: 5px; text-decoration: none; font-weight: bold;
      font-size: 16px; display: inline-block;
    }
    .btn:hover { background: #c02c21; }
  </style>
</head>
<body>
  <div class="box">
    <div class="icon">🛑</div>
    <h1>{$heading}</h1>
        <p>{$message}</p>
        <div style="display: flex; flex-direction: column; gap: 15px; align-items: center;">
HTML;
    if ($showsebbutton) {
        $cmid = optional_param('id', 0, PARAM_INT) ?: (optional_param('cmid', 0, PARAM_INT) ?: 0);
        $token = md5($USER->id . $cmid . ($CFG->passwordsaltmain ?? 'mseb'));
        $configurl = new moodle_url('/local/mseb/config_seb.php', [
            'id' => $cmid,
            'u' => $USER->id,
            'token' => $token
        ]);
        $seburl = str_replace(['http://', 'https://'], ['seb://', 'sebs://'], $configurl->out(false));
        $sebbuttontext = get_string('blocked_launch_seb', 'local_mseb');
        echo <<<HTML
            <a href="{$seburl}" class="btn" style="background: #28a745;">{$sebbuttontext}</a>
HTML;
    }
    echo <<<HTML
            <a href="javascript:history.back()" class="btn">{$backbutton}</a>
        </div>
    </div>
</body>
</html>
HTML;
  die();
}
