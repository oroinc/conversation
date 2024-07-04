<?php

namespace Oro\Bundle\ConversationBundle\Tools;

use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\EntityExtendBundle\Tools\DumperExtensions\AssociationEntityConfigDumperExtension;

/**
 * EntityConfigDumperExtension for Conversation participant entity.
 */
class ConversationParticipantEntityConfigDumperExtension extends AssociationEntityConfigDumperExtension
{
    #[\Override]
    protected function getAssociationEntityClass(): string
    {
        return ConversationParticipant::class;
    }

    #[\Override]
    protected function getAssociationScope(): string
    {
        return 'conversation_participant';
    }

    #[\Override]
    protected function getAssociationKind(): ?string
    {
        return 'conversationParticipant';
    }
}
