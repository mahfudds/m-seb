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
    throw new moodle_exception('accessdenied', 'local_mseb');
}

$cm = get_coursemodule_from_id('quiz', $cmid);

if (!$cm) {
    throw new moodle_exception('invalidcoursemodule');
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
    <key>mobileAllowQuit</key>
    <false/>
    <key>quitPassword</key>
    <string>mseb123</string>
    <key>mobileQuitPassword</key>
    <string>mseb123</string>
    <key>allowScreenshot</key>
    <false/>
    <key>mobileAllowScreenshot</key>
    <false/>
    <key>allowAAC</key>
    <true/>
    <key>showReloadButton</key>
    <false/>
    <key>showNavigationButtons</key>
    <false/>
    <key>mobileShowReloadButton</key>
    <false/>
    <key>mobileShowNavigationButtons</key>
    <false/>
    <key>mobileShowBack</key>
    <false/>
    <key>mobileShowForward</key>
    <false/>
    <key>mobileShowSettings</key>
    <false/>
    <key>mobileShowMenu</key>
    <false/>
    <key>mobileShowToolbar</key>
    <false/>
    <key>showMenuBar</key>
    <false/>
    <key>showTaskBar</key>
    <false/>
    <key>allowSharing</key>
    <false/>
    <key>mobileAllowSharing</key>
    <false/>
    <key>sendBrowserExamKey</key>
    <true/>
    <key>allowPreferencesWindow</key>
    <false/>
    <key>exitKeyCombinations</key>
    <false/>
    <key>browserViewMode</key>
    <integer>0</integer>
    <key>lockIPad</key>
    <true/>
    <key>allowManualResizing</key>
    <false/>
    <key>monitorSecondControl</key>
    <true/>
    <key>allowUserSwitches</key>
    <false/>
    <key>mobileAllowSpringboardLongPress</key>
    <false/>
</dict>
</plist>
XML;

$filename = "mseb_quiz_{$cmid}.seb";

header('Content-Type: application/seb');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $sebconfig;
exit();
