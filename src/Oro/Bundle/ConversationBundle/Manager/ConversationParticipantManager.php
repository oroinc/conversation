<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
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
    private ParticipantInfoProvider $participantInfoInfoProvider;

    public function __construct(
        ManagerRegistry $doctrine,
        TokenAccessorInterface $tokenAccessor,
        AssociationManager $associationManager,
        ParticipantInfoProvider $participantInfoInfoProvider
    ) {
        $this->doctrine = $doctrine;
        $this->tokenAccessor = $tokenAccessor;
        $this->associationManager = $associationManager;
        $this->participantInfoInfoProvider = $participantInfoInfoProvider;
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

    public function getOrCreateParticipantObjectForConversation(
        Conversation $conversation,
        object $participantTarget = null
    ): ?ConversationParticipant {
        if (null === $participantTarget) {
            $participantTarget = $this->tokenAccessor->getUser();
            if (!$participantTarget) {
                return null;
            }
        }

        $participant = null;

        if ($conversation->getId()) {
            $participant = $this->doctrine->getRepository(ConversationParticipant::class)
                ->findParticipantForConversation(
                    $this->associationManager,
                    $conversation,
                    $participantTarget
                );
        } else {
            foreach ($conversation->getParticipants() as $participantObject) {
                $participantClass = ClassUtils::getClass($participantTarget);
                $target = $participantObject->getConversationParticipantTarget();
                if ($target && is_a($target, $participantClass) && $participantTarget->getId() === $target->getId()) {
                    $participant = $participantObject;

                    break;
                }
            }
        }

        if (!$participant) {
            $participant = new ConversationParticipant();
            $participant->setConversation($conversation);
            $participant->setConversationParticipantTarget($participantTarget);
            $conversation->addParticipant($participant);
        }

        return $participant;
    }

    public function getParticipantInfoById(int $participantId): array
    {
        $targets = $this->associationManager->getAssociationTargets(
            ConversationParticipant::class,
            null,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        $conversationParticipantQuery = $this->doctrine->getManagerForClass(ConversationParticipant::class)
            ->createQueryBuilder()
            ->select('p')
            ->from(ConversationParticipant::class, 'p')
            ->where('p.id = :participant')
            ->setParameter('participant', $participantId);

        foreach ($targets as $targetField) {
            $conversationParticipantQuery->addSelect($targetField)
                ->leftJoin(sprintf('p.%s', $targetField), $targetField);
        }
        $conversationParticipant = $conversationParticipantQuery->getQuery()->getOneOrNullResult();

        if ($conversationParticipant->getConversationParticipantTarget()) {
            return $this->participantInfoInfoProvider->getParticipantInfo(
                $conversationParticipant->getConversationParticipantTarget()
            );
        }

        return [];
    }
}
