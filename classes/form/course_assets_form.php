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

namespace local_coursehead\form;

use html_writer;
use local_coursehead\local\validator;
use local_coursehead\manager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Editing form for per-course custom head assets.
 *
 * Custom data:
 * - courseid (int): the course being edited.
 * - cancss (bool): the user may edit CSS and CSS is globally allowed.
 * - canjs (bool): the user may edit JS and JS is globally allowed.
 * - showwarnings (bool): whether security warnings should be displayed.
 *
 * Fields the user is not allowed to edit are omitted entirely; the manager
 * preserves their stored values on save.
 *
 * @package    local_coursehead
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course_assets_form extends \moodleform {
    /**
     * Defines the form fields.
     *
     * @return void
     */
    protected function definition() {
        $mform = $this->_form;

        $cancss = !empty($this->_customdata['cancss']);
        $canjs = !empty($this->_customdata['canjs']);
        $showwarnings = !empty($this->_customdata['showwarnings']);
        $textareaattributes = [
            'rows' => 12,
            'cols' => 60,
            'spellcheck' => 'false',
            'class' => 'text-monospace font-monospace w-100',
        ];

        $mform->addElement('hidden', 'id', $this->_customdata['courseid']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('header', 'generalheader', get_string('coursesettings', 'local_coursehead'));
        $mform->setExpanded('generalheader', true);

        $mform->addElement('advcheckbox', 'enabled', get_string('enablecourseassets', 'local_coursehead'));
        $mform->setDefault('enabled', 1);

        // CSS section.
        $mform->addElement('header', 'cssheader', get_string('customcss', 'local_coursehead'));
        $mform->setExpanded('cssheader', true);

        if ($cancss) {
            $mform->addElement('advcheckbox', 'cssenabled', get_string('enablecss', 'local_coursehead'));
            $mform->setDefault('cssenabled', 1);

            $mform->addElement(
                'textarea',
                'customcss',
                get_string('customcss', 'local_coursehead'),
                $textareaattributes
            );
            $mform->setType('customcss', PARAM_RAW);
            $mform->addHelpButton('customcss', 'customcss', 'local_coursehead');
        } else {
            $mform->addElement(
                'static',
                'cssunavailable',
                '',
                get_string('csseditingunavailable', 'local_coursehead')
            );
        }

        // JavaScript section.
        $mform->addElement('header', 'jsheader', get_string('customjs', 'local_coursehead'));
        $mform->setExpanded('jsheader', true);

        if ($canjs) {
            if ($showwarnings) {
                $mform->addElement(
                    'static',
                    'jswarning',
                    '',
                    html_writer::div(get_string('jssecuritywarning', 'local_coursehead'), 'alert alert-warning')
                );
            }

            $mform->addElement('advcheckbox', 'jsenabled', get_string('enablejs', 'local_coursehead'));
            $mform->setDefault('jsenabled', 0);

            $mform->addElement(
                'textarea',
                'customjs',
                get_string('customjs', 'local_coursehead'),
                $textareaattributes
            );
            $mform->setType('customjs', PARAM_RAW);
            $mform->addHelpButton('customjs', 'customjs', 'local_coursehead');

            $strategies = [
                'defer' => get_string('strategydefer', 'local_coursehead'),
                'blocking' => get_string('strategyblocking', 'local_coursehead'),
            ];
            $mform->addElement(
                'select',
                'jsloadstrategy',
                get_string('jsloadstrategy', 'local_coursehead'),
                $strategies
            );
            $defaultstrategy = get_config('local_coursehead', 'defaultjsloadstrategy') ?: 'defer';
            $mform->setDefault('jsloadstrategy', $defaultstrategy);
            $mform->addHelpButton('jsloadstrategy', 'jsloadstrategy', 'local_coursehead');
        } else {
            $mform->addElement(
                'static',
                'jsunavailable',
                '',
                get_string('jseditingunavailable', 'local_coursehead')
            );
        }

        $this->add_action_buttons(true, get_string('savechanges'));
    }

    /**
     * Validates the submitted CSS/JS content.
     *
     * @param array $data Submitted data.
     * @param array $files Submitted files.
     * @return array Validation errors keyed by field name.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        $validator = new validator();

        if (isset($data['customcss'])) {
            $csserrors = $validator->validate_css((string) $data['customcss']);
            if (!empty($csserrors)) {
                $errors['customcss'] = implode(' ', $csserrors);
            }
        }

        if (isset($data['customjs'])) {
            $jserrors = $validator->validate_js((string) $data['customjs']);
            if (!empty($jserrors)) {
                $errors['customjs'] = implode(' ', $jserrors);
            }
        }

        if (
            isset($data['jsloadstrategy'])
                && !in_array($data['jsloadstrategy'], manager::JS_LOAD_STRATEGIES, true)
        ) {
            $errors['jsloadstrategy'] = get_string('errorvalidationfailed', 'local_coursehead');
        }

        return $errors;
    }
}
