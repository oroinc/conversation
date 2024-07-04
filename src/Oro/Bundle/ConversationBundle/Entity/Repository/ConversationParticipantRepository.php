<?php

namespace Oro\Bundle\ConversationBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;

/**
 * Repository class fot Conversation participant entity.
 */
class ConversationParticipantRepository extends EntityRepository
{
    public function findParticipantForConversation(
        AssociationManager $associationManager,
        Conversation $conversation,
        object $target
    ): ?ConversationParticipant {
        $entityClass = ClassUtils::getClass($target);
        $targets = $associationManager->getAssociationTargets(
            ConversationParticipant::class,
            fn (string $ownerClass, string $targetClass) => $targetClass === $entityClass,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        return $this->createQueryBuilder('participant')
            ->where('participant.conversation = :conversation')
            ->andWhere(sprintf('participant.%s = :target', $targets[$entityClass]))
            ->setParameter('conversation', $conversation)
            ->setParameter('target', $target)
            ->setMaxResults(1)
            ->orderBy('participant.id')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
