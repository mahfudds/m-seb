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
 * Backup steps for local_mseb.
 *
 * @package    local_mseb
 * @copyright  2024 M-SEB Kemenag
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Backup structure step that writes the local_mseb settings per quiz.
 */
class backup_local_mseb_plugin extends backup_local_plugin {

    /**
     * Define the backup structure for local_mseb.
     *
     * @return backup_plugin_element The plugin element with nested structure.
     */
    protected function define_module_plugin_structure() {
        // Define the plugin element.
        $plugin = $this->get_plugin_element(null, null, null);

        // Create the wrapper element.
        $wrapper = new backup_nested_element('local_mseb_settings');
        $plugin->add_child($wrapper);

        // Define the fields to back up.
        $mseb = new backup_nested_element('mseb', ['id'], [
            'quizid',
            'enabled',
            'allowpc',
            'protectpc',
            'allowios',
            'mintime',
            'minanswered',
        ]);
        $wrapper->add_child($mseb);

        // Get the quiz instance ID from the course module being backed up.
        $mseb->set_source_sql(
            'SELECT * FROM {local_mseb} WHERE quizid = ?',
            [backup::VAR_ACTIVITYID]
        );

        return $plugin;
    }
}
