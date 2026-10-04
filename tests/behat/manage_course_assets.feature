@local @local_coursehead
Feature: Manage course custom head assets
  In order to customise the look and behaviour of individual courses
  As a manager
  I need to define custom CSS and JavaScript per course

  Background:
    Given the following "courses" exist:
      | fullname | shortname | category |
      | Course 1 | C1        | 0        |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | manager1 | Manager   | One      | manager1@example.com |
      | teacher1 | Teacher   | One      | teacher1@example.com |
      | student1 | Student   | One      | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | manager1 | C1     | manager        |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following config values are set as admin:
      | enabled  | 1 | local_coursehead |
      | allowcss | 1 | local_coursehead |

  Scenario: Manager can access the course custom assets page
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    When I navigate to "Course custom head assets" in current page administration
    Then I should see "Course custom head assets"
    And I should see "Enable custom assets for this course"

  Scenario: Teacher cannot access the course custom assets page by default
    Given I log in as "teacher1"
    And I am on "Course 1" course homepage
    Then "Course custom head assets" "link" should not exist in current page administration

  Scenario: Manager can save custom CSS and it is injected into the course page head
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    And I navigate to "Course custom head assets" in current page administration
    When I set the following fields to these values:
      | Enable custom assets for this course | 1                          |
      | Enable custom CSS                    | 1                          |
      | Custom CSS                           | body { background: #fff; } |
    And I press "Save changes"
    Then I should see "Course custom head assets saved."
    When I am on "Course 1" course homepage
    Then the page head should contain the local_coursehead "css" asset tag

  Scenario: Manager can save custom JavaScript when globally enabled and it is injected with defer
    Given the following config values are set as admin:
      | allowjs | 1 | local_coursehead |
    And I log in as "manager1"
    And I am on "Course 1" course homepage
    And I navigate to "Course custom head assets" in current page administration
    When I set the following fields to these values:
      | Enable custom assets for this course | 1                  |
      | Enable custom JavaScript             | 1                  |
      | Custom JavaScript                    | console.log('c1'); |
    And I press "Save changes"
    Then I should see "Course custom head assets saved."
    When I am on "Course 1" course homepage
    Then the page head should contain the local_coursehead "js" asset tag
    And the local_coursehead script tag should use the defer attribute

  Scenario: JavaScript editor is not available when JavaScript is globally disabled
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    When I navigate to "Course custom head assets" in current page administration
    Then I should see "JavaScript editing is not available"

  @javascript
  Scenario: Disabling course assets removes the head tags
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    And I navigate to "Course custom head assets" in current page administration
    And I set the following fields to these values:
      | Enable custom assets for this course | 1                          |
      | Enable custom CSS                    | 1                          |
      | Custom CSS                           | body { background: #fff; } |
    And I press "Save changes"
    And I navigate to "Course custom head assets" in current page administration
    When I set the following fields to these values:
      | Enable custom assets for this course | 0 |
    And I press "Save changes"
    And I am on "Course 1" course homepage
    Then the page head should not contain the local_coursehead "css" asset tag

  Scenario: Deleting course custom assets removes them completely
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    And I navigate to "Course custom head assets" in current page administration
    And I set the following fields to these values:
      | Enable custom assets for this course | 1                          |
      | Enable custom CSS                    | 1                          |
      | Custom CSS                           | body { background: #fff; } |
    And I press "Save changes"
    When I press "Delete course custom assets"
    And I press "Continue"
    Then I should see "Course custom head assets deleted."
    And I should see "No custom assets are configured for this course yet."
    When I am on "Course 1" course homepage
    Then the page head should not contain the local_coursehead "css" asset tag

  Scenario: Invalid CSS containing a style tag is rejected
    Given I log in as "manager1"
    And I am on "Course 1" course homepage
    And I navigate to "Course custom head assets" in current page administration
    When I set the following fields to these values:
      | Enable custom assets for this course | 1                          |
      | Enable custom CSS                    | 1                          |
      | Custom CSS                           | <style>body {}</style>     |
    And I press "Save changes"
    Then I should see "The code must not contain"
