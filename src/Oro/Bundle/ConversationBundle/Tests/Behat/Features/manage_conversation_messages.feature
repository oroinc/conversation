@fixture-OroUserBundle:users.yml
@fixture-OroCustomerBundle:CustomerUserFixture.yml

Feature: Manage conversation messages
  In order to manage Conversations
  As Administrator
  I need to be able to manage conversation messages from the conversation view page

  Scenario: Feature Background
    Given set configuration property "oro_conversation.enable_conversation" to "1"

  Scenario: Create new Conversation
    Given I login as administrator
    And I go to Activities/Conversations
    When I press "Create Conversation"
    And I fill form with:
      | Name          | Some conversation |
      | Customer User | Amanda Cole       |
      | Context       | [charlie]         |
    And I press "Save and Close"
    Then I should see "Conversation has been saved" flash message

  Scenario: Add new message
    Given I go to Activities/Conversations
    And I click view "Some conversation" in grid
    When I fill form with:
      | Message  | First Message |
    And I click "Add Message"
    Then I should see "Message created successfully" flash message
    And I should see "First Message"

  Scenario: Add new message from another user
    When I fill form with:
      | Message  | Another User Message |
      | From     | charlie |
    And I click "Add Message"
    Then I should see "Message created successfully" flash message
    When I click "All messages"
    And I should see following grid:
      | Number | From                 | Message              |
      | 2      | User Charlie Sheen   | Another User Message |
      | 1      | User John Doe        | First Message        |
    And I should not see "There are no conversation messages"

  Scenario: Close conversation
    When I click "Close"
    Then I should see "Conversation Flow: Closed"
    And I should see "Reopen"
    And I should not see "From"
