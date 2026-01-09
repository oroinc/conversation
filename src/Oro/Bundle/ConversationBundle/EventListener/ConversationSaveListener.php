<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Complete the conversation entity during save and send socket notification on new message
 */
class ConversationSaveListener
{
    public function __construct(
        private EntityRoutingHelper $entityRoutingHelper,
        private ActivityManager $activityManager,
        private ConversationManager $conversationManager,
        private ConversationParticipantManager $participantManager,
        private WebSocketSendProcessor $webSocketSendProcessor,
        private ?object $organizationHelper = null
    ) {
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $unitOfWork = $event->getObjectManager()->getUnitOfWork();
        $userMessages = [];
        foreach ($unitOfWork->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof ConversationMessage) {
                foreach ($entity->getConversation()->getParticipants() as $participant) {
                    $target = $participant->getConversationParticipantTarget();
                    if ($target instanceof User) {
                        if (!array_key_exists($target->getId(), $userMessages)) {
                            $userMessages[$target->getId()] = [];
                        }

                        $organization = $entity->getConversation()->getOrganization()->getId();
                        if (!in_array($organization, $userMessages[$target->getId()], true)) {
                            $userMessages[$target->getId()][] = $organization;
                        }

                        $globalOrganization = $this->getGlobalOrganizationId();
                        if (
                            $globalOrganization
                            && !in_array($globalOrganization, $userMessages[$target->getId()], true)
                        ) {
                            $userMessages[$target->getId()][] = $globalOrganization;
                        }
                    }
                }
            }
        }

        $this->webSocketSendProcessor->send($userMessages);
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
        $this->conversationManager->ensureConversationHaveStatus($conversation);
    }

    private function addParticipants(Conversation $conversation, array $participants): void
    {
        foreach ($participants as $participant) {
            $this->participantManager->getOrCreateParticipantObjectForConversation($conversation, $participant);
        }
    }

    private function getGlobalOrganizationId(): ?int
    {
        if ($this->organizationHelper && $this->organizationHelper->isGlobalOrganizationExists()) {
            return $this->organizationHelper->getGlobalOrganizationId();
        }

        return null;
    }
}
