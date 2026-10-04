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
 * Backup support for local_coursehead.
 *
 * Includes the per-course custom head assets configuration in course backups
 * at course level.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Defines the backup structure of the course custom head assets.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_local_coursehead_plugin extends backup_local_plugin {
    /**
     * Adds the plugin structure at course level.
     *
     * The revision and timestamps are backed up for completeness, but the
     * restore step always regenerates the revision: a restored course must
     * never reuse cache-busting identifiers from another environment.
     *
     * @return backup_plugin_element The plugin element.
     */
    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();

        $pluginwrapper = new backup_nested_element($this->get_recommended_name());
        $plugin->add_child($pluginwrapper);

        $coursehead = new backup_nested_element('coursehead', ['id'], [
            'enabled',
            'cssenabled',
            'jsenabled',
            'customcss',
            'customjs',
            'jsloadstrategy',
            'revision',
            'timecreated',
            'timemodified',
        ]);
        $pluginwrapper->add_child($coursehead);

        $coursehead->set_source_table('local_coursehead', ['courseid' => backup::VAR_COURSEID]);

        return $plugin;
    }
}
