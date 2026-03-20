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
 * Upgrade steps for availability_userassoc.
 *
 * @package availability_userassoc
 */

/**
 * Execute availability_userassoc upgrade.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_availability_userassoc_upgrade(int $oldversion): bool {
    if ($oldversion < 2026030501) {
        // Remove legacy setting: empty employee_details is now always blocked.
        unset_config('blockempty', 'availability_userassoc');

        upgrade_plugin_savepoint(true, 2026030501, 'availability', 'userassoc');
    }

    return true;
}
