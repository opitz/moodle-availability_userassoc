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
 * @copyright Waleed ul Hassan <waleed.hassan@catalyst-eu.net>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

        // Create the custom profile field that this condition depends on.
        $this->profilefield = $this->getDataGenerator()->create_custom_profile_field([
            'shortname' => 'employee_details',
            'name' => 'Employee details',
            'datatype' => 'text',
        ]);

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
        global $USER;

        $this->setAdminUser();
        $info = new \core_availability\mock_info();

        $structure = (object)[
            'letters' => 'S,U,P,V,H',
        ];
        $cond = new condition($structure);

        // Admin user: no value => blocked.
        $this->assertFalse($cond->is_available(false, $info, true, $USER->id));

        // Set admin as staff => allowed.
        $this->set_field($USER->id, 'S-UCL');
        $this->assertTrue($cond->is_available(false, $info, true, $USER->id));

        // Another user.
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
     * Helper: set employee_details field value for a user.
     *
     * @param int $userid
     * @param string|null $value Null clears value
     * @throws \dml_exception
     */
    protected function set_field(int $userid, ?string $value): void {
        global $DB;

        $fieldid = (int)$this->profilefield->id;
        $alreadyset = array_key_exists($userid, $this->setusers);

        if (is_null($value)) {
            $DB->delete_records('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid]);
            unset($this->setusers[$userid]);
            return;
        }

        if ($alreadyset) {
            $DB->set_field('user_info_data', 'data', $value, ['userid' => $userid, 'fieldid' => $fieldid]);
        } else {
            $DB->insert_record('user_info_data', ['userid' => $userid, 'fieldid' => $fieldid, 'data' => $value]);
            $this->setusers[$userid] = true;
        }
    }
}
