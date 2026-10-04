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

#[\PHPUnit\Framework\Attributes\CoversClass(manager::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(observer::class)]
/**
 * Tests for the manager orchestration class.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_coursehead\manager
 * @covers     \local_coursehead\observer
 */
final class manager_test extends \advanced_testcase {
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
     * Saving creates assets and fires the created event.
     */
    public function test_save_creates_assets_and_triggers_created_event(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $sink = $this->redirectEvents();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => 'body { color: blue; }',
        ]);
        $events = $sink->get_events();
        $sink->close();

        $record = $manager->get_course_assets($course->id);
        $this->assertNotNull($record);
        $this->assertSame('body { color: blue; }', $record->customcss);
        $this->assertNotEmpty($record->revision);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(event\course_assets_created::class, $events[0]);
        $this->assertEquals($course->id, $events[0]->courseid);
    }

    /**
     * Updating fires the updated event.
     */
    public function test_update_triggers_updated_event(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, ['customcss' => 'a {}']);

        $sink = $this->redirectEvents();
        $manager->save_course_assets($course->id, ['customcss' => 'b {}']);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(event\course_assets_updated::class, $events[0]);
    }

    /**
     * Deleting removes the record and fires the deleted event.
     */
    public function test_delete_removes_assets_and_triggers_deleted_event(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, ['customcss' => 'a {}']);

        $sink = $this->redirectEvents();
        $manager->delete_course_assets($course->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertNull($manager->get_course_assets($course->id));
        $this->assertCount(1, $events);
        $this->assertInstanceOf(event\course_assets_deleted::class, $events[0]);
    }

    /**
     * Deleting a course without assets is a no-op without events.
     */
    public function test_delete_without_record_is_noop(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $sink = $this->redirectEvents();
        $manager->delete_course_assets($course->id);
        $events = $sink->get_events();
        $sink->close();

        $this->assertCount(0, $events);
    }

    /**
     * The revision changes when the CSS content changes.
     */
    public function test_revision_changes_when_content_changes(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, ['customcss' => 'a { color: red; }']);
        $first = $manager->get_course_assets($course->id)->revision;

        $manager->save_course_assets($course->id, ['customcss' => 'a { color: green; }']);
        $second = $manager->get_course_assets($course->id)->revision;

        $this->assertNotSame($first, $second);
    }

    /**
     * generate_revision is deterministic and content-sensitive.
     */
    public function test_generate_revision_deterministic(): void {
        $this->resetAfterTest();
        $this->enable_plugin();

        $manager = new manager();
        $reva = $manager->generate_revision(5, 'css', 'js', 1000);
        $revb = $manager->generate_revision(5, 'css', 'js', 1000);
        $revc = $manager->generate_revision(5, 'css2', 'js', 1000);
        $revd = $manager->generate_revision(5, 'css', 'js2', 1000);

        $this->assertSame($reva, $revb);
        $this->assertNotSame($reva, $revc);
        $this->assertNotSame($reva, $revd);
        $this->assertSame(16, strlen($reva));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{16}$/', $reva);
    }

    /**
     * Saving content with forbidden tags throws.
     */
    public function test_save_rejects_forbidden_content(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();

        $this->expectException(\moodle_exception::class);
        $manager->save_course_assets($course->id, ['customcss' => '<style>a{}</style>']);
    }

    /**
     * The site course is never injectable.
     */
    public function test_no_injection_for_siteid(): void {
        $this->resetAfterTest();
        $this->enable_plugin();

        $manager = new manager();

        $this->assertFalse($manager->is_course_injectable(SITEID));
        $this->assertSame('', $manager->get_head_html_for_course(SITEID));
    }

    /**
     * The site course is never injectable, even if a record exists for it.
     */
    public function test_no_injection_for_siteid_with_record(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();

        $manager = new manager();
        $manager->save_course_assets(SITEID, ['enabled' => 1, 'cssenabled' => 1, 'customcss' => 'a {}']);

        $this->assertFalse($manager->is_course_injectable(SITEID));
        $this->assertSame('', $manager->get_head_html_for_course(SITEID));
    }

    /**
     * Deleting a course removes its asset record and cached metadata.
     */
    public function test_course_deletion_removes_assets(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, ['enabled' => 1, 'cssenabled' => 1, 'customcss' => 'a {}']);
        $this->assertTrue($manager->is_course_injectable($course->id));

        delete_course($course, false);

        $this->assertNull($manager->get_course_assets($course->id));
        $this->assertNull($manager->get_course_assets_metadata($course->id));
    }

    /**
     * Nothing is injected when the plugin is globally disabled.
     */
    public function test_no_injection_when_plugin_disabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, ['customcss' => 'a {}', 'cssenabled' => 1, 'enabled' => 1]);

        set_config('enabled', 0, 'local_coursehead');
        manager::purge_all_caches();

        $this->assertFalse($manager->is_course_injectable($course->id));
        $this->assertSame('', $manager->get_head_html_for_course($course->id));
    }

    /**
     * Nothing is injected when the course assets are disabled.
     */
    public function test_no_injection_when_course_assets_disabled(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, ['customcss' => 'a {}', 'cssenabled' => 1, 'enabled' => 0]);

        $this->assertFalse($manager->is_course_injectable($course->id));
        $this->assertSame('', $manager->get_head_html_for_course($course->id));
    }

    /**
     * Nothing is injected for courses without any asset record.
     */
    public function test_no_injection_without_record(): void {
        $this->resetAfterTest();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();

        $this->assertFalse($manager->is_course_injectable($course->id));
        $this->assertSame('', $manager->get_head_html_for_course($course->id));
    }

    /**
     * Enabled assets produce the expected link and script tags.
     */
    public function test_head_html_contains_expected_tags(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => 'body { color: blue; }',
            'jsenabled' => 1,
            'customjs' => 'console.log(1);',
        ]);

        $this->assertTrue($manager->is_course_injectable($course->id));
        $html = $manager->get_head_html_for_course($course->id);
        $record = $manager->get_course_assets($course->id);

        $this->assertStringContainsString('<!-- local_coursehead: course custom assets start -->', $html);
        $this->assertStringContainsString('<!-- local_coursehead: course custom assets end -->', $html);
        $this->assertStringContainsString('<link rel="stylesheet"', $html);
        $this->assertStringContainsString('local/coursehead/style.php', $html);
        $this->assertStringContainsString('local/coursehead/script.php', $html);
        $this->assertStringContainsString('rev=' . $record->revision, $html);
        $this->assertStringContainsString('courseid=' . $course->id, $html);
    }

    /**
     * The script tag uses the defer attribute by default.
     */
    public function test_js_defer_attribute_present_by_default(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'jsenabled' => 1,
            'customjs' => 'console.log(1);',
        ]);

        $html = $manager->get_head_html_for_course($course->id);

        $this->assertStringContainsString('<script defer src=', $html);
    }

    /**
     * The blocking strategy removes the defer attribute.
     */
    public function test_blocking_strategy_omits_defer(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'jsenabled' => 1,
            'customjs' => 'console.log(1);',
            'jsloadstrategy' => 'blocking',
        ]);

        $html = $manager->get_head_html_for_course($course->id);

        $this->assertStringContainsString('<script src=', $html);
        $this->assertStringNotContainsString('<script defer', $html);
    }

    /**
     * Disabled JavaScript (global allowjs off) is not injected even when enabled per course.
     */
    public function test_js_not_injected_when_globally_disallowed(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => 'a {}',
            'jsenabled' => 1,
            'customjs' => 'console.log(1);',
        ]);

        set_config('allowjs', 0, 'local_coursehead');
        manager::purge_all_caches();

        $html = $manager->get_head_html_for_course($course->id);

        $this->assertStringContainsString('local/coursehead/style.php', $html);
        $this->assertStringNotContainsString('local/coursehead/script.php', $html);
    }

    /**
     * The served JavaScript starts with the generated header comment.
     */
    public function test_js_content_has_generated_comment(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'jsenabled' => 1,
            'customjs' => 'console.log(1);',
        ]);

        $js = $manager->get_js_content($course->id);

        $this->assertStringStartsWith("/* Generated by local_coursehead for course id {$course->id}. */", $js);
        $this->assertStringContainsString('console.log(1);', $js);
    }

    /**
     * Fields omitted on save keep their stored values (capability split).
     */
    public function test_save_preserves_fields_not_submitted(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->enable_plugin();
        $course = $this->getDataGenerator()->create_course();

        $manager = new manager();
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => 'a {}',
            'jsenabled' => 1,
            'customjs' => 'console.log(1);',
        ]);

        // A user without managejs only submits enabled/CSS fields.
        $manager->save_course_assets($course->id, [
            'enabled' => 1,
            'cssenabled' => 1,
            'customcss' => 'b {}',
        ]);

        $record = $manager->get_course_assets($course->id);

        $this->assertSame('b {}', $record->customcss);
        $this->assertSame('console.log(1);', $record->customjs);
        $this->assertEquals(1, $record->jsenabled);
    }
}
