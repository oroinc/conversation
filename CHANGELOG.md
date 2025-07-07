The upgrade instructions are available at [Oro documentation website](https://doc.oroinc.com/master/backend/setup/upgrade-to-new-version/).

The current file describes significant changes in the code that may affect the upgrade of your customizations.

## UNRELEASED

### Changed

#### ConversationBundle
* Changed the `\Oro\Bundle\ConversationBundle\Manager\ConversationManager` so it uses the `short` entity name representation
  format for a source entity.
* Changed the `Conversation/Datagrid/source.html.twig` so it uses the `short` entity name representation format for a source entity.
* Changed the `Conversation/view.html.twig` so it uses the `short` entity name representation format for a source entity.
