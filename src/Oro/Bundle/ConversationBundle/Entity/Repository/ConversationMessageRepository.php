<?php

namespace Oro\Bundle\ConversationBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;

/**
 * Repository class fot Conversation message entity.
 */
class ConversationMessageRepository extends EntityRepository
{
    /**
     * @return array ConversationMessage[]
     */
    public function getMessages(Conversation $conversation, int $firstResult, int $maxResults, string $order): array
    {
        return $this->createQueryBuilder('message')
            ->where('message.conversation = :conversation')
            ->setParameter('conversation', $conversation)
            ->setFirstResult($firstResult)
            ->setMaxResults($maxResults)
            ->orderBy('message.index', $order)
            ->getQuery()
            ->getResult();
    }
}
