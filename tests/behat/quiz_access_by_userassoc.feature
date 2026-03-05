@availability @availability_userassoc @mod_quiz
Feature: Restrict quiz access by employee_details first letter
  In order to control access for students and alumni
  As a teacher
  I need quizzes restricted to matching employee_details prefixes

  Scenario: Student with S-UCL can access quiz while alumni with empty field cannot
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email               | profile_field_employee_details |
      | student1 | Student   | One      | student1@example.com | S-UCL                         |
      | alumni1  | Alumni    | One      | alumni1@example.com  |                               |
    And the following "course enrolments" exist:
      | user     | course | role    |
      | student1 | C1     | student |
      | alumni1  | C1     | student |
    And the following "activities" exist:
      | activity | name   | course |
      | quiz     | Quiz 1 | C1     |
    And quiz "Quiz 1" is restricted to availability_userassoc letters "S"

    When I am on the "Course 1" "course" page logged in as "student1"
    Then I should see "Quiz 1"

    When I am on the "Course 1" "course" page logged in as "alumni1"
    Then I should see "Not available for your user association type."
