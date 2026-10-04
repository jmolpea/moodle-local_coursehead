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

use local_coursehead\local\validator;

#[\PHPUnit\Framework\Attributes\CoversClass(validator::class)]
/**
 * Tests for the content validator.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_coursehead\local\validator
 */
final class validator_test extends \advanced_testcase {
    /**
     * Valid CSS must produce no errors.
     */
    public function test_valid_css_passes(): void {
        $this->resetAfterTest();
        set_config('maxcsslength', 200000, 'local_coursehead');

        $validator = new validator();
        $css = "body { background-color: #f0f0f0; }\n.coursebox > .title::before { content: '>'; }";

        $this->assertSame([], $validator->validate_css($css));
    }

    /**
     * Valid JavaScript must produce no errors.
     */
    public function test_valid_js_passes(): void {
        $this->resetAfterTest();
        set_config('maxjslength', 200000, 'local_coursehead');

        $validator = new validator();
        $js = "document.addEventListener('DOMContentLoaded', function() {\n    console.log('ready');\n});";

        $this->assertSame([], $validator->validate_js($js));
    }

    /**
     * CSS containing a style tag must be rejected.
     */
    public function test_css_rejects_style_tag(): void {
        $this->resetAfterTest();

        $validator = new validator();
        $errors = $validator->validate_css('<style>body { color: red; }</style>');

        $this->assertNotEmpty($errors);
    }

    /**
     * CSS containing a script tag must be rejected.
     */
    public function test_css_rejects_script_tag(): void {
        $this->resetAfterTest();

        $validator = new validator();
        $errors = $validator->validate_css("body {}\n<script>alert(1)</script>");

        $this->assertNotEmpty($errors);
    }

    /**
     * JavaScript containing a script tag must be rejected.
     */
    public function test_js_rejects_script_tag(): void {
        $this->resetAfterTest();

        $validator = new validator();
        $errors = $validator->validate_js('<script>alert(1)</script>');

        $this->assertNotEmpty($errors);
    }

    /**
     * JavaScript containing a style tag must be rejected.
     */
    public function test_js_rejects_style_tag(): void {
        $this->resetAfterTest();

        $validator = new validator();
        $errors = $validator->validate_js('<style>body{}</style>');

        $this->assertNotEmpty($errors);
    }

    /**
     * PHP open tags must be rejected in both fields.
     */
    public function test_php_tags_rejected(): void {
        $this->resetAfterTest();

        $validator = new validator();

        $this->assertNotEmpty($validator->validate_css('<?php echo 1;'));
        $this->assertNotEmpty($validator->validate_js('<?php echo 1;'));
    }

    /**
     * Forbidden token detection must be case-insensitive.
     */
    public function test_forbidden_tokens_case_insensitive(): void {
        $this->resetAfterTest();

        $validator = new validator();

        $this->assertNotEmpty($validator->validate_js('<SCRIPT>alert(1)</SCRIPT>'));
        $this->assertNotEmpty($validator->validate_css('<StYlE>body{}</StYlE>'));
    }

    /**
     * Content longer than the configured maximum must be rejected.
     */
    public function test_max_length_enforced(): void {
        $this->resetAfterTest();
        set_config('maxcsslength', 10, 'local_coursehead');
        set_config('maxjslength', 10, 'local_coursehead');

        $validator = new validator();

        $this->assertNotEmpty($validator->validate_css('body { color: #112233; }'));
        $this->assertNotEmpty($validator->validate_js("console.log('toolong');"));
        $this->assertSame([], $validator->validate_css('a{}'));
    }

    /**
     * A maximum length of zero means unlimited.
     */
    public function test_zero_max_length_is_unlimited(): void {
        $this->resetAfterTest();
        set_config('maxcsslength', 0, 'local_coursehead');

        $validator = new validator();
        $css = str_repeat('.c{color:red}', 1000);

        $this->assertSame([], $validator->validate_css($css));
    }
}
