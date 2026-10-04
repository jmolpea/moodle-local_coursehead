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

namespace local_coursehead\privacy;

/**
 * Privacy provider for local_coursehead.
 *
 * The plugin stores course-level CSS/JavaScript configuration only. It does
 * not store any user ids or other personal data in its own tables. Audit
 * trails rely on standard Moodle events handled by the logging subsystem,
 * which implements its own privacy support.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements \core_privacy\local\metadata\null_provider {
    /**
     * Returns the language string identifier explaining why no data is stored.
     *
     * @return string The string identifier.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
