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
 * Privacy API implementation for local_mseb.
 *
 * The local_mseb plugin stores quiz-level configuration only.
 * It does not store, process, or export any personal user data.
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_mseb\privacy;

/**
 * Privacy provider for local_mseb.
 *
 * This plugin only stores quiz configuration settings (enabled, allowpc, etc.)
 * keyed by quiz ID. No personal data is stored.
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Returns a reason why this plugin does not store any personal data.
     *
     * @return string The language string identifier for the reason.
     */
    public static function get_reason(): string {
        return 'privacy:metadata:local_mseb';
    }
}
