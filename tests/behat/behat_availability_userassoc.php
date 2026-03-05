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
 * Behat steps for availability_userassoc.
 *
 * @package availability_userassoc
 */

require_once(__DIR__ . '/../../../../../lib/behat/behat_base.php');

/**
 * Behat steps to validate user association availability checks.
 */
class behat_availability_userassoc extends behat_base {
    /**
     * Unsets plugin config for blockempty.
     *
     * @Given /^I unset availability_userassoc blockempty config$/
     */
    public function i_unset_availability_userassoc_blockempty_config(): void {
        unset_config('blockempty', 'availability_userassoc');
    }

    /**
     * Checks availability for a user and letters list.
     *
     * @Then /^the availability_userassoc condition with letters "(?P<letters_string>[^"]*)" should be (available|unavailable) for user "(?P<username_string>[^"]*)"$/
     * @param string $letters
     * @param string $expected
     * @param string $username
     */
    public function the_availability_userassoc_condition_should_be_for_user(
        string $letters,
        string $expected,
        string $username
    ): void {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/availability/tests/fixtures/mock_info.php');

        $user = $DB->get_record('user', ['username' => $username], '*', MUST_EXIST);
        $condition = new \availability_userassoc\condition((object)[
            'letters' => $letters,
        ]);
        $info = new \core_availability\mock_info();

        $available = $condition->is_available(false, $info, true, (int)$user->id);

        if ($expected === 'available' && !$available) {
            throw new \ExpectationException(
                'Expected condition to be available for user ' . $username,
                $this->getSession()
            );
        }

        if ($expected === 'unavailable' && $available) {
            throw new \ExpectationException(
                'Expected condition to be unavailable for user ' . $username,
                $this->getSession()
            );
        }
    }
}
