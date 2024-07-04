<?php

namespace Oro\Bundle\ConversationBundle\EntityExtend;

use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\EntityExtendBundle\EntityExtend\AbstractAssociationEntityFieldExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;

/**
 * Extended Entity Field Processor Extension for conversation participant associations
 */
class ConversationParticipantEntityFieldExtension extends AbstractAssociationEntityFieldExtension
{
    #[\Override]
    public function isApplicable(EntityFieldProcessTransport $transport): bool
    {
        return $transport->getClass() === ConversationParticipant::class;
    }

    #[\Override]
    public function getRelationKind(): ?string
    {
        return 'conversationParticipant';
    }

    #[\Override]
    public function getRelationType(): string
    {
        return RelationType::MANY_TO_ONE;
    }
}
