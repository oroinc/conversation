<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Acl\Voter\ManageConversationMessagesVoter;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\Repository\ConversationMessageRepository;
use Oro\Bundle\ConversationBundle\Manager\ConversationMessageManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationMessageExtended;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationMessageType;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationParticipantExtended;
use Oro\Bundle\EntityExtendBundle\Provider\EnumOptionsProvider;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ConversationMessageManagerTest extends TestCase
{
    private ManagerRegistry|MockObject $doctrine;
    private EnumOptionsProvider|MockObject $enumOptionsProvider;
    private ConversationParticipantManager|MockObject $participantManager;
    private ParticipantInfoProvider|MockObject $participantInfoInfoProvider;
    private AuthorizationCheckerInterface|MockObject $authorizationChecker;
    private ActivityManager|MockObject $activityManager;

    private ConversationMessageManager $conversationMessageManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->enumOptionsProvider = $this->createMock(EnumOptionsProvider::class);
        $this->participantManager = $this->createMock(ConversationParticipantManager::class);
        $this->participantInfoInfoProvider = $this->createMock(ParticipantInfoProvider::class);
        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);
        $this->activityManager = $this->createMock(ActivityManager::class);

        $this->conversationMessageManager = new ConversationMessageManager(
            $this->doctrine,
            $this->enumOptionsProvider,
            $this->participantManager,
            $this->participantInfoInfoProvider,
            $this->authorizationChecker,
            $this->activityManager
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
            ->method('getParticipantObjectForConversation')
            ->with($conversation)
            ->willReturn($participant);

        $result = $this->conversationMessageManager->createMessage($conversation);

        self::assertInstanceOf(ConversationMessage::class, $result);
        self::assertEquals($conversation, $result->getConversation());
        self::assertEquals($participant, $result->getParticipant());
    }

    public function testSaveMessageWhenUserHaveNoAccess(): void
    {
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('You does not have access to manage messages for conversation "conv1".');

        $conversation = new Conversation();
        $conversation->setName('conv1');
        $message = new ConversationMessage();
        $message->setConversation($conversation);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)
            ->willReturn(false);

        $this->conversationMessageManager->saveMessage($message);
    }

    public function testSaveMessageOnNewMessage(): void
    {
        $conversation = new Conversation();
        $conversation->setName('conv1');
        $conversation->setMessagesNumber(12);
        $message = new ConversationMessageExtended();
        $message->setConversation($conversation);
        $message->setBody('Message #1');

        $messageType = new ConversationMessageType('test_enum_code', ConversationMessage::TYPE_TEXT, 1);

        $user = new User();
        $participant = new ConversationParticipantExtended();
        $participant->setConversation($conversation);
        $participant->setConversationParticipantTarget($user);

        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(ConversationMessage::class)
            ->willReturn($em);

        $this->authorizationChecker->expects(self::once())
            ->method('isGranted')
            ->with(ManageConversationMessagesVoter::PERMISSION_NAME, $conversation)
            ->willReturn(true);

        $this->enumOptionsProvider->expects(self::once())
            ->method('getEnumOptionByCode')
            ->with(ConversationMessage::MESSAGE_TYPE_ENUM_CODE, ConversationMessage::TYPE_TEXT)
            ->willReturn($messageType);

        $this->participantManager->expects(self::once())
            ->method('getParticipantObjectForConversation')
            ->with($conversation, $user)
            ->willReturn($participant);

        $em->expects(self::exactly(3))
            ->method('persist')
            ->withConsecutive(
                [$participant],
                [$conversation],
                [$message],
            );
        $em->expects(self::once())
            ->method('flush');

        $this->activityManager->expects(self::once())
            ->method('addActivityTarget')
            ->with($conversation, $user);

        $result = $this->conversationMessageManager->saveMessage($message, $user);

        self::assertSame($messageType, $result->getType());
        self::assertEquals(13, $result->getConversation()->getMessagesNumber());
        self::assertEquals(13, $result->getParticipant()->getLastReadMessageIndex());
        self::assertInstanceOf(\DateTime::class, $result->getParticipant()->getLastReadDate());
        self::assertSame($user, $result->getParticipant()->getConversationParticipantTarget());
    }
}
