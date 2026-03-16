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
 * Language strings for local_mseb (English).
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['blocked_android'] = 'SORRY! This quiz must be taken through the <b>M-SEB</b> app. You are detected as using a regular browser.';
$string['blocked_back'] = 'GO BACK';
$string['blocked_generic'] = 'This quiz may only be taken through the official <b>M-SEB</b> (Android) app.';
$string['blocked_ios_seb'] = 'For iOS (iPhone/iPad), this quiz must be taken through the <b>Safe Exam Browser</b> app.';
$string['blocked_launch_seb'] = 'OPEN IN SAFE EXAM BROWSER';
$string['blocked_locked'] = 'LOCKED';
$string['blocked_pc'] = 'This quiz cannot be taken through a regular laptop browser. Please use <b>Safe Exam Browser (SEB)</b> or the M-SEB app.';
$string['blocked_title'] = 'ACCESS BLOCKED';
$string['error_access_denied'] = 'M-SEB: Access Denied. Invalid security token.';
$string['error_quiz_not_found'] = 'M-SEB: Quiz not found.';
$string['js:autotranslate'] = 'Automatic translation detected';
$string['js:leavingexam'] = 'Leaving the exam area';
$string['js:penalty_level'] = 'Penalty Level';
$string['js:sanction_continued'] = 'Continued Sanction';
$string['js:violation'] = 'DETECTION VIOLATION';
$string['js:violationcount'] = 'Violations';
$string['mseb_allowios'] = 'ALLOW iOS (SAFARI/CHROME)';
$string['mseb_allowios_desc'] = 'Allow iPhone users with Pro Guard JS protection (Penalty Timer).';
$string['mseb_allowpc'] = 'ALLOW LAPTOP / PC (Chrome)';
$string['mseb_allowpc_desc'] = 'Allow access via Google Chrome on Laptops.';
$string['mseb_enabled'] = 'ENABLE M-SEB LOCK';
$string['mseb_enabled_desc'] = 'Enforce the use of the M-SEB (Android) app or block regular browsers.';
$string['mseb_minanswered'] = 'MINIMUM ANSWERED QUESTIONS (%)';
$string['mseb_minanswered_help'] = 'Minimum percentage of questions that must be answered before the submit button appears (0-100).';
$string['mseb_mintime'] = 'MINIMUM WORKING TIME (MINUTES)';
$string['mseb_mintime_help'] = 'Students cannot finish the quiz before X minutes have passed. Set to 0 to disable.';
$string['mseb_protectpc'] = 'ENABLE JS GUARD ON PC';
$string['mseb_protectpc_desc'] = 'Use Penalty Timer if PC switches tabs.';
$string['msebheader'] = 'M-SEB PROCTORING';
$string['plugindescription'] = 'M-SEB (Moodle Secure Exam Browser) enforces secure quiz-taking on Android via the M-SEB app, with optional iOS and PC JS-based proctoring.';
$string['pluginname'] = 'M-SEB Quiz Lock';
$string['privacy:metadata:local_mseb'] = 'The M-SEB plugin only stores configuration settings for quizzes and does not store any personal data about users.';
