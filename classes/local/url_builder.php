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

namespace local_coursehead\local;

use moodle_url;

/**
 * Builds the URLs of the plugin asset-serving endpoints.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class url_builder {
    /**
     * Returns the URL of the CSS endpoint for a course.
     *
     * @param int $courseid The course id.
     * @param string $revision The current asset revision for cache busting.
     * @return moodle_url The stylesheet URL.
     */
    public static function css_url(int $courseid, string $revision): moodle_url {
        return new moodle_url('/local/coursehead/style.php', [
            'courseid' => $courseid,
            'rev' => $revision,
        ]);
    }

    /**
     * Returns the URL of the JavaScript endpoint for a course.
     *
     * @param int $courseid The course id.
     * @param string $revision The current asset revision for cache busting.
     * @return moodle_url The script URL.
     */
    public static function js_url(int $courseid, string $revision): moodle_url {
        return new moodle_url('/local/coursehead/script.php', [
            'courseid' => $courseid,
            'rev' => $revision,
        ]);
    }
}
