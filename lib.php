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
 * Legacy Moodle callbacks for local_coursehead.
 *
 * This file is intentionally minimal. All business logic lives in
 * autoloaded classes under classes/. Only callbacks that Moodle core
 * still resolves through lib.php are defined here, and they delegate
 * immediately to the autoloaded API.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Adds the course custom head assets page to the course administration navigation.
 *
 * @param navigation_node $parentnode The course administration node.
 * @param stdClass $course The course record.
 * @param context_course $context The course context.
 * @return void
 */
function local_coursehead_extend_navigation_course(
    navigation_node $parentnode,
    stdClass $course,
    context_course $context
): void {
    if ((int) $course->id === (int) SITEID) {
        return;
    }
    if (!has_capability('local/coursehead:manage', $context)) {
        return;
    }
    $url = new moodle_url('/local/coursehead/manage.php', ['id' => $course->id]);
    $parentnode->add(
        get_string('managecourseassets', 'local_coursehead'),
        $url,
        navigation_node::TYPE_SETTING,
        null,
        'coursehead',
        new pix_icon('i/settings', '')
    );
}

/**
 * Purges the plugin asset metadata caches.
 *
 * Used as the updated-callback of the admin settings so that injection
 * decisions are recalculated after configuration changes.
 *
 * @return void
 */
function local_coursehead_reset_asset_caches(): void {
    \local_coursehead\manager::purge_all_caches();
}
