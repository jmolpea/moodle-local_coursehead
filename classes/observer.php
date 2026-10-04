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

namespace local_coursehead;

/**
 * Event observers for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class observer {
    /**
     * Removes the custom head assets of a course that has been deleted.
     *
     * The course context no longer exists at this point, so the record is
     * removed directly through the repository instead of the manager, which
     * would try to fire a course-context event.
     *
     * @param \core\event\course_deleted $event The course deleted event.
     * @return void
     */
    public static function course_deleted(\core\event\course_deleted $event): void {
        $courseid = (int) $event->objectid;

        (new local\asset_repository())->delete_by_courseid($courseid);
        (new manager())->purge_course_cache($courseid);
    }
}
