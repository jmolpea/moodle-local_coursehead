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
 * Admin settings for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_coursehead', get_string('pluginname', 'local_coursehead'));
    $ADMIN->add('localplugins', $settings);

    if ($ADMIN->fulltree) {
        $enabled = new admin_setting_configcheckbox(
            'local_coursehead/enabled',
            get_string('enabled', 'local_coursehead'),
            get_string('enabled_desc', 'local_coursehead'),
            1
        );
        $enabled->set_updatedcallback('local_coursehead_reset_asset_caches');
        $settings->add($enabled);

        $allowcss = new admin_setting_configcheckbox(
            'local_coursehead/allowcss',
            get_string('allowcss', 'local_coursehead'),
            get_string('allowcss_desc', 'local_coursehead'),
            1
        );
        $allowcss->set_updatedcallback('local_coursehead_reset_asset_caches');
        $settings->add($allowcss);

        // Custom JavaScript is disabled by default: enabling it means trusted
        // capability holders can run JavaScript for every course participant.
        $allowjs = new admin_setting_configcheckbox(
            'local_coursehead/allowjs',
            get_string('allowjs', 'local_coursehead'),
            get_string('allowjs_desc', 'local_coursehead'),
            0
        );
        $allowjs->set_updatedcallback('local_coursehead_reset_asset_caches');
        $settings->add($allowjs);

        $settings->add(new admin_setting_configcheckbox(
            'local_coursehead/injectoncoursehome',
            get_string('injectoncoursehome', 'local_coursehead'),
            get_string('injectoncoursehome_desc', 'local_coursehead'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursehead/injectonactivitypages',
            get_string('injectonactivitypages', 'local_coursehead'),
            get_string('injectonactivitypages_desc', 'local_coursehead'),
            1
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursehead/injectongradepages',
            get_string('injectongradepages', 'local_coursehead'),
            get_string('injectongradepages_desc', 'local_coursehead'),
            0
        ));

        $settings->add(new admin_setting_configtextarea(
            'local_coursehead/excludedpaths',
            get_string('excludedpaths', 'local_coursehead'),
            get_string('excludedpaths_desc', 'local_coursehead'),
            "/mod/quiz/attempt.php\n",
            PARAM_RAW
        ));

        $settings->add(new admin_setting_configtext(
            'local_coursehead/maxcsslength',
            get_string('maxcsslength', 'local_coursehead'),
            get_string('maxcsslength_desc', 'local_coursehead'),
            200000,
            PARAM_INT
        ));

        $settings->add(new admin_setting_configtext(
            'local_coursehead/maxjslength',
            get_string('maxjslength', 'local_coursehead'),
            get_string('maxjslength_desc', 'local_coursehead'),
            200000,
            PARAM_INT
        ));

        $settings->add(new admin_setting_configcheckbox(
            'local_coursehead/showwarnings',
            get_string('showwarnings', 'local_coursehead'),
            get_string('showwarnings_desc', 'local_coursehead'),
            1
        ));

        $settings->add(new admin_setting_configselect(
            'local_coursehead/defaultjsloadstrategy',
            get_string('defaultjsloadstrategy', 'local_coursehead'),
            get_string('defaultjsloadstrategy_desc', 'local_coursehead'),
            'defer',
            [
                'defer' => get_string('strategydefer', 'local_coursehead'),
                'blocking' => get_string('strategyblocking', 'local_coursehead'),
            ]
        ));
    }
}
