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
 */
function xmldb_availability_userassoc_install(): void {
    global $DB;

    // If field already exists, do nothing.
    if ($DB->record_exists('user_info_field', ['shortname' => 'employee_details'])) {
        return;
    }

    // Pick an existing category (or create one).
    $categoryid = $DB->get_field_sql(
        'SELECT id FROM {user_info_category} ORDER BY sortorder, id',
        [],
        IGNORE_MISSING
    );

    if (!$categoryid) {
        $categoryid = $DB->insert_record('user_info_category', (object)[
            'name' => 'Other fields',
            'sortorder' => 1,
        ]);
    }

    // Put it at the end of the category.
    $sortorder = (int)$DB->get_field_sql(
            'SELECT COALESCE(MAX(sortorder), 0) FROM {user_info_field} WHERE categoryid = ?',
            [$categoryid]
        ) + 1;

    // Create a text custom profile field: hidden + locked by default.
    $DB->insert_record('user_info_field', (object)[
        'shortname' => 'employee_details',
        'name' => 'Employee details',
        'datatype' => 'text',
        'description' => '',
        'descriptionformat' => FORMAT_HTML,
        'categoryid' => $categoryid,
        'sortorder' => $sortorder,
        'required' => 0,
        'locked' => 1,
        'visible' => 0, // Hidden.
        'forceunique' => 0,
        'signup' => 0,
        'defaultdata' => '',
        'defaultdataformat' => FORMAT_HTML,
        'param1' => 255, // Max length for text field.
        'param2' => 0,
        'param3' => 0,
        'param4' => '',
        'param5' => '',
    ]);
}