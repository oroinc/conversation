@fixture-OroUserBundle:users.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Conversation on activity list
  In order to manage the activity lists
  As a Administrator
  I need to be able to work with Conversations in the activity list

  Scenario: Create new Conversation
    Given I login as administrator
    And I go to System/User Management/Users
    And I click view "John" in grid
    And follow "More actions"
    And follow "Start conversation"
    And I fill form with:
      | NAME          | Some test conversation |
      | Customer User | Amanda Cole            |
      | Context       | [admin, charlie]       |
    And I press "Save"
    Then I should see "Saved successfully" flash message
    And should see "Some test conversation" conversation in activity list

  Scenario: View conversation activity list item
    When I collapse "Some test conversation" in activity list
    Then I should see Name Some test conversation text in activity
    And I should see Owner John Doe text in activity

  Scenario: Add new message
    When I click "Add Message"
    And I fill form with:
      | Message  | First Message |
    And I click "Save"
    Then I should see "Message created successfully" flash message
    And I should see "User John Doe added"
    And I should see "First Message"

  Scenario: Try to add new message with empty body
    When I click "Add Message"
    And I click "Save"
    Then I should see validation errors:
      | Message | This value should not be blank. |

  Scenario: Add new message from another user
    When I fill form with:
      | Message  | Second Message |
      | From     | charlie |
    And I click "Save"
    Then I should see "Message created successfully" flash message
    And I should see "User John Doe added"
    And I should see "First Message"
    And I should see "User Charlie Sheen added"
    And I should see "Second Message"
