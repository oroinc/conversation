<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Manager class for conversation messages entity.
 */
class ConversationMessageManager
{
    private ManagerRegistry $doctrine;
    private ConversationParticipantManager $participantManager;
    private ParticipantInfoProvider $participantInfoInfoProvider;
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(
        ManagerRegistry $doctrine,
        ConversationParticipantManager $participantManager,
        ParticipantInfoProvider $participantInfoInfoProvider,
        AuthorizationCheckerInterface $authorizationChecker,
    ) {
        $this->doctrine = $doctrine;
        $this->participantManager = $participantManager;
        $this->participantInfoInfoProvider = $participantInfoInfoProvider;
        $this->authorizationChecker = $authorizationChecker;
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
            'perPage' => $perPage,
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
        $message->setParticipant($this->participantManager->getOrCreateParticipantObjectForConversation($conversation));

        return $message;
    }

    public function setMessageParticipant(ConversationMessage $message, object $fromParticipantTarget): void
    {
        $messageParticipant = $message->getParticipant();
        if ($messageParticipant && $messageParticipant->getConversationParticipantTarget() === $fromParticipantTarget) {
            return;
        }

        $participant = $this->participantManager->getOrCreateParticipantObjectForConversation(
            $message->getConversation(),
            $fromParticipantTarget
        );

        $message->setParticipant($participant);
    }

    public function saveMessage(ConversationMessage $message): ConversationMessage
    {
        $em = $this->doctrine->getManagerForClass(Conversation::class);
        $em->persist($message);
        $em->flush();

        return $message;
    }
}
