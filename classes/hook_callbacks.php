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
 * Hook callbacks for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class hook_callbacks {
    /**
     * Adds the course custom asset tags to the page head.
     *
     * Runs on every page, so it delegates to the manager which performs the
     * cheapest checks first and only reads cached metadata, never the CSS/JS
     * bodies. Any unexpected failure is swallowed (and surfaced through
     * debugging()) so a plugin problem can never break page rendering.
     *
     * @param \core\hook\output\before_standard_head_html_generation $hook The hook instance.
     * @return void
     */
    public static function before_standard_head_html_generation(
        \core\hook\output\before_standard_head_html_generation $hook
    ): void {
        global $PAGE;

        try {
            $manager = new manager();
            if (!$manager->should_inject_for_current_page()) {
                return;
            }
            $html = $manager->get_head_html_for_course((int) $PAGE->course->id);
            if ($html !== '') {
                $hook->add_html($html);
            }
        } catch (\Throwable $exception) {
            // Never let asset injection break page output. Do not include
            // CSS/JS content or other detail in the message.
            debugging('local_coursehead: head injection skipped due to an internal error.', DEBUG_DEVELOPER);
        }
    }
}
