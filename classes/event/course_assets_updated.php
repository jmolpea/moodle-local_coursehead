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

namespace local_coursehead\event;

/**
 * Event fired when course custom head assets are updated.
 *
 * The event deliberately carries no CSS/JS content: only the course context
 * and the record id are logged.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_assets_updated extends \core\event\base {
    /**
     * Initialises the event data.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'local_coursehead';
    }

    /**
     * Returns the localised event name.
     *
     * @return string The event name.
     */
    public static function get_name() {
        return get_string('eventcourseassetsupdated', 'local_coursehead');
    }

    /**
     * Returns a non-localised description of what happened.
     *
     * @return string The event description.
     */
    public function get_description() {
        return "The user with id '$this->userid' updated the custom head assets of the course with id '$this->courseid'.";
    }

    /**
     * Returns the URL related to this event.
     *
     * @return \moodle_url The management page of the course.
     */
    public function get_url() {
        return new \moodle_url('/local/coursehead/manage.php', ['id' => $this->courseid]);
    }

    /**
     * Validates the event data.
     *
     * @return void
     * @throws \coding_exception When the objectid is missing.
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->objectid)) {
            throw new \coding_exception('The \'objectid\' must be set.');
        }
    }

    /**
     * Returns the objectid mapping for backup and restore.
     *
     * @return array The mapping definition.
     */
    public static function get_objectid_mapping() {
        return ['db' => 'local_coursehead', 'restore' => \core\event\base::NOT_MAPPED];
    }
}
