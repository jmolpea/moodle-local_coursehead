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

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

#[\PHPUnit\Framework\Attributes\CoversClass(\backup_local_coursehead_plugin::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\restore_local_coursehead_plugin::class)]
/**
 * Tests for the course backup and restore support.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \backup_local_coursehead_plugin
 * @covers     \restore_local_coursehead_plugin
 */
final class backup_restore_test extends \advanced_testcase {
    /**
     * Enables the plugin with permissive defaults for testing.
     */
    private function enable_plugin(): void {
        set_config('enabled', 1, 'local_coursehead');
        set_config('allowcss', 1, 'local_coursehead');
        set_config('allowjs', 1, 'local_coursehead');
        set_config('maxcsslength', 200000, 'local_coursehead');
        set_config('maxjslength', 200000, 'local_coursehead');
        set_config('defaultjsloadstrategy', 'defer', 'local_coursehead');
    }

    /**
     * Backs up a course and restores it.
     *
     * @param \stdClass $course The source course.
     * @param int|null $targetcourseid Restore target course id, or null to create a new course.
     * @return int The id of the restored course.
     */
    private function backup_and_restore(\stdClass $course, ?int $targetcourseid = null): int {
        global $CFG, $USER;

        $CFG->backup_file_logger_level = \backup::LOG_NONE;

        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_IMPORT,
            $USER->id
        );
        $backupid = $bc->get_backupid();
        $bc->execute_plan();
        $bc->destroy();

        if ($targetcourseid === null) {
            $targetcourseid = \restore_dbops::create_new_course(
                $course->fullname . ' copy',
                $course->shortname . '_copy',
                $course->category
            );
            $target = \backup::TARGET_NEW_COURSE;
        } else {
            $target = \backup::TARGET_CURRENT_ADDING;
        }

        $rc = new \restore_controller(
            $backupid,
            $targetcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            $target
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $targetcourseid;
    }

    /**
     * Restoring into a new course carries the asset configuration across.
     */
    public function test_assets_restored_into_new_course(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => 'body { background: #fafafa; }',
            'jsenabled' => 1,
            'customjs' => "console.log('restored');",
            'jsloadstrategy' => 'blocking',
        ]);
        $original = $manager->get_course_assets($course->id);

        $newcourseid = $this->backup_and_restore($course);
        $restored = $manager->get_course_assets($newcourseid);

        $this->assertNotNull($restored);
        $this->assertSame('body { background: #fafafa; }', $restored->customcss);
        $this->assertSame("console.log('restored');", $restored->customjs);
        $this->assertEquals(1, $restored->enabled);
        $this->assertEquals(1, $restored->cssenabled);
        $this->assertEquals(1, $restored->jsenabled);
        $this->assertSame('blocking', $restored->jsloadstrategy);

        // The revision must be regenerated, never copied from the backup.
        $this->assertNotSame($original->revision, $restored->revision);

        // The restored course must be injectable straight away.
        $this->assertTrue($manager->is_course_injectable($newcourseid));
    }

    /**
     * Restoring into a course that already has assets keeps the existing configuration.
     */
    public function test_restore_keeps_existing_assets(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();

        $source = $this->getDataGenerator()->create_course();
        $target = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($source->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => '.source { color: red; }',
        ]);
        $manager->save_course_assets($target->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => '.target { color: green; }',
        ]);

        $this->backup_and_restore($source, (int) $target->id);

        $record = $manager->get_course_assets($target->id);
        $this->assertSame('.target { color: green; }', $record->customcss);
    }

    /**
     * Restoring a course without assets creates no record in the new course.
     */
    public function test_restore_without_assets_creates_no_record(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $newcourseid = $this->backup_and_restore($course);

        $manager = new manager();
        $this->assertNull($manager->get_course_assets($newcourseid));
    }
}
