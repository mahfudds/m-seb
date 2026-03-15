<?php
/**
 * Dynamic SEB configuration generator for Assessment Mode (iOS/PC).
 *
 * This file generates a .seb configuration file that forces the Safe Exam Browser
 * into Assessment Mode, locking the device and preventing screenshots.
 *
 * @package    local_mseb
 * @copyright  2024 M-SEB
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(__DIR__ . '/../../config.php');

// Security check: Validate the token using a consistent salt.
$cmid  = optional_param('id', 0, PARAM_INT);
$token = optional_param('token', '', PARAM_ALPHANUM);
$userid = optional_param('u', 0, PARAM_INT);

// Use a consistent salt for both sides.
$salt = $CFG->passwordsaltmain ?? 'mseb_default_salt';
$expectedtoken = md5($userid . '|' . $cmid . '|' . $salt);

if (empty($token) || $token !== $expectedtoken) {
    header('HTTP/1.1 403 Forbidden');
    die('M-SEB: Access Denied. Invalid security token.');
}

$cm = get_coursemodule_from_id('quiz', $cmid);

if (!$cm) {
    header('HTTP/1.1 404 Not Found');
    die('M-SEB: Quiz not found.');
}

$quizurl = new moodle_url('/mod/quiz/view.php', ['id' => $cmid]);
$starturl = $quizurl->out(false);

// SEB Configuration XML.
// Key features: allowAAC=true (Assessment Mode), allowScreenshot=false, allowDictation=false.
$sebconfig = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>startURL</key>
    <string>{$starturl}</string>

    <key>sendBrowserExamKey</key>
    <true/>

    <key>quitPassword</key>
    <string>mseb123</string>

    <key>allowQuit</key>
    <false/>
    <key>allowPreferencesWindow</key>
    <false/>

    <key>allowAAC</key>
    <true/>
    <key>lockIPad</key>
    <true/>

    <key>allowScreenshot</key>
    <false/>

    <key>showReloadButton</key>
    <false/>
    <key>showNavigationButtons</key>
    <false/>
    <key>showTaskBar</key>
    <false/>
    <key>showMenuBar</key>
    <false/>

    <key>browserViewMode</key>
    <integer>0</integer>
    <key>allowManualResizing</key>
    <false/>
    <key>monitorSecondControl</key>
    <true/>
</dict>
</plist>
XML;

$filename = "mseb_quiz_{$cmid}.seb";

header('Content-Type: application/seb');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $sebconfig;
exit();
