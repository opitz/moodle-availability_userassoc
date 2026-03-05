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
 * Install file to ensure the profile field exists.
 *
 * @package availability_userassoc
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Waleed ul hassan <waleed.hassan@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Install hook for availability_userassoc.
 *
 * Ensures required custom profile field exists.
 * @throws dml_exception
 */
function xmldb_availability_userassoc_install(): void {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/user/profile/lib.php');

    $shortname = 'employee_details';

    // Default to blocking empty employee_details values (alumni model).
    if (get_config('availability_userassoc', 'blockempty') === false) {
        set_config('blockempty', 1, 'availability_userassoc');
    }

    // If the field already exists, do nothing further.
    if ($DB->record_exists('user_info_field', ['shortname' => $shortname])) {
        return;
    }

    // Find or create a category for the field.
    $categoryname = 'User association';
    $category = $DB->get_record('user_info_category', ['name' => $categoryname]);

    if (!$category) {
        $category = new stdClass();
        $category->name = $categoryname;
        $category->sortorder = 999; // Put near bottom by default.
        profile_save_category($category);
    }

    // Create the text field using the profile API.
    $field = new stdClass();
    $field->datatype = 'text';
    $field->shortname = $shortname;
    $field->name = 'Employee details';
    $field->description = '';
    $field->descriptionformat = FORMAT_HTML;

    $field->categoryid = $category->id;

    // Field behaviour.
    $field->required = 0;
    $field->locked = 0;
    $field->forceunique = 0;
    $field->signup = 0;
    $field->visible = PROFILE_VISIBLE_PRIVATE; // Adjust if you want.
    $field->defaultdata = '';
    $field->defaultdataformat = FORMAT_HTML;

    // Text-field specific config (param1/param2 are length etc for text fields).
    // Moodle's UI uses these, and profile_save_field will store them.
    $field->param1 = 30;   // Display size.
    $field->param2 = 2048; // Max length.

    profile_save_field($field);
}