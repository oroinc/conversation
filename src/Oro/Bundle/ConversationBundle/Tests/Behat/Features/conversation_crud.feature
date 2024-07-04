@fixture-OroUserBundle:users.yml

Feature: conversation CRUD
  In order to manage Conversations
  As Administrator
  I need to be able to manage conversations

  Scenario: Create new Conversation
    Given I login as administrator
    And I go to Activities/Conversations
    When I press "Create Conversation"
    And I fill form with:
      | NAME     | Some test conversation |
      | Context  | [charlie] |
    And I press "Save and Close"
    Then I should see "Conversation has been saved" flash message

  Scenario: View Conversation
    Given I go to Activities/Conversations
    Then I should see following grid:
      | Name                   | Status   | Contexts      |
      | Some test conversation | Active   | Charlie Sheen |
    When I click view "Some test conversation" in grid
    Then I should see "Some test conversation"
    And John Doe should be an owner
    And I should see "Context Charlie Sheen"
    And I should see "Conversation Flow: Active"
    And I should see "There are no conversation messages"
    And I should see "Set inactive"
    And I should see "Close"

  Scenario: Inline edit conversation in grid
    Given I go to Activities/Conversations
    When I edit Name as "First conversation"
    And I reload the page
    Then I should see following grid:
      | Name               | Status   | Contexts      |
      | First conversation | Active   | Charlie Sheen |

  Scenario: Edit Conversation
    Given I go to Activities/Conversations
    When I click edit "First conversation" in grid
    And I fill form with:
      | Name     | EditedConv |
    And press "Save and Close"
    Then I should see "Conversation has been saved" flash message

  Scenario: Delete Conversation
    Given I go to Activities/Conversations
    When I keep in mind number of records in list
    And I click Delete "EditedConv" in grid
    And confirm deletion
    Then I should see "Conversation Deleted" flash message
    And the number of records decreased by 1
    And I should not see "EditedConv"