<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manager class for conversation messages entity.
 */
class ConversationMessageManager
{
    private ManagerRegistry $doctrine;
    private EnumOptionsProvider $enumOptionsProvider;
    private ConversationParticipantManager $participantManager;
    private ParticipantInfoProvider $participantInfoInfoProvider;
    private AuthorizationCheckerInterface $authorizationChecker;
    private ActivityManager $activityManager;

    public function __construct(
        ManagerRegistry $doctrine,
        EnumOptionsProvider $enumOptionsProvider,
        ConversationParticipantManager $participantManager,
        ParticipantInfoProvider $participantInfoInfoProvider,
        AuthorizationCheckerInterface $authorizationChecker,
        ActivityManager $activityManager
    ) {
        $this->doctrine = $doctrine;
        $this->enumOptionsProvider = $enumOptionsProvider;
        $this->participantManager = $participantManager;
        $this->participantInfoInfoProvider = $participantInfoInfoProvider;
        $this->authorizationChecker = $authorizationChecker;
        $this->activityManager = $activityManager;
    }

    public function getMessages(
        Conversation $conversation,
        int $page = 1,
        int $perPage = 10,
        string $order = 'ASC',
        bool $inverse = false
    ): array {
        $messages = $this->doctrine->getRepository(ConversationMessage::class)
            ->getMessages($conversation, ($page - 1) * $perPage, $perPage + 1, $order);
        $hasMore = count($messages) === $perPage + 1;
        if ($hasMore) {
            $messages = \array_slice($messages, 0, $perPage);
        }
        if ($inverse) {
            $messages = array_reverse($messages);
        }

        $resultMessages = [];
        /** @var ConversationMessage $message */
        foreach ($messages as $message) {
            $participant = $message->getParticipant();
            $participantInfo = [];
            if ($participant) {
                $participantInfo = $this->participantInfoInfoProvider->getParticipantInfo(
                    $participant->getConversationParticipantTarget()
                );
            }
            $resultMessages[] = [
                'object' => $message,
                'participant' => $participantInfo,
            ];
        }

        return [
            'conversation' => $conversation,
            'messages' => $resultMessages,
            'hasMore' => $hasMore,
            'page' => $page,
            'perPage' => $perPage
        ];
    }

    public function createMessage(Conversation $conversation): ConversationMessage
    {
        if (!$this->authorizationChecker->isGranted(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)) {
            throw new AccessDeniedException(
                sprintf('You does not have access to manage messages for conversation "%s".', $conversation->getName())
            );
        }

        $message = new ConversationMessage();
        $message->setConversation($conversation);
        $message->setParticipant($this->participantManager->getParticipantObjectForConversation($conversation));

        return $message;
    }

    public function saveMessage(
        ConversationMessage $message,
        object $fromParticipantTarget = null
    ): ConversationMessage {
        $conversation = $message->getConversation();
        if (!$this->authorizationChecker->isGranted(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)) {
            throw new AccessDeniedException(
                sprintf('You does not have access to manage messages for conversation "%s".', $conversation->getName())
            );
        }

        $em = $this->doctrine->getManagerForClass(ConversationMessage::class);
        if (!$message->getId()) {
            $this->ensureMessageTypeWasSet($message);
            $messageNumber = $this->updateMessageNumber($message);
            $participant = $this->updateParticipant($message, $messageNumber, $fromParticipantTarget);
            $em->persist($participant);

            if ($participant->getConversationParticipantTarget()) {
                $this->activityManager->addActivityTarget(
                    $conversation,
                    $participant->getConversationParticipantTarget()
                );
                $em->persist($conversation);
            }
        }

        $em->persist($message);
        $em->flush();

        return $message;
    }

    private function ensureMessageTypeWasSet(ConversationMessage $message): void
    {
        if ($message->getType()) {
            return;
        }

        $messageType = $this->enumOptionsProvider->getEnumOptionByCode(
            ConversationMessage::MESSAGE_TYPE_ENUM_CODE,
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
        int $messageIndex,
        ?object $fromParticipantTarget = null
    ): ?ConversationParticipant {
        if ($conversationMessage->getType()->getId() === ConversationMessage::TYPE_SYSTEM) {
            return null;
        }

        $participant = $conversationMessage->getParticipant();
        if (null === $conversationMessage->getId()) {
            $participant = $this->participantManager->getParticipantObjectForConversation(
                $conversationMessage->getConversation(),
                $fromParticipantTarget
            );
            $conversationMessage->setParticipant($participant);
        }

        $participant->setLastReadMessage($conversationMessage);
        $participant->setLastReadMessageIndex($messageIndex);
        $participant->setLastReadDate(new \DateTime('now', new \DateTimeZone('UTC')));

        return $participant;
    }
}
