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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Setting page.
 *
 * @package availability_userassoc
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Waleed ul hassan <waleed.hassan@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Note: For availability plugins Moodle already provides a settings page.
    // This file should only add settings to the existing $settings object.
    $settings->add(new admin_setting_configcheckbox(
        'availability_userassoc/blockempty',
        get_string('setting_blockempty', 'availability_userassoc'),
        get_string('setting_blockempty_desc', 'availability_userassoc'),
        1
    ));
}