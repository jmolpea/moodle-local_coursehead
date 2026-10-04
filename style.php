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
 * Serves the per-course custom CSS.
 *
 * Access is restricted to users who can access the course: assets are never
 * exposed to users outside the course. Responses are private-cacheable and
 * revision-aware.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);

require(__DIR__ . '/../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$rev = optional_param('rev', '', PARAM_ALPHANUMEXT);

$senderror = function (int $code): void {
    $statusline = ($code === 403) ? 'HTTP/1.1 403 Forbidden' : 'HTTP/1.1 404 Not Found';
    header($statusline);
    header('Cache-Control: private, no-store, max-age=0');
    header('Content-Type: text/css; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    exit;
};

if (!get_config('local_coursehead', 'enabled') || !get_config('local_coursehead', 'allowcss')) {
    $senderror(404);
}

$course = $DB->get_record('course', ['id' => $courseid]);
if (!$course || (int) $course->id === (int) SITEID) {
    $senderror(404);
}

// Course access check without redirects: an asset request must answer with an
// HTTP status, never with a login page.
try {
    require_login($course, true, null, false, true);
} catch (Throwable $exception) {
    $senderror(403);
}

$manager = new \local_coursehead\manager();
$record = $manager->get_course_assets($courseid);
if (!$record || empty($record->enabled) || empty($record->cssenabled)) {
    $senderror(404);
}

$content = $manager->get_css_content($courseid);
if (trim($content) === '') {
    $senderror(404);
}

// The response does not depend on the session beyond the access check.
\core\session\manager::write_close();

$etag = '"' . $record->revision . '"';
if (!empty($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH']) === $etag) {
    header('HTTP/1.1 304 Not Modified');
    header('Etag: ' . $etag);
    exit;
}

if ($rev !== '' && $rev === $record->revision) {
    // Revisioned URL: safe to cache for a long time. Private because access
    // is course-restricted and must not be served from shared caches.
    header('Cache-Control: private, max-age=604800, immutable');
} else {
    // Unrevisioned or stale URL: serve the current content but force
    // revalidation so browsers pick up changes immediately.
    header('Cache-Control: private, no-cache, must-revalidate');
}

header('Etag: ' . $etag);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', (int) $record->timemodified) . ' GMT');
header('Content-Type: text/css; charset=utf-8');
header('X-Content-Type-Options: nosniff');

echo $content;
exit;
