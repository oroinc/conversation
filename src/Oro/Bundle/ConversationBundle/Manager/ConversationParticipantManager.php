<?php

namespace Oro\Bundle\ConversationBundle\Manager;

use Doctrine\Common\Util\ClassUtils;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\User;

/**
 * Manager class for conversation participants entity.
 */
class ConversationParticipantManager
{
    public function __construct(
        private ManagerRegistry $doctrine,
        private TokenAccessorInterface $tokenAccessor,
        private AssociationManager $associationManager,
        private ParticipantInfoProvider $participantInfoInfoProvider,
        private DateTimeFormatterInterface $dateTimeFormatter,
        private WebSocketSendProcessor $webSocketSendProcessor,
        private AclHelper $aclHelper,
    ) {
    }

    public function getParticipantTargetClasses(): array
    {
        return array_keys($this->associationManager->getAssociationTargets(
            ConversationParticipant::class,
            null,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        ));
    }

    public function getOrCreateParticipantObjectForConversation(
        Conversation $conversation,
        object $participantTarget = null
    ): ?ConversationParticipant {
        if (null === $participantTarget) {
            $participantTarget = $this->tokenAccessor->getUser();
            if (!$participantTarget) {
                return null;
            }
        }

        $participant = null;

        if ($conversation->getId()) {
            $participant = $this->doctrine->getRepository(ConversationParticipant::class)
                ->findParticipantForConversation(
                    $this->associationManager,
                    $conversation,
                    $participantTarget
                );
        } else {
            foreach ($conversation->getParticipants() as $participantObject) {
                $participantClass = ClassUtils::getClass($participantTarget);
                $target = $participantObject->getConversationParticipantTarget();
                if ($target && is_a($target, $participantClass) && $participantTarget->getId() === $target->getId()) {
                    $participant = $participantObject;

                    break;
                }
            }
        }

        if (!$participant) {
            $participant = new ConversationParticipant();
            $participant->setConversation($conversation);
            $participant->setConversationParticipantTarget($participantTarget);
            $conversation->addParticipant($participant);
        }

        return $participant;
    }

    public function getParticipantInfoById(int $participantId): array
    {
        $targets = $this->associationManager->getAssociationTargets(
            ConversationParticipant::class,
            null,
            RelationType::MANY_TO_ONE,
            'conversationParticipant'
        );

        $conversationParticipantQuery = $this->doctrine->getManagerForClass(ConversationParticipant::class)
            ->createQueryBuilder()
            ->select('p')
            ->from(ConversationParticipant::class, 'p')
            ->where('p.id = :participant')
            ->setParameter('participant', $participantId);

        foreach ($targets as $targetField) {
            $conversationParticipantQuery->addSelect($targetField)
                ->leftJoin(sprintf('p.%s', $targetField), $targetField);
        }
        $conversationParticipant = $conversationParticipantQuery->getQuery()->getOneOrNullResult();

        if ($conversationParticipant->getConversationParticipantTarget()) {
            return $this->participantInfoInfoProvider->getParticipantInfo(
                $conversationParticipant->getConversationParticipantTarget()
            );
        }

        return [];
    }

    public function setLastReadMessageForParticipantAndSendNotification(
        Conversation $conversation,
        object $participantTarget
    ): void {
        $participant = $this->doctrine->getRepository(ConversationParticipant::class)
            ->findParticipantForConversation(
                $this->associationManager,
                $conversation,
                $participantTarget
            );

        $lastMessage = $conversation->getLastMessage();
        if ($participant && $lastMessage) {
            $participant->setLastReadMessage($lastMessage);
            $em = $this->doctrine->getManagerForClass(ConversationParticipant::class);
            $em->persist($participant);
            $em->flush();

            if ($participantTarget instanceof User) {
                $this->webSocketSendProcessor->sendForUser(
                    $participantTarget->getId(),
                    $this->tokenAccessor->getOrganizationId()
                );
            }
        }
    }

    public function getLastConversationsDataForUser(int $conversationsCount = 4): array
    {
        $resultMessages = [];
        $conversations = $this->doctrine->getRepository(ConversationParticipant::class)
            ->getLastConversationsForParticipant(
                $this->tokenAccessor->getUser(),
                $this->associationManager,
                $this->aclHelper,
                $conversationsCount
            );

        /** @var Conversation $conversation */
        foreach ($conversations as $conversation) {
            $message = $conversation->getMessages()->first();
            $participant = $message->getParticipant();
            $participantInfo = [];
            if ($participant) {
                $participantInfo = $this->participantInfoInfoProvider->getParticipantInfo(
                    $participant->getConversationParticipantTarget()
                );
            }

            $resultMessages[] = [
                'id' => $message->getId(),
                'conversationId' => $conversation->getId(),
                'conversationName' => $conversation->getName(),
                'message' => substr(strip_tags($message->getBody()), 0, 50),
                'fromName' => $participantInfo['title'],
                'messageTime' => $this->dateTimeFormatter->format($message->getCreatedAt()),
            ];
        }

        return $resultMessages;
    }

    public function getLastConversationsCountForUser(): int
    {
        return $this->doctrine->getRepository(ConversationParticipant::class)
            ->getLastConversationsCountForParticipant(
                $this->tokenAccessor->getUser(),
                $this->associationManager,
                $this->aclHelper
            );
    }
}
