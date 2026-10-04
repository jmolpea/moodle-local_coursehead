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
 * English language strings for local_coursehead.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['allowcss'] = 'Allow custom CSS';
$string['allowcss_desc'] = 'Allow per-course custom CSS to be edited and served.';
$string['allowjs'] = 'Allow custom JavaScript';
$string['allowjs_desc'] = 'Allow per-course custom JavaScript to be edited and served. Custom JavaScript runs in the browser of every user viewing the course; enable it only if the local/coursehead:managejs capability is restricted to fully trusted roles. Disabled by default.';
$string['assetsdeleted'] = 'Course custom head assets deleted.';
$string['assetssaved'] = 'Course custom head assets saved.';
$string['cachedef_assetmeta'] = 'Per-course custom head asset metadata';
$string['coursehead:manage'] = 'Manage course custom head assets';
$string['coursehead:managecss'] = 'Edit course custom CSS';
$string['coursehead:managejs'] = 'Edit course custom JavaScript (trusted users only)';
$string['coursesettings'] = 'Course asset settings';
$string['csseditingunavailable'] = 'CSS editing is not available. Either custom CSS is disabled site-wide or you do not have permission to edit it.';
$string['cssurl'] = 'CSS asset URL';
$string['currentstatus'] = 'Current status';
$string['customcss'] = 'Custom CSS';
$string['customcss_help'] = 'Paste CSS rules only. Do not include &lt;style&gt; tags.';
$string['customjs'] = 'Custom JavaScript';
$string['customjs_help'] = 'Paste JavaScript only. Do not include &lt;script&gt; tags. JavaScript is powerful and should only be enabled for trusted users.';
$string['defaultjsloadstrategy'] = 'Default JavaScript load strategy';
$string['defaultjsloadstrategy_desc'] = 'How the custom script tag is loaded by default for new course configurations. "Defer" is strongly recommended: it does not block page rendering.';
$string['deleteassets'] = 'Delete course custom assets';
$string['deleteconfirm'] = 'Are you sure you want to delete all custom head assets of course "{$a}"? The stored CSS and JavaScript will be removed permanently.';
$string['enablecourseassets'] = 'Enable custom assets for this course';
$string['enablecss'] = 'Enable custom CSS';
$string['enabled'] = 'Enable plugin';
$string['enabled_desc'] = 'Master switch. When disabled, no custom assets are injected or served for any course.';
$string['enablejs'] = 'Enable custom JavaScript';
$string['errorforbiddentag'] = 'The code must not contain "{$a}". Paste plain CSS or JavaScript rules without HTML or PHP tags.';
$string['errortoolong'] = 'The code exceeds the maximum allowed length of {$a} characters.';
$string['errorvalidationfailed'] = 'The submitted code failed validation and was not saved.';
$string['eventcourseassetscreated'] = 'Course custom head assets created';
$string['eventcourseassetsdeleted'] = 'Course custom head assets deleted';
$string['eventcourseassetsupdated'] = 'Course custom head assets updated';
$string['excludedpaths'] = 'Excluded paths';
$string['excludedpaths_desc'] = 'One path prefix per line (relative to the Moodle root). Pages whose path starts with a listed prefix never receive custom assets. Lines starting with # are comments. Example: /mod/quiz/attempt.php';
$string['injectonactivitypages'] = 'Inject on activity pages';
$string['injectonactivitypages_desc'] = 'Inject custom assets on activity (module) pages within the course.';
$string['injectoncoursehome'] = 'Inject on course home';
$string['injectoncoursehome_desc'] = 'Inject custom assets on the course home page.';
$string['injectongradepages'] = 'Inject on grade pages';
$string['injectongradepages_desc'] = 'Inject custom assets on gradebook pages of the course. Disabled by default because grade pages are sensitive and rarely need restyling.';
$string['jseditingunavailable'] = 'JavaScript editing is not available. Either custom JavaScript is disabled site-wide or you do not have permission to edit it.';
$string['jsloadstrategy'] = 'JavaScript load strategy';
$string['jsloadstrategy_help'] = '"Defer" (recommended) loads the script without blocking page rendering and executes it after the document has been parsed. "Blocking" loads and executes the script immediately, pausing page rendering.';
$string['jssecuritywarning'] = 'Custom JavaScript runs for users viewing this course. Grant this permission only to trusted users.';
$string['jsurl'] = 'JavaScript asset URL';
$string['managecourseassets'] = 'Course custom head assets';
$string['maxcsslength'] = 'Maximum CSS length';
$string['maxcsslength_desc'] = 'Maximum number of characters allowed in the custom CSS of a course. Set to 0 for no limit.';
$string['maxjslength'] = 'Maximum JavaScript length';
$string['maxjslength_desc'] = 'Maximum number of characters allowed in the custom JavaScript of a course. Set to 0 for no limit.';
$string['noassets'] = 'No custom assets are configured for this course yet.';
$string['pluginname'] = 'Course custom head assets';
$string['privacy:metadata'] = 'The Course custom head assets plugin stores course-level CSS and JavaScript configuration only and does not store personal data.';
$string['restoredroppedcss'] = 'The custom CSS was not restored: the restoring user lacks the local/coursehead:managecss capability in the target course or the content failed validation.';
$string['restoredroppedjs'] = 'The custom JavaScript was not restored: the restoring user lacks the local/coursehead:managejs capability in the target course or the content failed validation.';
$string['restorekeptexisting'] = 'The target course already has custom head assets; the existing configuration was kept.';
$string['revision'] = 'Revision';
$string['showwarnings'] = 'Show security warnings';
$string['showwarnings_desc'] = 'Show a security warning on the course management page when JavaScript editing is available.';
$string['statusdisabled'] = 'Disabled';
$string['statusenabled'] = 'Enabled';
$string['strategyblocking'] = 'Blocking';
$string['strategydefer'] = 'Defer (recommended)';
