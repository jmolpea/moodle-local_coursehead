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

// NOTE: no MOODLE_INTERNAL check is needed in behat step definition files.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException;

/**
 * Behat step definitions for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_local_coursehead extends behat_base {
    /**
     * Returns the endpoint marker for an asset type.
     *
     * @param string $type Either "css" or "js".
     * @return string The endpoint substring expected in the page source.
     */
    private function get_asset_needle(string $type): string {
        return ($type === 'css') ? 'local/coursehead/style.php' : 'local/coursehead/script.php';
    }

    /**
     * Checks that the page source contains the plugin asset tag.
     *
     * @Then /^the page head should contain the local_coursehead "(?P<type>css|js)" asset tag$/
     *
     * @param string $type Either "css" or "js".
     * @return void
     * @throws ExpectationException When the tag is missing.
     */
    public function the_page_head_should_contain_the_asset_tag(string $type): void {
        $content = $this->getSession()->getPage()->getContent();
        $needle = $this->get_asset_needle($type);
        if (strpos($content, $needle) === false) {
            throw new ExpectationException(
                "Expected the page head to contain a '{$needle}' asset tag, but it was not found.",
                $this->getSession()
            );
        }
    }

    /**
     * Checks that the page source does not contain the plugin asset tag.
     *
     * @Then /^the page head should not contain the local_coursehead "(?P<type>css|js)" asset tag$/
     *
     * @param string $type Either "css" or "js".
     * @return void
     * @throws ExpectationException When the tag is present.
     */
    public function the_page_head_should_not_contain_the_asset_tag(string $type): void {
        $content = $this->getSession()->getPage()->getContent();
        $needle = $this->get_asset_needle($type);
        if (strpos($content, $needle) !== false) {
            throw new ExpectationException(
                "Expected the page head to not contain a '{$needle}' asset tag, but it was found.",
                $this->getSession()
            );
        }
    }

    /**
     * Checks that the injected script tag uses the defer attribute.
     *
     * @Then /^the local_coursehead script tag should use the defer attribute$/
     *
     * @return void
     * @throws ExpectationException When the defer attribute is missing.
     */
    public function the_script_tag_should_use_the_defer_attribute(): void {
        $content = $this->getSession()->getPage()->getContent();
        $pattern = '~<script\s+defer\s+src="[^"]*local/coursehead/script\.php~';
        if (!preg_match($pattern, $content)) {
            throw new ExpectationException(
                'Expected a <script defer src="...local/coursehead/script.php..."> tag, but it was not found.',
                $this->getSession()
            );
        }
    }
}
