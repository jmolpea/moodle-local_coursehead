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

use local_coursehead\local\asset_repository;
use local_coursehead\local\url_builder;
use local_coursehead\local\validator;
use moodle_url;

/**
 * Main orchestration class of the local_coursehead plugin.
 *
 * Decides whether assets should be injected for the current page, builds the
 * head HTML, and coordinates saving/deleting course assets including events,
 * cache invalidation and revision generation.
 *
 * The head-injection path is performance-critical (it runs on every page),
 * therefore it only ever reads cached lightweight metadata and never the
 * LONGTEXT CSS/JS bodies. Bodies are only loaded by style.php/script.php.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {
    /** @var string Cache sentinel stored when a course has no asset record. */
    private const CACHE_NO_RECORD = 'norecord';

    /** @var string[] Page layouts on which assets are never injected. */
    private const EXCLUDED_LAYOUTS = ['login', 'maintenance', 'redirect', 'embedded', 'admin'];

    /** @var string[] Valid JavaScript load strategies. */
    public const JS_LOAD_STRATEGIES = ['defer', 'blocking'];

    /** @var asset_repository The data access layer. */
    private asset_repository $repository;

    /**
     * Constructor.
     *
     * @param asset_repository|null $repository Repository instance, or null for the default one.
     */
    public function __construct(?asset_repository $repository = null) {
        $this->repository = $repository ?? new asset_repository();
    }

    /**
     * Checks whether the plugin is globally enabled.
     *
     * @return bool True when the plugin is enabled.
     */
    public static function is_enabled(): bool {
        return (bool) get_config('local_coursehead', 'enabled');
    }

    /**
     * Checks whether serving custom CSS is globally allowed.
     *
     * @return bool True when CSS is allowed.
     */
    public static function css_allowed(): bool {
        return (bool) get_config('local_coursehead', 'allowcss');
    }

    /**
     * Checks whether serving custom JavaScript is globally allowed.
     *
     * @return bool True when JavaScript is allowed.
     */
    public static function js_allowed(): bool {
        return (bool) get_config('local_coursehead', 'allowjs');
    }

    /**
     * Decides whether assets should be injected into the current page head.
     *
     * Designed to return early as fast as possible: most pages exit on the
     * first few cheap checks before any database or cache access happens.
     *
     * @return bool True when the current page should receive course assets.
     */
    public function should_inject_for_current_page(): bool {
        global $CFG, $PAGE;

        // Never inject outside normal web page rendering.
        if (
            (defined('CLI_SCRIPT') && CLI_SCRIPT)
                || (defined('AJAX_SCRIPT') && AJAX_SCRIPT)
                || (defined('WS_SERVER') && WS_SERVER)
        ) {
            return false;
        }
        if (during_initial_install() || !empty($CFG->upgraderunning)) {
            return false;
        }
        if (!self::is_enabled()) {
            return false;
        }
        if (!isset($PAGE)) {
            return false;
        }
        // Note: $PAGE->course is a magic property; never test it with
        // empty()/isset(), always read it into a local variable first.
        $course = $PAGE->course;
        if (!$course || (int) $course->id === (int) SITEID) {
            return false;
        }
        if (in_array($PAGE->pagelayout, self::EXCLUDED_LAYOUTS, true)) {
            return false;
        }

        // The page must genuinely belong to this course: require a course or
        // module context whose course matches $PAGE->course. This prevents
        // injection on system pages where $PAGE->course is populated
        // accidentally.
        $context = $PAGE->context;
        if (!$context) {
            return false;
        }
        $coursecontext = $context->get_course_context(false);
        if (!$coursecontext || (int) $coursecontext->instanceid !== (int) $course->id) {
            return false;
        }

        $path = self::get_current_relative_path();
        if ($path === '' || self::path_is_excluded($path)) {
            return false;
        }
        $adminprefix = '/' . (empty($CFG->admin) ? 'admin' : $CFG->admin) . '/';
        if (strpos($path, $adminprefix) === 0) {
            return false;
        }

        // Page-type gating against the admin settings.
        if (strpos($path, '/grade/') === 0) {
            if (!get_config('local_coursehead', 'injectongradepages')) {
                return false;
            }
        } else if ((int) $context->contextlevel === CONTEXT_MODULE) {
            if (!get_config('local_coursehead', 'injectonactivitypages')) {
                return false;
            }
        } else if (strpos($PAGE->pagetype, 'course-view') === 0) {
            if (!get_config('local_coursehead', 'injectoncoursehome')) {
                return false;
            }
        }

        return $this->is_course_injectable((int) $course->id);
    }

    /**
     * Checks whether a specific course has enabled assets worth injecting.
     *
     * Uses only cached metadata; never loads the CSS/JS bodies.
     *
     * @param int $courseid The course id.
     * @return bool True when at least one asset would be served for the course.
     */
    public function is_course_injectable(int $courseid): bool {
        if (!self::is_enabled()) {
            return false;
        }
        if ($courseid <= 0 || $courseid === (int) SITEID) {
            return false;
        }

        $meta = $this->get_cached_metadata($courseid);
        if (!$meta || empty($meta->enabled)) {
            return false;
        }

        $servecss = self::css_allowed() && !empty($meta->cssenabled) && !empty($meta->hascss);
        $servejs = self::js_allowed() && !empty($meta->jsenabled) && !empty($meta->hasjs);

        return $servecss || $servejs;
    }

    /**
     * Builds the HTML to add to the page head for a course.
     *
     * @param int $courseid The course id.
     * @return string The head HTML, or an empty string when nothing should be injected.
     */
    public function get_head_html_for_course(int $courseid): string {
        if (!$this->is_course_injectable($courseid)) {
            return '';
        }

        $meta = $this->get_cached_metadata($courseid);
        $lines = ['<!-- local_coursehead: course custom assets start -->'];

        if (self::css_allowed() && !empty($meta->cssenabled) && !empty($meta->hascss)) {
            $cssurl = url_builder::css_url($courseid, $meta->revision);
            $lines[] = '<link rel="stylesheet" href="' . $cssurl->out() . '">';
        }

        if (self::js_allowed() && !empty($meta->jsenabled) && !empty($meta->hasjs)) {
            $jsurl = url_builder::js_url($courseid, $meta->revision);
            $deferattr = ($meta->jsloadstrategy === 'blocking') ? '' : 'defer ';
            $lines[] = '<script ' . $deferattr . 'src="' . $jsurl->out() . '"></script>';
        }

        $lines[] = '<!-- local_coursehead: course custom assets end -->';

        return implode("\n", $lines) . "\n";
    }

    /**
     * Returns the CSS endpoint URL for a course.
     *
     * @param int $courseid The course id.
     * @return moodle_url The stylesheet URL.
     */
    public function get_css_url(int $courseid): moodle_url {
        $meta = $this->get_cached_metadata($courseid);
        return url_builder::css_url($courseid, $meta->revision ?? '');
    }

    /**
     * Returns the JavaScript endpoint URL for a course.
     *
     * @param int $courseid The course id.
     * @return moodle_url The script URL.
     */
    public function get_js_url(int $courseid): moodle_url {
        $meta = $this->get_cached_metadata($courseid);
        return url_builder::js_url($courseid, $meta->revision ?? '');
    }

    /**
     * Returns the stored CSS content for a course.
     *
     * @param int $courseid The course id.
     * @return string The CSS, or an empty string when none is stored.
     */
    public function get_css_content(int $courseid): string {
        $record = $this->repository->get_by_courseid($courseid);
        return (string) ($record->customcss ?? '');
    }

    /**
     * Returns the stored JavaScript content for a course.
     *
     * A short generated header comment is prepended. No user-specific data is
     * ever included in the served JavaScript.
     *
     * @param int $courseid The course id.
     * @return string The JavaScript, or an empty string when none is stored.
     */
    public function get_js_content(int $courseid): string {
        $record = $this->repository->get_by_courseid($courseid);
        $js = (string) ($record->customjs ?? '');
        if ($js === '') {
            return '';
        }
        return "/* Generated by local_coursehead for course id {$courseid}. */\n" . $js;
    }

    /**
     * Returns the full asset record for a course.
     *
     * @param int $courseid The course id.
     * @return \stdClass|null The record or null if none exists.
     */
    public function get_course_assets(int $courseid): ?\stdClass {
        return $this->repository->get_by_courseid($courseid);
    }

    /**
     * Returns the cached metadata for a course (without CSS/JS bodies).
     *
     * @param int $courseid The course id.
     * @return \stdClass|null The metadata or null if no record exists.
     */
    public function get_course_assets_metadata(int $courseid): ?\stdClass {
        return $this->get_cached_metadata($courseid);
    }

    /**
     * Saves (creates or updates) the custom assets of a course.
     *
     * Fields missing from $data (because the editing user lacks the matching
     * capability and the form omitted them) keep their previously stored
     * values. Content is re-validated as a defence-in-depth measure.
     *
     * @param int $courseid The course id.
     * @param array $data Asset data: enabled, cssenabled, customcss, jsenabled, customjs, jsloadstrategy.
     * @return void
     * @throws \moodle_exception When the content fails validation.
     */
    public function save_course_assets(int $courseid, array $data): void {
        $existing = $this->repository->get_by_courseid($courseid);

        $record = new \stdClass();
        $record->enabled = array_key_exists('enabled', $data)
            ? (int) !empty($data['enabled'])
            : (int) ($existing->enabled ?? 1);
        $record->cssenabled = array_key_exists('cssenabled', $data)
            ? (int) !empty($data['cssenabled'])
            : (int) ($existing->cssenabled ?? 1);
        $record->jsenabled = array_key_exists('jsenabled', $data)
            ? (int) !empty($data['jsenabled'])
            : (int) ($existing->jsenabled ?? 0);
        $record->customcss = array_key_exists('customcss', $data)
            ? (string) $data['customcss']
            : (string) ($existing->customcss ?? '');
        $record->customjs = array_key_exists('customjs', $data)
            ? (string) $data['customjs']
            : (string) ($existing->customjs ?? '');

        $defaultstrategy = get_config('local_coursehead', 'defaultjsloadstrategy') ?: 'defer';
        $strategy = $data['jsloadstrategy'] ?? ($existing->jsloadstrategy ?? $defaultstrategy);
        $record->jsloadstrategy = in_array($strategy, self::JS_LOAD_STRATEGIES, true) ? $strategy : 'defer';

        $validator = new validator();
        $errors = array_merge(
            $validator->validate_css($record->customcss),
            $validator->validate_js($record->customjs)
        );
        if (!empty($errors)) {
            throw new \moodle_exception('errorvalidationfailed', 'local_coursehead');
        }

        $now = time();
        $record->timemodified = $now;
        $record->revision = $this->generate_revision($courseid, $record->customcss, $record->customjs, $now);

        $saved = $this->repository->save($courseid, $record);
        $this->purge_course_cache($courseid);

        $eventclass = $existing
            ? event\course_assets_updated::class
            : event\course_assets_created::class;
        $event = $eventclass::create([
            'context' => \context_course::instance($courseid),
            'objectid' => $saved->id,
        ]);
        $event->trigger();
    }

    /**
     * Deletes the custom assets of a course.
     *
     * @param int $courseid The course id.
     * @return void
     */
    public function delete_course_assets(int $courseid): void {
        $existing = $this->repository->get_by_courseid($courseid);
        if (!$existing) {
            return;
        }

        $this->repository->delete_by_courseid($courseid);
        $this->purge_course_cache($courseid);

        $event = event\course_assets_deleted::create([
            'context' => \context_course::instance($courseid),
            'objectid' => $existing->id,
        ]);
        $event->trigger();
    }

    /**
     * Generates the deterministic cache-busting revision for course assets.
     *
     * The revision changes whenever the CSS, the JS, the modification time or
     * the plugin version changes.
     *
     * @param int $courseid The course id.
     * @param string $css The CSS content.
     * @param string $js The JavaScript content.
     * @param int $timemodified The modification timestamp.
     * @return string A 16-character hexadecimal revision string.
     */
    public function generate_revision(int $courseid, string $css, string $js, int $timemodified): string {
        $pluginversion = (string) get_config('local_coursehead', 'version');
        $source = implode('|', [
            $courseid,
            hash('sha256', $css),
            hash('sha256', $js),
            $timemodified,
            $pluginversion,
        ]);
        return substr(hash('sha256', $source), 0, 16);
    }

    /**
     * Purges the cached metadata of a single course.
     *
     * @param int $courseid The course id.
     * @return void
     */
    public function purge_course_cache(int $courseid): void {
        \cache::make('local_coursehead', 'assetmeta')->delete($courseid);
    }

    /**
     * Purges all cached asset metadata, e.g. after admin settings changes.
     *
     * @return void
     */
    public static function purge_all_caches(): void {
        \cache::make('local_coursehead', 'assetmeta')->purge();
    }

    /**
     * Reads the course metadata through the MUC cache.
     *
     * @param int $courseid The course id.
     * @return \stdClass|null The metadata or null when the course has no record.
     */
    private function get_cached_metadata(int $courseid): ?\stdClass {
        $cache = \cache::make('local_coursehead', 'assetmeta');

        $cached = $cache->get($courseid);
        if ($cached === self::CACHE_NO_RECORD) {
            return null;
        }
        if ($cached instanceof \stdClass) {
            return $cached;
        }

        $meta = $this->repository->get_metadata_by_courseid($courseid);
        $cache->set($courseid, $meta ?? self::CACHE_NO_RECORD);
        return $meta;
    }

    /**
     * Returns the current page path relative to the Moodle wwwroot.
     *
     * @return string The relative path, or an empty string when unavailable.
     */
    private static function get_current_relative_path(): string {
        global $CFG, $PAGE;

        if (!$PAGE->has_set_url()) {
            return '';
        }
        $path = $PAGE->url->get_path();
        $root = (string) parse_url($CFG->wwwroot, PHP_URL_PATH);
        if ($root !== '' && $root !== '/' && strpos($path, $root) === 0) {
            $path = substr($path, strlen($root));
        }
        return $path;
    }

    /**
     * Checks the path against the configured excluded path prefixes.
     *
     * Each non-empty line of the setting is treated as a path prefix.
     * Lines starting with "#" are comments.
     *
     * @param string $path The wwwroot-relative path of the current page.
     * @return bool True when the path is excluded from injection.
     */
    private static function path_is_excluded(string $path): bool {
        $config = (string) get_config('local_coursehead', 'excludedpaths');
        if (trim($config) === '') {
            return false;
        }

        foreach (preg_split('/\R/', $config) as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }
            if (strpos($path, $line) === 0) {
                return true;
            }
        }
        return false;
    }
}
