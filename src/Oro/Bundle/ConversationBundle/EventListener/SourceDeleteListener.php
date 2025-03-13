<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;

/**
 * Removes conversation source data if source entity was removed.
 */
class SourceDeleteListener
{
    public function __construct(private ActivityManager $activityManager)
    {
    }

    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();
        $deletedEntities = $uow->getScheduledEntityDeletions();
        if (!count($deletedEntities)) {
            return;
        }

        $targetClasses = array_keys($this->activityManager->getActivityTargets(Conversation::class));
        foreach ($deletedEntities as $entity) {
            $className = ClassUtils::getClass($entity);
            if (\in_array($className, $targetClasses, true)) {
                $conversations = $em->getRepository(Conversation::class)->findBy(
                    ['sourceEntityClass' => $className, 'sourceEntityId' => $entity->getId()]
                );
                /** @var Conversation $conversation */
                foreach ($conversations as $conversation) {
                    $conversation->setSourceEntity(null, null);
                    $uow->scheduleForUpdate($conversation);
                }
            }
        }
    }
}
