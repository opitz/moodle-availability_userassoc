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
 * Actual logic of the condition.
 *
 * @package availability_userassoc
 * @copyright 2022 onwards Catalyst IT EU {@link https://catalyst-eu.net}
 * @author    Waleed ul hassan <waleed.hassan@catalyst-eu.net>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace availability_userassoc;

/**
 * Availability condition based on user association letters.
 *
 * Determines access based on the first character of the
 * employee_details custom profile field.
 *
 * @package availability_userassoc
 */
class condition extends \core_availability\condition {
    /** @var string Allowed letters CSV (e.g. "S,U,P,V,H") */
    protected $letters = '';

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode.
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct(\stdClass $structure) {
        if (!isset($structure->letters) || !is_string($structure->letters)) {
            throw new \coding_exception('Missing or invalid property stdClass::$letters');
        }
        $this->letters = trim($structure->letters);
        if ($this->letters === '') {
            throw new \coding_exception('Missing or invalid property stdClass::$letters');
        }
    }

    /**
     * Save this condition back to JSON.
     * NOTE: In Moodle 4.5 this method must have NO parameters.
     *
     * @return \stdClass
     */
    public function save() {
        return (object)[
            'type' => 'userassoc',
            'letters' => $this->letters,
        ];
    }

    /**
     * Determines whether the condition allows access for a given user.
     *
     * @param bool $not Whether the condition is negated
     * @param \core_availability\info $info Availability context information
     * @param bool $grabthelot Whether to fetch all availability info
     * @param int $userid User ID being checked
     * @return bool True if access is allowed
     *
     * @throws \dml_exception
     */
    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        global $DB;

        // Parse allowed letters.
        $allowed = preg_split('/[,\s]+/', strtoupper($this->letters));
        $allowed = array_values(array_filter(array_map('trim', $allowed)));
        $allowed = array_values(array_unique($allowed));

        if (empty($allowed)) {
            // Fail closed if misconfigured.
            $allow = false;
        } else {
            // Empty employee_details means "alumni" => deny by default.
            // Keep setting for backwards compatibility, but default to secure behaviour.
            $rawblockempty = get_config('availability_userassoc', 'blockempty');
            $blockempty = ($rawblockempty === false || $rawblockempty === null || $rawblockempty === '')
                ? true
                : (bool)$rawblockempty;

            // Find the profile field by shortname.
            $fieldid = $DB->get_field(
                'user_info_field',
                'id',
                ['shortname' => 'employee_details'],
                IGNORE_MISSING
            );

            if (!$fieldid) {
                // Field missing: fail open to avoid locking out sites that install the plugin
                // but haven't configured/synced profile data yet.
                $allow = true;
            } else {
                // Read user's custom profile field value.
                $val = (string)$DB->get_field(
                    'user_info_data',
                    'data',
                    [
                        'userid' => $userid,
                        'fieldid' => $fieldid,
                    ],
                    IGNORE_MISSING,
                );
                $val = trim($val);

                if ($val === '') {
                    // Empty means alumni in UCL's model, but make it configurable to avoid lockouts.
                    $allow = !$blockempty;
                } else {
                    $letter = strtoupper(substr($val, 0, 1));
                    $allow = in_array($letter, $allowed, true);
                }
            }
        }

        if ($not) {
            $allow = !$allow;
        }
        return $allow;
    }

    /**
     * Returns a human-readable description of the condition.
     *
     * @param bool $full Whether full information is requested
     * @param bool $not Whether the condition is negated
     * @param \core_availability\info $info Availability context information
     * @return string Description shown to teachers/students
     *
     * @throws \coding_exception
     */
    public function get_description($full, $not, \core_availability\info $info) {
        $a = new \stdClass();
        $a->letters = s(strtoupper($this->letters));
        $desc = get_string('requires_letters', 'availability_userassoc', $a);
        return $not ? 'NOT ' . $desc : $desc;
    }

    /**
     * Returns a short debug representation of the condition.
     *
     * @return string Debug string
     *
     */
    protected function get_debug_string() {
        return 'letters=' . $this->letters;
    }
}
