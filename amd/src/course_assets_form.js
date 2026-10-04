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
 * UI niceties for the course custom head assets form.
 *
 * Shows a security warning notification when JavaScript is enabled for a
 * course. The form is fully functional without this module.
 *
 * @module     local_coursehead/course_assets_form
 * @copyright  2026 Pluginia <jmolpea@gmail.com>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {getString} from 'core/str';
import Notification from 'core/notification';

const SELECTORS = {
    JS_ENABLED_CHECKBOX: 'input[type="checkbox"][name="jsenabled"]',
};

/**
 * Initialises the form enhancements.
 */
export const init = () => {
    const checkbox = document.querySelector(SELECTORS.JS_ENABLED_CHECKBOX);
    if (!checkbox) {
        return;
    }

    checkbox.addEventListener('change', async(event) => {
        if (!event.target.checked) {
            return;
        }
        try {
            const message = await getString('jssecuritywarning', 'local_coursehead');
            await Notification.addNotification({
                message: message,
                type: 'warning',
            });
        } catch (error) {
            Notification.exception(error);
        }
    });
};
