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
 * Unit tests for the user association (employee_details) condition.
 *
 * @package availability_userassoc
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Waleed ul hassan <waleed.hassan@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_userassoc;

/**
 * Tests for the availability_userassoc condition.
 *
 * @package availability_userassoc
 */
final class condition_test extends \advanced_testcase {
    /** @var \stdClass Custom profile field record for employee_details */
    protected $profilefield;

    /** @var array Users we already inserted user_info_data for */
    protected $setusers = [];

    public function setUp(): void {
        global $CFG;
        parent::setUp();
        $this->resetAfterTest();


        // Load the mock info class so that it can be used.
        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');
    }

    /**
     * Tests condition evaluation when used inside an availability tree.
     *
     * @covers ::is_available
     * @throws \dml_exception
     */
    public function test_in_tree(): void {
        global $USER;

        $this->setAdminUser();
        set_config('blockempty', 1, 'availability_userassoc');

        $info = new \core_availability\mock_info();

        $structure = (object)[
            'op' => '|',
            'show' => true,
            'c' => [
                (object)[
                    'type' => 'userassoc',
                    'letters' => 'S,U,P,V,H',
                ],
            ],
        ];

        $tree = new \core_availability\tree($structure);

        // Initial check (no employee_details set => alumni => should be blocked).
        $result = $tree->check_available(false, $info, true, $USER->id);
        $this->assertFalse($result->is_available());

        // Set employee_details to staff.
        $this->set_field($USER->id, 'S-UCL');

        // Now allowed.
        $result = $tree->check_available(false, $info, true, $USER->id);
        $this->assertTrue($result->is_available());
    }

    /**
     * Tests constructor validation and debug string output.
     *
     * @covers ::__construct
     * @covers ::get_debug_string
     * @throws \coding_exception
     */
    public function test_constructor(): void {
        // Missing letters.
        $structure = new \stdClass();
        try {
            new condition($structure);
            $this->fail('Expected coding_exception for missing letters');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('letters', $e->getMessage());
        }

        // Letters not string.
        $structure->letters = false;
        try {
            new condition($structure);
            $this->fail('Expected coding_exception for invalid letters type');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('letters', $e->getMessage());
        }

        // Empty letters string.
        $structure->letters = '   ';
        try {
            new condition($structure);
            $this->fail('Expected coding_exception for empty letters');
        } catch (\coding_exception $e) {
            $this->assertStringContainsString('letters', $e->getMessage());
        }

        // Valid.
        $structure->letters = 'S,U';
        $cond = new condition($structure);
        $this->assertNotEmpty($cond);
    }

    /**
     * Tests availability evaluation for allowed and disallowed users.
     *
     * @covers ::is_available
     * @covers ::get_description
     * @throws \coding_exception
     */
    public function test_save(): void {
        $structure = (object)[
            'letters' => 'S,U,P',
        ];
        $cond = new condition($structure);

        $expected = (object)[
            'type' => 'userassoc',
            'letters' => 'S,U,P',
        ];
        $this->assertEquals($expected, $cond->save());
    }

    /**
     * Tests availability evaluation for allowed and disallowed users.
     *
     * @covers ::is_available
     * @covers ::get_description
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function test_is_available(): void {

        set_config('blockempty', 1, 'availability_userassoc');
        $info = new \core_availability\mock_info();

        $structure = (object)[
            'letters' => 'S,U,P,V,H',
        ];
        $cond = new condition($structure);

        // User.
        $user = $this->getDataGenerator()->create_user();

        // No value => blocked.
        $this->assertFalse($cond->is_available(false, $info, true, $user->id));

        // Set to U => allowed.
        $this->set_field($user->id, 'U-ABC');
        $this->assertTrue($cond->is_available(false, $info, true, $user->id));

        // Set to X => blocked.
        $this->set_field($user->id, 'X-NOTVALID');
        $this->assertFalse($cond->is_available(false, $info, true, $user->id));

        // NOT logic flips it.
        $this->assertTrue($cond->is_available(true, $info, true, $user->id));
    }


    /**
     * Sets a custom profile field value for a user (creates the field if needed).
     *
     * By default this targets the 'employee_details' custom field used by this plugin.
     *
     * @param int $userid User ID
     * @param string|null $value Value to set, or null to delete the record
     * @param string $shortname Custom profile field shortname
     */
    protected function set_field(int $userid, ?string $value, string $shortname = 'employee_details'): void {
        global $DB;

        // Ensure the field exists.
        $fieldid = (int)$DB->get_field('user_info_field', 'id', ['shortname' => $shortname], IGNORE_MISSING);
        if (!$fieldid) {
            // Ensure there is at least one category.
            $categoryid = (int)$DB->get_field_sql(
                'SELECT id FROM {user_info_category} ORDER BY sortorder, id',
                [],
                IGNORE_MISSING
            );
            if (!$categoryid) {
                $categoryid = (int)$DB->insert_record('user_info_category', (object)[
                    'name' => 'Other fields',
                    'sortorder' => 1,
                ]);
            }

            // Add the field.
            $sortorder = (int)$DB->get_field_sql(
                'SELECT COALESCE(MAX(sortorder), 0) FROM {user_info_field} WHERE categoryid = ?',
                [$categoryid]
            ) + 1;

            $fieldid = (int)$DB->insert_record('user_info_field', (object)[
                'shortname' => $shortname,
                'name' => 'Employee details',
                'datatype' => 'text',
                'description' => '',
                'descriptionformat' => FORMAT_HTML,
                'categoryid' => $categoryid,
                'sortorder' => $sortorder,
                'required' => 0,
                'locked' => 0,
                'visible' => 0,
                'forceunique' => 0,
                'signup' => 0,
                'defaultdata' => '',
                'defaultdataformat' => FORMAT_HTML,
                'param1' => 255,
                'param2' => 0,
                'param3' => 0,
                'param4' => '',
                'param5' => '',
            ]);
        }

        // If null, delete any existing record.
        if ($value === null) {
            $DB->delete_records('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid]);
            return;
        }

        // Upsert: update if exists, insert if not.
        $params = ['userid' => $userid, 'fieldid' => $fieldid];
        $existingid = $DB->get_field('user_info_data', 'id', $params, IGNORE_MISSING);

        if ($existingid) {
            $DB->set_field('user_info_data', 'data', $value, ['id' => $existingid]);
        } else {
            $DB->insert_record('user_info_data', (object)($params + ['data' => $value]));
        }
    }
}
