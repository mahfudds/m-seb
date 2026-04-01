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
 * Database upgrade steps for local_mseb.
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade the local_mseb plugin.
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool True on success.
 */
function xmldb_local_mseb_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2024030198) {
        $table = new xmldb_table('local_mseb');

        $field = new xmldb_field('minanswered', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0', 'mintime');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024030198, 'local', 'mseb');
    }

    if ($oldversion < 2026040101) {
        $table = new xmldb_table('local_mseb');

        // Add 'facerecognition' field.
        $field = new xmldb_field('facerecognition', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'minanswered');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add 'navsafetimeout' field.
        $field = new xmldb_field('navsafetimeout', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '60', 'facerecognition');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2026040101, 'local', 'mseb');
    }

    return true;
}
