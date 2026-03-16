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
 * Restore steps for local_mseb.
 *
 * @package  local_mseb
 * @copyright 2024 M-SEB 
 * @license  http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Restore plugin class for local_mseb.
 */
class restore_local_mseb_plugin extends restore_local_plugin {

  /**
   * Define the restore structure for local_mseb.
   *
   * @return restore_path_element[] Array of restore path elements.
   */
  protected function define_module_plugin_structure() {
    $paths = [];

    $paths[] = new restore_path_element(
      'mseb',
      $this->get_pathfor('/local_mseb_settings/mseb')
    );

    return $paths;
  }

  /**
   * Process the restored local_mseb settings.
   *
   * @param array $data The data from the backup file.
   */
  public function process_mseb($data) {
    global $DB;

    $data = (object) $data;

    // Map the old quiz ID to the new one.
    $newquizid = $this->get_mappingid('quiz', $data->quizid);
    if (!$newquizid) {
      // If we can't map, use the task's activity ID (the new quiz instance).
      $newquizid = $this->task->get_activityid();
    }

    // Check if a record already exists for this quiz.
    $existing = $DB->get_record('local_mseb', ['quizid' => $newquizid]);
    if ($existing) {
      $existing->enabled = $data->enabled;
      $existing->allowpc = $data->allowpc;
      $existing->protectpc = $data->protectpc;
      $existing->allowios = $data->allowios;
      $existing->mintime = $data->mintime;
      $existing->minanswered = $data->minanswered;
      $DB->update_record('local_mseb', $existing);
    } else {
      $newrecord = new \stdClass();
      $newrecord->quizid = $newquizid;
      $newrecord->enabled = $data->enabled;
      $newrecord->allowpc = $data->allowpc;
      $newrecord->protectpc = $data->protectpc;
      $newrecord->allowios = $data->allowios;
      $newrecord->mintime = $data->mintime;
      $newrecord->minanswered = $data->minanswered;
      $DB->insert_record('local_mseb', $newrecord);
    }
  }
}
