<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\Repository\ConversationMessageRepository;
use Oro\Bundle\ConversationBundle\Manager\ConversationMessageManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationParticipantExtended;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConversationMessageManagerTest extends TestCase
{
    private ManagerRegistry|MockObject $doctrine;
    private ConversationParticipantManager|MockObject $participantManager;
    private ParticipantInfoProvider|MockObject $participantInfoInfoProvider;
    private AuthorizationCheckerInterface|MockObject $authorizationChecker;

    private ConversationMessageManager $conversationMessageManager;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->participantManager = $this->createMock(ConversationParticipantManager::class);
        $this->participantInfoInfoProvider = $this->createMock(ParticipantInfoProvider::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->conversationMessageManager = new ConversationMessageManager(
            $this->doctrine,
            $this->participantManager,
            $this->participantInfoInfoProvider,
            $this->authorizationChecker
        );
    }

    public function testGetMessages(): void
    {
        $messages = [];

        $conversation = new Conversation();
        $user1 = new User();
        $participant1 = new ConversationParticipantExtended();
        $participant1->setConversation($conversation);
        $participant1->setConversationParticipantTarget($user1);

        $user2 = new User();
        $participant2 = new ConversationParticipantExtended();
        $participant2->setConversation($conversation);
        $participant2->setConversationParticipantTarget($user2);

        for ($i = 1; $i <= 15; ++$i) {
            $message = new ConversationMessage();
            $message->setBody('Message #' . $i);
            $message->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $message->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
            $message->setConversation($conversation);
            $message->setParticipant($i % 2 == 0 ? $participant1 : $participant2);

            $messages[] = $message;
        }

        $messageRepo = $this->createMock(ConversationMessageRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(ConversationMessage::class)
            ->willReturn($messageRepo);
        $messageRepo->expects(self::once())
            ->method('getMessages')
            ->with($conversation, 14, 15, 'ASC')
            ->willReturn($messages);

        $this->participantInfoInfoProvider->expects(self::exactly(14))
            ->method('getParticipantInfo')
            ->willReturn([]);

        self::assertEquals(
            [
                'conversation' => $conversation,
                'messages' => [
                    ['object' => $messages[13], 'participant' => []],
                    ['object' => $messages[12], 'participant' => []],
                    ['object' => $messages[11], 'participant' => []],
                    ['object' => $messages[10], 'participant' => []],
                    ['object' => $messages[9], 'participant' => []],
                    ['object' => $messages[8], 'participant' => []],
                    ['object' => $messages[7], 'participant' => []],
                    ['object' => $messages[6], 'participant' => []],
                    ['object' => $messages[5], 'participant' => []],
                    ['object' => $messages[4], 'participant' => []],
                    ['object' => $messages[3], 'participant' => []],
                    ['object' => $messages[2], 'participant' => []],
                    ['object' => $messages[1], 'participant' => []],
                    ['object' => $messages[0], 'participant' => []],
                ],
                'hasMore' => true,
                'page' => 2,
                'perPage' => 14
            ],
            $this->conversationMessageManager->getMessages($conversation, 2, 14, 'ASC', true)
        );
    }

    public function testCreateMessageWhenUserHaveNoAccess(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You does not have access to manage messages for conversation "conv1".');

        $conversation = new Conversation();
        $conversation->setName('conv1');

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)
            ->willReturn(false);

        $this->conversationMessageManager->createMessage($conversation);
    }

    public function testCreateMessage(): void
    {
        $conversation = new Conversation();
        $conversation->setName('conv1');

        $user = new User();
        $participant = new ConversationParticipantExtended();
        $participant->setConversation($conversation);
        $participant->setConversationParticipantTarget($user);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)
            ->willReturn(true);

        $this->participantManager->expects(self::once())
            ->method('getOrCreateParticipantObjectForConversation')
            ->with($conversation)
            ->willReturn($participant);

        $result = $this->conversationMessageManager->createMessage($conversation);

        self::assertInstanceOf(ConversationMessage::class, $result);
        self::assertEquals($conversation, $result->getConversation());
        self::assertEquals($participant, $result->getParticipant());
    }
}
