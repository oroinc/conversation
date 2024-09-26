<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;

/**
 * Complete the conversation entity during save.
 */
class ConversationSaveListener
{
    private EntityRoutingHelper $entityRoutingHelper;
    private ActivityManager $activityManager;
    private ConversationManager $conversationManager;
    private ConversationParticipantManager $participantManager;

    public function __construct(
        EntityRoutingHelper $entityRoutingHelper,
        ActivityManager $activityManager,
        ConversationManager $conversationManager,
        ConversationParticipantManager $participantManager
    ) {
        $this->entityRoutingHelper = $entityRoutingHelper;
        $this->activityManager = $activityManager;
        $this->conversationManager = $conversationManager;
        $this->participantManager = $participantManager;
    }

    public function prePersist(Conversation $conversation): void
    {
        if ($conversation->getId()) {
            return;
        }

        $sourceEntityClass = $conversation->getSourceEntityClass();
        $sourceEntityId = $conversation->getSourceEntityId();
        $activityTargets = [$conversation->getOwner()];
        $participants = [$conversation->getOwner()];

        if ($sourceEntityClass && $sourceEntityId) {
            $sourceEntity = $this->entityRoutingHelper->getEntity($sourceEntityClass, $sourceEntityId);
            if (!$conversation->getName()) {
                $conversation->setName($this->conversationManager->getConversationName($sourceEntity));
            }

            if (null === $conversation->getCustomerUser()) {
                $this->conversationManager->setCustomerUserToConversation($conversation, $sourceEntity);
            }

            $activityTargets[] = $sourceEntity;
        }

        if (null === $conversation->getCustomer() && null !== $conversation->getCustomerUser()) {
            $conversation->setCustomer($conversation->getCustomerUser()->getCustomer());
        }

        $activityTargets[] = $conversation->getCustomerUser();
        if (count($activityTargets)) {
            $this->activityManager->addActivityTargets($conversation, $activityTargets);
        }

        $participants[] = $conversation->getCustomerUser();
        $this->addParticipants($conversation, $participants);
    }

    private function addParticipants(Conversation $conversation, array $participants): void
    {
        foreach ($participants as $participant) {
            $this->participantManager->getOrCreateParticipantObjectForConversation($conversation, $participant);
        }
    }
}
