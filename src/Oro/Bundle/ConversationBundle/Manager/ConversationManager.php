<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Manager class for conversation entity.
 */
class ConversationManager
{
    private EntityRoutingHelper $entityRoutingHelper;
    private RequestStack $requestStack;
    private EntityNameResolver $entityNameResolver;
    private EntityConfigHelper $entityConfigHelper;

    public function __construct(
        EntityRoutingHelper $entityRoutingHelper,
        RequestStack $requestStack,
        EntityNameResolver $entityNameResolver,
        EntityConfigHelper $entityConfigHelper
    ) {
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->requestStack = $requestStack;
        $this->entityNameResolver = $entityNameResolver;
        $this->entityConfigHelper = $entityConfigHelper;
    }

    public function create(): Conversation
    {
        $conversation = new Conversation();

        $request = $this->requestStack->getCurrentRequest();
        if ($request) {
            $targetEntityClass = $this->entityRoutingHelper->getEntityClassName($request);
            $targetEntityId = $this->entityRoutingHelper->getEntityId($request);

            if ($targetEntityClass && $targetEntityId) {
                $sourceEntity = $this->entityRoutingHelper->getEntity($targetEntityClass, $targetEntityId);
                $conversation->setName(sprintf(
                    '%s %s',
                    $this->entityConfigHelper->getLabel($sourceEntity),
                    $this->entityNameResolver->getName($sourceEntity)
                ));
            }
        }

        return $conversation;
    }
}
