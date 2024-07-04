<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

/**
 * Manager class for conversation participants entity.
 */
class ConversationParticipantManager
{
    private ManagerRegistry $doctrine;
    private TokenAccessorInterface $tokenAccessor;
    private AssociationManager $associationManager;

    public function __construct(
        ManagerRegistry $doctrine,
        TokenAccessorInterface $tokenAccessor,
        AssociationManager $associationManager
    ) {
        $this->doctrine = $doctrine;
        $this->tokenAccessor = $tokenAccessor;
        $this->associationManager = $associationManager;
    }

    public function getParticipantTargetClasses(): array
    {
        return array_keys($this->associationManager->getAssociationTargets(
            ConversationParticipant::class,
            null,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        ));
    }

    public function getParticipantObjectForConversation(
        Conversation $conversation,
        object $participantTarget = null
    ): ?ConversationParticipant {
        if (null === $participantTarget) {
            $participantTarget = $this->tokenAccessor->getUser();
            if (!$participantTarget) {
                return null;
            }
        }

        $participant = $this->doctrine->getRepository(ConversationParticipant::class)->findParticipantForConversation(
            $this->associationManager,
            $conversation,
            $participantTarget
        );

        if (!$participant) {
            $participant = new ConversationParticipant();
            $participant->setConversation($conversation);
            $participant->setConversationParticipantTarget($participantTarget);
        }

        return $participant;
    }
}
