@availability @availability_userassoc
Feature: Block empty employee_details values
  In order to restrict alumni access correctly
  As a teacher configuring availability_userassoc
  Empty employee_details users should be blocked by default

  Scenario: Empty employee_details is blocked when blockempty config is not yet set
    Given the following "users" exist:
      | username | firstname | lastname | email               | profile_field_employee_details |
      | allowed  | Allowed   | User     | allowed@example.com | S-UCL                          |
      | alumni   | Alumni    | User     | alumni@example.com  |                                |
    And I unset availability_userassoc blockempty config
    Then the availability_userassoc condition with letters "S,U,P,V,H" should be available for user "allowed"
    And the availability_userassoc condition with letters "S,U,P,V,H" should be unavailable for user "alumni"
