@fixture-OroUserBundle:users.yml

Feature: Manage conversation messages
  In order to manage Conversations
  As Administrator
  I need to be able to manage conversation messages from the conversation view page

  Scenario: Create new Conversation
    Given I login as administrator
    And I go to Activities/Conversations
    When I press "Create Conversation"
    And I fill form with:
      | Name     | Some conversation |
      | Context  | [charlie] |
    And I press "Save and Close"
    Then I should see "Conversation has been saved" flash message

  Scenario: Add new message
    Given I go to Activities/Conversations
    And I click view "Some conversation" in grid
    When I click "Add Message"
    And I fill form with:
      | Message  | First Message |
    And I click "Save"
    Then I should see "Message created successfully" flash message
    And I should see following grid:
      | Number | From            | Message       |
      | 1      | User John Doe   | First Message |
    And I should not see "There are no conversation messages"

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
    And I should see following grid:
      | Number | From            | Message       |
      | 2      | User Charlie Sheen   | Second Message |
      | 1      | User John Doe   | First Message |
    And I should not see "There are no conversation messages"

  Scenario: Close conversation
    When I click "Close"
    Then I should see "Conversation Flow: Closed"
    And I should see "Reopen"
    And I should not see "Add Message"
