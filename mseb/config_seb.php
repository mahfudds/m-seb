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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

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
$cmid   = optional_param('id', 0, PARAM_INT);
$token  = optional_param('token', '', PARAM_ALPHANUM);
$userid = optional_param('u', 0, PARAM_INT);

// Use a consistent salt for both sides.
$salt = $CFG->passwordsaltmain ?? 'mseb_default_salt';
$expectedtoken = md5($userid . '|' . $cmid . '|' . $salt);

if (empty($token) || $token !== $expectedtoken) {
    header('HTTP/1.1 403 Forbidden');
    die(get_string('error_access_denied', 'local_mseb'));
}

$cm = get_coursemodule_from_id('quiz', $cmid);
if (!$cm) {
    header('HTTP/1.1 404 Not Found');
    die(get_string('error_quiz_not_found', 'local_mseb'));
}

$quizurl = new moodle_url('/mod/quiz/view.php', ['id' => $cmid]);
$starturl = $quizurl->out(false);

// SEB Configuration XML.
// CRITICAL: allowAAC and lockIPad must be present and set to true for the iOS popup.
$sebconfig = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
    <key>allowAAC</key>
    <true/>
    <key>lockIPad</key>
    <true/>
    <key>startURL</key>
    <string>{$starturl}</string>
    <key>browserWindowWebView</key>
    <integer>3</integer>
    <key>sendBrowserExamKey</key>
    <true/>
    <key>allowQuit</key>
    <false/>
    <key>quitPassword</key>
    <string>mseb123</string>
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
    <key>examSessionClearCookiesOnStart</key>
    <false/>
    <key>browserWindowAllowReload</key>
    <false/>
</dict>
</plist>
XML;

$filename = "mseb_quiz_{$cmid}.seb";

// Clean any output before sending headers.
while (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: application/seb');
header('Content-Disposition: attachment; filename="' . $filename . '"');
echo $sebconfig;
exit();
