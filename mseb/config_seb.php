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

// Security check: Validate the token to prevent brute-forcing cmids.
$cmid  = optional_param('id', 0, PARAM_INT);
$token = optional_param('token', '', PARAM_ALPHANUM);
$userid = optional_param('u', 0, PARAM_INT);
$expectedtoken = md5($userid . $cmid . ($CFG->passwordsaltmain ?? 'mseb'));

if ($token !== $expectedtoken) {
    print_error('accessdenied', 'local_mseb');
}

$cm = get_coursemodule_from_id('quiz', $cmid);

if (!$cm) {
    print_error('invalidcoursemodule');
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
    <key>allowQuit</key>
    <false/>
    <key>showReloadButton</key>
    <false/>
    <key>showNavigationButtons</key>
    <false/>
    <key>sendBrowserExamKey</key>
    <true/>
    <key>allowAAC</key>
    <true/>
    <key>allowScreenshot</key>
    <false/>
    <key>allowPreferencesWindow</key>
    <false/>
    <key>exitKeyCombinations</key>
    <false/>
    <key>showTaskBar</key>
    <false/>
    <key>browserViewMode</key>
    <integer>0</integer>
    <key>lockIPad</key>
    <true/>
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
