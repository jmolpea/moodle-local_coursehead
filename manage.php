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
 * Course-level management page for custom head assets.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');

use local_coursehead\form\course_assets_form;
use local_coursehead\manager;

$courseid = required_param('id', PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

if ($courseid === (int) SITEID) {
    throw new moodle_exception('invalidcourse', 'error');
}

$course = get_course($courseid);
require_login($course);
$context = context_course::instance($course->id);
require_capability('local/coursehead:manage', $context);
$coursename = format_string($course->fullname, true, ['context' => $context]);

$manager = new manager();
$cancss = has_capability('local/coursehead:managecss', $context) && manager::css_allowed();
$canjs = has_capability('local/coursehead:managejs', $context) && manager::js_allowed();
$showwarnings = (bool) get_config('local_coursehead', 'showwarnings');

$pageurl = new moodle_url('/local/coursehead/manage.php', ['id' => $course->id]);
$courseurl = new moodle_url('/course/view.php', ['id' => $course->id]);

$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('managecourseassets', 'local_coursehead'));
$PAGE->set_heading($coursename);

// Delete / reset handling.
if ($delete) {
    if ($confirm) {
        require_sesskey();
        $manager->delete_course_assets($course->id);
        redirect(
            $pageurl,
            get_string('assetsdeleted', 'local_coursehead'),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('managecourseassets', 'local_coursehead'));
    $confirmurl = new moodle_url($pageurl, ['delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()]);
    echo $OUTPUT->confirm(
        get_string('deleteconfirm', 'local_coursehead', $coursename),
        $confirmurl,
        $pageurl
    );
    echo $OUTPUT->footer();
    die;
}

$existing = $manager->get_course_assets($course->id);

$form = new course_assets_form($pageurl->out(false), [
    'courseid' => $course->id,
    'cancss' => $cancss,
    'canjs' => $canjs,
    'showwarnings' => $showwarnings,
]);

// Pre-populate the form. The record id must not leak into the hidden course
// id field, so the data object is assembled explicitly.
$toform = ['id' => $course->id];
if ($existing) {
    $toform['enabled'] = $existing->enabled;
    if ($cancss) {
        $toform['cssenabled'] = $existing->cssenabled;
        $toform['customcss'] = $existing->customcss;
    }
    if ($canjs) {
        $toform['jsenabled'] = $existing->jsenabled;
        $toform['customjs'] = $existing->customjs;
        $toform['jsloadstrategy'] = $existing->jsloadstrategy;
    }
}
$form->set_data($toform);

if ($form->is_cancelled()) {
    redirect($courseurl);
}

if ($data = $form->get_data()) {
    // Capability checks again at processing time: only include the fields the
    // current user is allowed to change. Everything else keeps its stored value.
    $assets = ['enabled' => !empty($data->enabled)];
    if ($cancss) {
        $assets['cssenabled'] = !empty($data->cssenabled);
        $assets['customcss'] = $data->customcss ?? '';
    }
    if ($canjs) {
        $assets['jsenabled'] = !empty($data->jsenabled);
        $assets['customjs'] = $data->customjs ?? '';
        $assets['jsloadstrategy'] = $data->jsloadstrategy ?? 'defer';
    }
    $manager->save_course_assets($course->id, $assets);

    redirect(
        $pageurl,
        get_string('assetssaved', 'local_coursehead'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$PAGE->requires->js_call_amd('local_coursehead/course_assets_form', 'init');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecourseassets', 'local_coursehead'));

// Status panel.
$meta = $manager->get_course_assets_metadata($course->id);
$templatedata = (object) [
    'coursename' => $coursename,
    'hasassets' => !empty($meta),
    'enabled' => !empty($meta->enabled),
    'hascss' => !empty($meta) && !empty($meta->cssenabled) && !empty($meta->hascss) && manager::css_allowed(),
    'hasjs' => !empty($meta) && !empty($meta->jsenabled) && !empty($meta->hasjs) && manager::js_allowed(),
    'cssurl' => !empty($meta) ? $manager->get_css_url($course->id)->out(false) : '',
    'jsurl' => !empty($meta) ? $manager->get_js_url($course->id)->out(false) : '',
    'revision' => $meta->revision ?? '',
    'showwarning' => $showwarnings && $canjs,
];

/** @var \local_coursehead\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_coursehead');
echo $renderer->course_assets_page($templatedata);

$form->display();

if ($existing) {
    $deleteurl = new moodle_url($pageurl, ['delete' => 1]);
    echo $OUTPUT->single_button($deleteurl, get_string('deleteassets', 'local_coursehead'), 'get');
}

echo $OUTPUT->footer();
