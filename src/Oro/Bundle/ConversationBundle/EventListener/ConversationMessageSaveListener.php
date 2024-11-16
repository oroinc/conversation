<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UIBundle\Tools\HtmlTagHelper;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Complete the conversation message entity during save.
 */
class ConversationMessageSaveListener
{
    private AuthorizationCheckerInterface $authorizationChecker;
    private HtmlTagHelper $htmlTagHelper;
    private ActivityManager $activityManager;
    private EnumOptionsProvider $enumOptionsProvider;
    private ConversationParticipantManager $participantManager;
    private TokenAccessorInterface $tokenAccessor;

    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        HtmlTagHelper $htmlTagHelper,
        ActivityManager $activityManager,
        EnumOptionsProvider $enumOptionsProvider,
        ConversationParticipantManager $participantManager,
        TokenAccessorInterface $tokenAccessor
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->htmlTagHelper = $htmlTagHelper;
        $this->activityManager = $activityManager;
        $this->enumOptionsProvider = $enumOptionsProvider;
        $this->participantManager = $participantManager;
        $this->tokenAccessor = $tokenAccessor;
    }

    public function prePersist(ConversationMessage $message): void
    {
        if ($message->getId()) {
            return;
        }

        $conversation = $message->getConversation();
        if ($this->tokenAccessor->hasUser()
            && !$this->authorizationChecker->isGranted(
                ManageConversationMessagesVoter::PERMISSION_NAME,
                $conversation
            )
        ) {
            throw new AccessDeniedException(
                sprintf('You does not have access to manage messages for conversation "%s".', $conversation->getName())
            );
        }

        if (!str_contains($message->getBody(), '<p>')) {
            $message->setBody('<p>' . str_replace("\n", '</p><p>', $message->getBody()) . '</p>');
        }
        $message->setBody($this->htmlTagHelper->sanitize($message->getBody()));

        if (!$message->getId()) {
            $this->ensureMessageTypeWasSet($message);
            $messageNumber = $this->updateMessageNumber($message);
            $participant = $this->updateParticipant($message, $messageNumber);
            $message->getConversation()->setLastMessage($message);
            if (!$conversation->getParticipants()->contains($participant)) {
                $conversation->getParticipants()->add($participant);
            }

            if ($participant->getConversationParticipantTarget()) {
                $this->activityManager->addActivityTarget(
                    $conversation,
                    $participant->getConversationParticipantTarget()
                );
            }
        }
    }

    private function ensureMessageTypeWasSet(ConversationMessage $message): void
    {
        if ($message->getType()) {
            return;
        }

        $messageType = $this->enumOptionsProvider->getEnumOptionByCode(
            ConversationMessage::TYPE_CODE,
            ConversationMessage::TYPE_TEXT
        );

        $message->setType($messageType);
    }

    private function updateMessageNumber(ConversationMessage $conversationMessage): int
    {
        $conversation = $conversationMessage->getConversation();
        $messageNumber = $conversation->getMessagesNumber();
        $messageNumber++;
        $conversation->setMessagesNumber($messageNumber);
        $conversationMessage->setIndex($messageNumber);

        return $messageNumber;
    }

    private function updateParticipant(
        ConversationMessage $conversationMessage,
        int $messageIndex
    ): ?ConversationParticipant {
        if ($conversationMessage->getType()->getId() === ConversationMessage::TYPE_SYSTEM) {
            return null;
        }

        $participant = $conversationMessage->getParticipant();
        if (!$participant) {
            $participant = $this->participantManager->getOrCreateParticipantObjectForConversation(
                $conversationMessage->getConversation()
            );
            $conversationMessage->setParticipant($participant);
        }

        $participant->setLastReadMessage($conversationMessage);
        $participant->setLastReadMessageIndex($messageIndex);
        $participant->setLastReadDate(new \DateTime('now', new \DateTimeZone('UTC')));

        return $participant;
    }
}
