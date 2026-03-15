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
 * Language strings for local_mseb.
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Plugin metadata.
$string['pluginname'] = 'M-SEB Quiz Lock';
$string['plugindescription'] = 'M-SEB (Moodle Secure Exam Browser) enforces secure quiz-taking on Android via the M-SEB app, with optional iOS and PC JS-based proctoring, minimum time enforcement, and minimum answered question requirements.';

// Privacy API.
$string['privacy:metadata:local_mseb'] = 'The M-SEB plugin stores quiz configuration settings. It does not store any personal user data.';

// Form header and field labels.
$string['msebheader'] = 'M-SEB';
$string['mseb_enabled'] = 'Enable M-SEB Lock';
$string['mseb_enabled_desc'] = 'Check to block regular Android browsers from accessing this quiz.';
$string['mseb_allowpc'] = 'Allow regular PC/Laptop (Google Chrome)';
$string['mseb_allowpc_desc'] = 'Allow regular laptops to open the quiz without Safe Exam Browser.';
$string['mseb_protectpc'] = 'Enable JS Guard on PC/Laptop';
$string['mseb_protectpc_desc'] = 'Apply penalty timer (like iOS) if a laptop user switches tabs or minimises the window.';
$string['mseb_allowios'] = 'Allow iOS Users (Pro Guard JS)';
$string['mseb_allowios_desc'] = 'Allow iPhone/iPad users with unlimited penalty timer (no auto-submit).';
$string['mseb_mintime'] = 'Minimum working time (minutes)';
$string['mseb_mintime_help'] = 'Students cannot click the Finish button before this many minutes have elapsed. Set to 0 to disable.';
$string['mseb_minanswered'] = 'Minimum answered questions (%)';
$string['mseb_minanswered_help'] = 'Minimum percentage of questions that must be answered before the submit button appears (0–100).';

// JS-facing strings (passed to AMD modules).
$string['js:violation'] = 'EXAM VIOLATION';
$string['js:violationcount'] = 'Violations';
$string['js:detected'] = 'You have been detected: {$a}.';
$string['js:penaltynotice'] = 'This is violation #{$a}. A time penalty has been applied.';
$string['js:leavingexam'] = 'Leaving the exam screen';
$string['js:autotranslate'] = 'Automatic translation detected';
$string['js:warningnotmet'] = 'Warning: The quiz completion requirements have not been met yet!';

// Block messages.
$string['blocked_generic'] = 'This quiz may only be taken through the official <b>M-SEB</b> app (Android).';
$string['blocked_ios'] = 'Sorry, iOS (iPhone/iPad) users are currently blocked for this quiz.';
$string['blocked_android'] = 'SORRY! This quiz must be taken through the <b>M-SEB</b> app. You are detected as using a regular browser.';
$string['blocked_pc'] = 'This quiz cannot be taken through a regular laptop browser. Please use <b>Safe Exam Browser (SEB)</b> or the M-SEB app.';
$string['blocked_title'] = 'ACCESS DENIED';
$string['blocked_back'] = 'GO BACK';
$string['blocked_locked'] = 'LOCKED';
