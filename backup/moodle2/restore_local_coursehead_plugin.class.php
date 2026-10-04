<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Restore support for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Restores the course custom head assets from a course backup.
 *
 * Security model: a backup file is untrusted input. Anyone allowed to restore
 * a course (e.g. an editing teacher) could craft a backup containing arbitrary
 * CSS/JavaScript, so restoring blindly would bypass the plugin capabilities.
 * Therefore each content type is only restored when the restoring user holds
 * the matching editing capability (local/coursehead:managecss /
 * local/coursehead:managejs) in the target course, and the content is
 * re-validated. The revision is always regenerated; existing configuration of
 * the target course is never overwritten.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_local_coursehead_plugin extends restore_local_plugin {
    /**
     * Returns the paths handled by this plugin at course level.
     *
     * @return restore_path_element[] The restore path elements.
     */
    protected function define_course_plugin_structure() {
        return [
            new restore_path_element('coursehead', $this->get_pathfor('/coursehead')),
        ];
    }

    /**
     * Processes one coursehead element from the backup file.
     *
     * @param array|\stdClass $data The parsed element data.
     * @return void
     */
    public function process_coursehead($data) {
        global $DB;

        $data = (object) $data;
        $courseid = (int) $this->task->get_courseid();
        $userid = (int) $this->task->get_userid();
        $context = context_course::instance($courseid);

        // Never overwrite the configuration of the target course.
        if ($DB->record_exists('local_coursehead', ['courseid' => $courseid])) {
            $this->step->log(get_string('restorekeptexisting', 'local_coursehead'), backup::LOG_INFO);
            return;
        }

        $validator = new \local_coursehead\local\validator();

        $css = (string) ($data->customcss ?? '');
        $cssenabled = !empty($data->cssenabled);
        if ($css !== '') {
            $cancss = has_capability('local/coursehead:managecss', $context, $userid);
            if (!$cancss || !empty($validator->validate_css($css))) {
                $css = '';
                $cssenabled = false;
                $this->step->log(get_string('restoredroppedcss', 'local_coursehead'), backup::LOG_WARNING);
            }
        }

        $js = (string) ($data->customjs ?? '');
        $jsenabled = !empty($data->jsenabled);
        if ($js !== '') {
            $canjs = has_capability('local/coursehead:managejs', $context, $userid);
            if (!$canjs || !empty($validator->validate_js($js))) {
                $js = '';
                $jsenabled = false;
                $this->step->log(get_string('restoredroppedjs', 'local_coursehead'), backup::LOG_WARNING);
            }
        }

        // Nothing restorable left: do not create an empty record.
        if ($css === '' && $js === '') {
            return;
        }

        // The manager regenerates the revision, purges the metadata cache and
        // fires the created event attributed to the restoring user.
        $manager = new \local_coursehead\manager();
        $manager->save_course_assets($courseid, [
            'enabled' => !empty($data->enabled),
            'cssenabled' => $cssenabled,
            'customcss' => $css,
            'jsenabled' => $jsenabled,
            'customjs' => $js,
            'jsloadstrategy' => $data->jsloadstrategy ?? 'defer',
        ]);
    }
}
