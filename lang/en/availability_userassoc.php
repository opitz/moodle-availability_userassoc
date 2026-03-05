<?php
// This file is part of Moodle - http://moodle.org/
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
 * Language strings.
 *
 * @package availability_userassoc
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Waleed ul hassan <waleed.hassan@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['description'] = 'Restrict access based on the first letter of the custom profile field employee_details (empty = alumnus).';
$string['error_letters'] = 'Enter allowed letters as a comma-separated list (e.g. S,U,P,V,H). Only A–Z and commas are allowed.';
$string['isnotallowed'] = 'Not available for your user association type.';
$string['label'] = 'Allowed letters';
$string['label_help'] = 'Comma-separated letters (e.g. S,U,P,V,H). Users with an empty employee_details field are treated as alumni.';
$string['missing'] = 'You must enter at least one allowed letter.';
$string['pluginname'] = 'User association';
$string['privacy:metadata'] = 'The User association availability condition does not store any personal data.';
$string['requires_employee_details'] = 'This condition uses the custom profile field shortname employee_details.';
$string['requires_letters'] = 'User association first letter is one of: {$a->letters}';
$string['title'] = 'User association';
