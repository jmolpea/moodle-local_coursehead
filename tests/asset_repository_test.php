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

#[\PHPUnit\Framework\Attributes\CoversClass(asset_repository::class)]
/**
 * Tests for the asset repository.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_coursehead\local\asset_repository
 */
final class asset_repository_test extends \advanced_testcase {
    /**
     * Builds a default record object for saving.
     *
     * @param array $overrides Field overrides.
     * @return \stdClass The record data.
     */
    private function make_record(array $overrides = []): \stdClass {
        $record = (object) array_merge([
            'enabled' => 1,
            'cssenabled' => 1,
            'jsenabled' => 0,
            'customcss' => 'body { color: #333; }',
            'customjs' => '',
            'jsloadstrategy' => 'defer',
            'revision' => 'abc123def456abcd',
        ], $overrides);
        return $record;
    }

    /**
     * Saving creates a record that can be read back.
     */
    public function test_save_creates_record(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $repository = new asset_repository();
        $saved = $repository->save($course->id, $this->make_record());

        $this->assertNotEmpty($saved->id);
        $this->assertEquals($course->id, $saved->courseid);
        $this->assertSame('body { color: #333; }', $saved->customcss);
        $this->assertNotEmpty($saved->timecreated);
        $this->assertNotEmpty($saved->timemodified);
        $this->assertTrue($repository->exists_for_course($course->id));
    }

    /**
     * Saving again updates the existing record instead of inserting a new one.
     */
    public function test_save_updates_existing_record(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $repository = new asset_repository();
        $first = $repository->save($course->id, $this->make_record());
        $second = $repository->save($course->id, $this->make_record([
            'customcss' => '.updated { display: none; }',
            'revision' => 'ffff123def456abc',
        ]));

        $this->assertEquals($first->id, $second->id);
        $this->assertSame('.updated { display: none; }', $second->customcss);
        $this->assertEquals($first->timecreated, $second->timecreated);
    }

    /**
     * get_by_courseid returns null for unknown courses.
     */
    public function test_get_by_courseid_returns_null_when_missing(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $repository = new asset_repository();

        $this->assertNull($repository->get_by_courseid($course->id));
        $this->assertFalse($repository->exists_for_course($course->id));
    }

    /**
     * Metadata retrieval must not include the LONGTEXT bodies.
     */
    public function test_metadata_excludes_content_fields(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $repository = new asset_repository();
        $repository->save($course->id, $this->make_record([
            'customjs' => 'console.log(1);',
            'jsenabled' => 1,
        ]));

        $meta = $repository->get_metadata_by_courseid($course->id);

        $this->assertNotNull($meta);
        $this->assertFalse(property_exists($meta, 'customcss'));
        $this->assertFalse(property_exists($meta, 'customjs'));
        $this->assertEquals(1, $meta->hascss);
        $this->assertEquals(1, $meta->hasjs);
        $this->assertSame('defer', $meta->jsloadstrategy);
        $this->assertSame('abc123def456abcd', $meta->revision);
    }

    /**
     * Metadata content indicators must reflect empty bodies.
     */
    public function test_metadata_indicators_for_empty_content(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $repository = new asset_repository();
        $repository->save($course->id, $this->make_record([
            'customcss' => '',
            'customjs' => '',
        ]));

        $meta = $repository->get_metadata_by_courseid($course->id);

        $this->assertEquals(0, $meta->hascss);
        $this->assertEquals(0, $meta->hasjs);
    }

    /**
     * Deletion removes the record.
     */
    public function test_delete_by_courseid(): void {
        $this->resetAfterTest();
        $course = $this->getDataGenerator()->create_course();

        $repository = new asset_repository();
        $repository->save($course->id, $this->make_record());
        $repository->delete_by_courseid($course->id);

        $this->assertNull($repository->get_by_courseid($course->id));
        $this->assertFalse($repository->exists_for_course($course->id));
    }
}
