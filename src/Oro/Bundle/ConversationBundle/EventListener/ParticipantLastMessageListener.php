<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;

/**
 * Updates the last read message data for the participant.
 */
class ParticipantLastMessageListener
{
    public function onFlush(OnFlushEventArgs $event): void
    {
        $unitOfWork = $event->getObjectManager()->getUnitOfWork();
        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (
                $entity instanceof ConversationParticipant
                && $this->isLastMessageChanged($unitOfWork, $entity)
                && $entity->getLastReadMessage()
            ) {
                $entity->setLastReadDate(new \DateTime('now', new \DateTimeZone('UTC')));
                $entity->setLastReadMessageIndex($entity->getLastReadMessage()->getIndex());
            }
        }
    }

    private function isLastMessageChanged(UnitOfWork $unitOfWork, ConversationParticipant $entity): bool
    {
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        return isset($changeSet['lastReadMessage']);
    }
}
