@fixture-OroUserBundle:users.yml
@fixture-OroOrderBundle:order.yml

Feature: conversation on storefront
  In order to manage Conversations
  As Storefront user
  I need to be able to manage my conversations

  Scenario: Feature Background
    Given sessions active:
      | Admin | first_session  |
      | Buyer | second_session |

  Scenario: Enable Conversations
    Given I proceed as the Admin
    And I login as administrator
    And I go to System/ Configuration
    And I follow "Commerce/Customer/Interactions" on configuration sidebar
    And uncheck "Use default" for "Enable Conversations" field
    And I check "Enable Conversations"
    When I save form
    Then I should see "Configuration saved" flash message
    And the "Enable Conversations" checkbox should be checked

  Scenario: Create Conversation from order
    Given I proceed as the Buyer
    And I signed in as AmandaRCole@example.org on the store frontend
    And I open Order History page on the store frontend
    And I click "View" on row "SimpleOrder" in grid "PastOrdersGrid"
    When I click "Ask a question"
    And I fill form with:
      | Subject      | Conversation from order |
      | Your Message | Some first message |
    And I press "Send"
    Then I should see "Questions"
    When I click "Questions"
    Then I should see "Ask New Question"
    And I should see "Conversation from order"
    And I should see "Some first message"
    When I click "Ask New Question"
    And I fill form with:
      | Your Message | Second conversation message|
    And I press "Send"
    And I click "Questions"
    Then I should see "Conversation from order"
    And I should see "Some first message"
    And I should see "Order SimpleOrder"
    And I should see "Second conversation message"

  Scenario: Go to conversation from the order
    When I click "Conversation from order"
    Then I should see "Conversations"
    And I should see "Conversation from order"
    And I should see "Some first message"
    And I should see "Order SimpleOrder"
    And I should see "Second conversation message"
    And I should see "Active"

  Scenario: New conversation from the conversation page
    When I click "New Conversation"
    And I press "SendPopupButton"
    Then I should see validation errors:
      | Subject      | This value should not be blank. |
      | Your Message | This value should not be blank. |
    When I fill form with:
      | Subject      | conv1 |
      | Your Message | message1 |
    And I press "SendPopupButton"
    Then I should see "Conversations"
    And I should see "Conversation from order"
    And I should see "Some first message"
    And I should see "Order SimpleOrder"
    And I should see "Second conversation message"
    And I should see "Active"
    And I should see "conv1"
    And I should see "message1"

  Scenario: Send message from the backoffice
    Given I proceed as the Admin
    And I login as administrator
    When I go to Activities/Conversations
    Then I should see following grid:
      | Name                    | Messages | Initiated from    |
      | conv1                   | 1        |                   |
      | Order SimpleOrder       | 1        | Order SimpleOrder |
      | Conversation from order | 1        | Order SimpleOrder |
    When I click view "Conversation from order" in grid
    When I fill form with:
      | Message  | From admin |
    And I click "Add Message"
    Then I should see "Message created successfully" flash message

  Scenario: See the message on the storefront
    Given I proceed as the Buyer
    And I open Order History page on the store frontend
    And I click "View" on row "SimpleOrder" in grid "PastOrdersGrid"
    When I press "Questions"
    Then I should see "Order SimpleOrder"
    And I should see "Conversation from order"
    And I should see "From admin"
    When I click "Conversation from order"
    Then I should see "From admin"
    And I should see "John Doe"
    And I should see "JD"

  Scenario: Remove Order should not break the backoffice
    Given I proceed as the Admin
    And I go to Sales/Orders
    When I click delete SimpleOrder in grid
    And I confirm deletion
    Then there is one record in grid
    When I go to Activities/Conversations
    Then I should see following grid:
      | Name                    | Messages | Initiated from    |
      | Conversation from order | 2        |                   |
      | conv1                   | 1        |                   |
      | Order SimpleOrder       | 1        |                   |
    When I click view "Conversation from order" in grid
    Then I should see "Conversation from order"

  Scenario: See conversations list on storefront after the source entity was removed
    Given I proceed as the Buyer
    When I click "Conversations"
    Then I should see "conv1"
    And I should see "Order SimpleOrder"
    And I should see "Conversation from order"

  Scenario: Check mobile view
    Given I set window size to 375x840
    And I reload the page
    And I click "Conversation Back"
    Then I click on "Conversation SimpleOrder"
    And I should see "Order SimpleOrder"
    And I click "Conversation Back"
    Then I should see "Conversations"
    And I set window size to 1920x1080

  Scenario: Create new conversation from storefront conversation page
    Given I proceed as the Admin
    And I go to Activities/Conversations
    And I check all records in grid
    And I click "Delete" link from select all mass action dropdown
    And confirm deletion
    Then I proceed as the Buyer
    And I reload the page
    And I click "New Conversation"
    When I fill form with:
      | Subject      | Test Conv |
      | Your Message | Test Message |
    And I press "SendPopupButton"
    Then I click on "Conversation Test Conv"
    And I should see "Test Conv"
