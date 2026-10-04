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
 * Validates user-supplied CSS and JavaScript content.
 *
 * The content is trusted administrative code, so the validator does not
 * attempt to sanitise it. It only rejects accidental misuse, such as pasting
 * complete HTML documents or PHP code, and enforces the configured maximum
 * lengths. Nothing is silently stripped: invalid content produces explicit
 * validation errors.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class validator {
    /** @var string[] Tokens that must never appear in CSS or JS content. */
    private const FORBIDDEN_TOKENS = [
        '<style',
        '</style',
        '<script',
        '</script',
        '<?php',
        '?>',
    ];

    /**
     * Validates CSS content.
     *
     * @param string $css The CSS code to validate.
     * @return string[] A list of human-readable error messages; empty when valid.
     */
    public function validate_css(string $css): array {
        $errors = $this->check_forbidden_tokens($css);

        $max = (int) get_config('local_coursehead', 'maxcsslength');
        if ($max > 0 && \core_text::strlen($css) > $max) {
            $errors[] = get_string('errortoolong', 'local_coursehead', $max);
        }

        return $errors;
    }

    /**
     * Validates JavaScript content.
     *
     * @param string $js The JavaScript code to validate.
     * @return string[] A list of human-readable error messages; empty when valid.
     */
    public function validate_js(string $js): array {
        $errors = $this->check_forbidden_tokens($js);

        $max = (int) get_config('local_coursehead', 'maxjslength');
        if ($max > 0 && \core_text::strlen($js) > $max) {
            $errors[] = get_string('errortoolong', 'local_coursehead', $max);
        }

        return $errors;
    }

    /**
     * Checks the content for forbidden HTML/PHP tokens.
     *
     * @param string $content The content to inspect.
     * @return string[] Error messages for every forbidden token found.
     */
    private function check_forbidden_tokens(string $content): array {
        $errors = [];
        foreach (self::FORBIDDEN_TOKENS as $token) {
            if (stripos($content, $token) !== false) {
                $errors[] = get_string('errorforbiddentag', 'local_coursehead', s($token));
            }
        }
        return $errors;
    }
}
