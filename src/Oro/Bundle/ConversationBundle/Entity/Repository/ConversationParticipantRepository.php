<?php

namespace Oro\Bundle\ConversationBundle\Entity\Repository;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Repository class fot Conversation participant entity.
 */
class ConversationParticipantRepository extends EntityRepository
{
    public function findParticipantForConversation(
        AssociationManager $associationManager,
        Conversation|int $conversation,
        object $target
    ): ?ConversationParticipant {
        $entityClass = ClassUtils::getClass($target);
        $targets = $associationManager->getAssociationTargets(
            ConversationParticipant::class,
            fn (string $ownerClass, string $targetClass) => $targetClass === $entityClass,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        $andWhereRestriction = sprintf('participant.%s = :target', $targets[$entityClass]);
        return $this->createQueryBuilder('participant')
            ->where('participant.conversation = :conversation')
            ->andWhere($andWhereRestriction)
            ->setParameter('conversation', $conversation)
            ->setParameter('target', $target)
            ->setMaxResults(1)
            ->orderBy('participant.id')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getLastConversationsForParticipant(
        object $target,
        AssociationManager $associationManager,
        AclHelper $aclHelper,
        int $limit = 10
    ): array {
        $entityClass = ClassUtils::getClass($target);
        $targets = $associationManager->getAssociationTargets(
            ConversationParticipant::class,
            fn (string $ownerClass, string $targetClass) => $targetClass === $entityClass,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        $andWhereRestriction = sprintf('participant.%s = :target', $targets[$entityClass]);
        $query = $this->_em->createQueryBuilder()
            ->select('conversation')
            ->distinct()
            ->from(Conversation::class, 'conversation')
            ->join('conversation.participants', 'participant')
            ->andWhere($andWhereRestriction)
            ->andWhere('conversation.messagesNumber - participant.lastReadMessageIndex > 0')
            ->orderBy('conversation.updatedAt', 'DESC')
            ->setParameter('target', $target)
            ->setMaxResults($limit)
            ->getQuery();

        return $aclHelper->apply($query)->getResult();
    }

    public function getLastConversationsCountForParticipant(
        object $target,
        AssociationManager $associationManager,
        AclHelper $aclHelper
    ): int {
        $entityClass = ClassUtils::getClass($target);
        $targets = $associationManager->getAssociationTargets(
            ConversationParticipant::class,
            fn (string $ownerClass, string $targetClass) => $targetClass === $entityClass,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        $andWhereRestriction = sprintf('participant.%s = :target', $targets[$entityClass]);
        $query = $this->_em->createQueryBuilder()
            ->select('count(distinct(conversation.id))')
            ->from(Conversation::class, 'conversation')
            ->join('conversation.participants', 'participant')
            ->andWhere($andWhereRestriction)
            ->andWhere('conversation.messagesNumber - participant.lastReadMessageIndex > 0')
            ->setParameter('target', $target)
            ->getQuery();

        return $aclHelper->apply($query)->getSingleScalarResult();
    }
}
