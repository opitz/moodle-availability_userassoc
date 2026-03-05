@availability @availability_userassoc
Feature: Block empty employee_details values
  In order to restrict alumni access correctly
  As a teacher configuring availability_userassoc
  Empty employee_details users should be blocked by default

  Background:
    Given the following "custom field categories" exist:
      | name | component   | area   | itemid |
      | CLC  | core_course | course | 0      |
    And the following "custom fields" exist:
      | name        | shortname   | category | type |
      | Course Year | course_year | CLC      | text |
    And the following "courses" exist:
      | fullname | shortname | format | customfield_course_year |
      | Course 1 | C1        | topics | ##now##%Y##             |
    And the following "users" exist:
      | username | firstname | lastname | email                 | profile_field_employee_details  |
      | teacher1 | teacher   | 1        | teacher1@example.com  |                                 |
      | allowed  | Allowed   | User     | allowed@example.com   | S-UCL                           |
      | alumni   | Alumni    | User     | alumni@example.com    |                                 |
    And the following "course enrolments" exist:
      | user      | course | role           |
      | teacher1  | C1     | editingteacher |
      | allowed   | C1     | student        |
      | alumni    | C1     | student        |
    And I log in as "admin"
    And I add a quiz activity to course "Course 1" section "3" and I fill the form with:
      | Name                    | Test quiz                                      |
      | Formative or summative? | Formative - does not contribute to course mark |
      | Description             | Test quiz description                          |
      | Grade to pass           | 8                                              |
    And the following config values are set as admin:
      | blockempty | 1 | availability_userassoc |

  @javascript
  Scenario: Users with empty profile employee details (alumni) are blocked from user association restricted modules
    Given I am on the "Course 1" "course" page logged in as "admin"
    And I follow "Test quiz"
    And I follow "Settings"
    And I click on "Expand all" "link" in the "region-main" "region"
    And I press "Add restriction"
    And I press "User association"
    And I set the field "User association" to "S"
    And I press "Save and return to course"
    And I log out

    # Allowed user should be able to access the module
    Given I am on the "Course 1" "course" page logged in as "allowed"
    And I follow "Test quiz"
    Then I should see "Test quiz description"
    And I log out

    # Alumni should not be able to access the module
    Given I am on the "Course 1" "course" page logged in as "alumni"
    Then I should see "Not available unless: User association first letter is one of: S"
