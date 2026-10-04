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

/**
 * Data access layer for per-course custom head assets.
 *
 * All database access for the local_coursehead table is concentrated here.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class asset_repository {
    /** @var string The plugin database table name. */
    public const TABLE = 'local_coursehead';

    /**
     * Returns the full asset record for a course, including CSS/JS bodies.
     *
     * @param int $courseid The course id.
     * @return \stdClass|null The record or null if none exists.
     */
    public function get_by_courseid(int $courseid): ?\stdClass {
        global $DB;

        $record = $DB->get_record(self::TABLE, ['courseid' => $courseid]);
        return $record ?: null;
    }

    /**
     * Returns lightweight metadata for a course without loading the LONGTEXT bodies.
     *
     * The returned object contains the flags, revision, load strategy and the
     * derived indicators "hascss" and "hasjs" but deliberately not the
     * customcss/customjs contents, so it is cheap enough for the head hook.
     *
     * @param int $courseid The course id.
     * @return \stdClass|null The metadata record or null if none exists.
     */
    public function get_metadata_by_courseid(int $courseid): ?\stdClass {
        global $DB;

        $hascss = $DB->sql_isnotempty(self::TABLE, 'customcss', true, true);
        $hasjs = $DB->sql_isnotempty(self::TABLE, 'customjs', true, true);

        $sql = "SELECT id, courseid, enabled, cssenabled, jsenabled, jsloadstrategy, revision, timemodified,
                       CASE WHEN {$hascss} THEN 1 ELSE 0 END AS hascss,
                       CASE WHEN {$hasjs} THEN 1 ELSE 0 END AS hasjs
                  FROM {" . self::TABLE . "}
                 WHERE courseid = :courseid";

        $record = $DB->get_record_sql($sql, ['courseid' => $courseid]);
        return $record ?: null;
    }

    /**
     * Inserts or updates the asset record for a course.
     *
     * @param int $courseid The course id.
     * @param \stdClass $data Record data (without id/courseid/timecreated handling).
     * @return \stdClass The stored record, freshly read from the database.
     */
    public function save(int $courseid, \stdClass $data): \stdClass {
        global $DB;

        $now = empty($data->timemodified) ? time() : (int) $data->timemodified;
        $existing = $this->get_by_courseid($courseid);

        if ($existing) {
            $data->id = $existing->id;
            $data->courseid = $courseid;
            $data->timecreated = $existing->timecreated;
            $data->timemodified = $now;
            $DB->update_record(self::TABLE, $data);
        } else {
            unset($data->id);
            $data->courseid = $courseid;
            $data->timecreated = $now;
            $data->timemodified = $now;
            $data->id = $DB->insert_record(self::TABLE, $data);
        }

        return $this->get_by_courseid($courseid);
    }

    /**
     * Deletes the asset record for a course.
     *
     * @param int $courseid The course id.
     * @return void
     */
    public function delete_by_courseid(int $courseid): void {
        global $DB;

        $DB->delete_records(self::TABLE, ['courseid' => $courseid]);
    }

    /**
     * Checks whether an asset record exists for a course.
     *
     * @param int $courseid The course id.
     * @return bool True if a record exists.
     */
    public function exists_for_course(int $courseid): bool {
        global $DB;

        return $DB->record_exists(self::TABLE, ['courseid' => $courseid]);
    }
}
