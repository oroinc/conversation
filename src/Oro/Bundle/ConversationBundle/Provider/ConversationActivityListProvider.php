<?php

namespace Oro\Bundle\ConversationBundle\Provider;

use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ActivityListBundle\Model\ActivityListDateProviderInterface;
use Oro\Bundle\ActivityListBundle\Model\ActivityListProviderInterface;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Provides a way to use Conversation entity in an activity list.
 */
class ConversationActivityListProvider implements
    ActivityListProviderInterface,
    ActivityListDateProviderInterface
{
    private DoctrineHelper $doctrineHelper;
    private ActivityAssociationHelper $activityAssociationHelper;

    public function __construct(
        DoctrineHelper $doctrineHelper,
        ActivityAssociationHelper $activityAssociationHelper
    ) {
        $this->doctrineHelper            = $doctrineHelper;
        $this->activityAssociationHelper = $activityAssociationHelper;
    }

    #[\Override]
    public function isApplicableTarget($entityClass, $accessible = true): bool
    {
        return $this->activityAssociationHelper->isActivityAssociationEnabled(
            $entityClass,
            Conversation::class,
            $accessible
        );
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getSubject($entity): string
    {
        return $entity->getName();
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getDescription($entity): string
    {
        return '';
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getOwner($entity): User
    {
        return $entity->getOwner();
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getCreatedAt($entity): \DateTime
    {
        return $entity->getCreatedAt();
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getUpdatedAt($entity): \DateTime
    {
        return $entity->getUpdatedAt();
    }

    #[\Override]
    public function getData(ActivityList $activityList): array
    {
        /** @var Conversation $conversation */
        $conversation = $this->doctrineHelper
            ->getEntityRepository($activityList->getRelatedActivityClass())
            ->find($activityList->getRelatedActivityId());

        if (!$conversation->getStatus()) {
            return [
                'statusId' => null,
                'statusName' => null,
            ];
        }

        return [
            'statusId' => $conversation->getStatus()->getId(),
            'statusName' => $conversation->getStatus()->getName(),
        ];
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getOrganization($entity): Organization
    {
        return $entity->getOrganization();
    }

    #[\Override]
    public function getTemplate(): string
    {
        return '@OroConversation/Conversation/js/activityItemTemplate.html.twig';
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getRoutes($entity): array
    {
        return [
            'itemView'   => 'oro_conversation_widget_info',
            'itemEdit'   => 'oro_conversation_update'
        ];
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getActivityId($entity): int
    {
        return $this->doctrineHelper->getSingleEntityIdentifier($entity);
    }

    #[\Override]
    public function isApplicable($entity): bool
    {
        if (\is_object($entity)) {
            return $entity instanceof Conversation;
        }

        return $entity === Conversation::class;
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getTargetEntities($entity): iterable
    {
        return $entity->getActivityTargets();
    }

    /**
     * @param Conversation $entity
     */
    #[\Override]
    public function getActivityOwners($entity, ActivityList $activityList): array
    {
        $organization = $this->getOrganization($entity);
        $owner = $entity->getOwner();

        if (!$organization || !$owner) {
            return [];
        }

        $activityOwner = new ActivityOwner();
        $activityOwner->setActivity($activityList);
        $activityOwner->setOrganization($organization);
        $activityOwner->setUser($owner);

        return [$activityOwner];
    }

    #[\Override]
    public function isActivityListApplicable(ActivityList $activityList): bool
    {
        return true;
    }
}
