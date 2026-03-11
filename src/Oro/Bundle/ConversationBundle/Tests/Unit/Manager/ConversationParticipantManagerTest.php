<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Entity\Repository\ConversationParticipantRepository;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConversationParticipantManagerTest extends TestCase
{
    use EntityTrait;

    private ManagerRegistry|MockObject $doctrine;
    private TokenAccessorInterface|MockObject $tokenAccessor;
    private AssociationManager|MockObject $associationManager;
    private ParticipantInfoProvider|MockObject $participantInfoInfoProvider;
    private DateTimeFormatterInterface|MockObject $dateTimeFormatter;
    private WebSocketSendProcessor|MockObject $webSocketSendProcessor;
    private AclHelper|MockObject $aclHelper;

    private ConversationParticipantManager $conversationParticipantManager;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->associationManager = $this->createMock(AssociationManager::class);
        $this->participantInfoInfoProvider = $this->createMock(ParticipantInfoProvider::class);
        $this->dateTimeFormatter = $this->createMock(DateTimeFormatterInterface::class);
        $this->webSocketSendProcessor = $this->createMock(WebSocketSendProcessor::class);
        $this->aclHelper = $this->createMock(AclHelper::class);

        $this->conversationParticipantManager = new ConversationParticipantManager(
            $this->doctrine,
            $this->tokenAccessor,
            $this->associationManager,
            $this->participantInfoInfoProvider,
            $this->dateTimeFormatter,
            $this->webSocketSendProcessor,
            $this->aclHelper
        );
    }

    public function testGetParticipantTargetClasses(): void
    {
        $this->associationManager->expects(self::once())
            ->method('getAssociationTargets')
            ->with(
                ConversationParticipant::class,
                null,
                RelationType::MANY_TO_ONE,
                'conversationParticipant'
            )
            ->willReturn([\stdClass::class => [], User::class => []]);

        self::assertEquals(
            [\stdClass::class, User::class],
            $this->conversationParticipantManager->getParticipantTargetClasses()
        );
    }

    public function testGetParticipantObjectForConversationWithoutTargetAndUserInToken(): void
    {
        $conversation = new Conversation();
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(false);

        self::assertNull($this->conversationParticipantManager->getOrCreateParticipantObjectForConversation(
            $conversation
        ));
    }

    public function testGetParticipantObjectForExistingConversation(): void
    {
        $conversation = $this->getEntity(Conversation::class, ['id' => 1]);
        $participant = new ConversationParticipant();
        $user = new User();

        $repo = $this->createMock(ConversationParticipantRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->willReturn($repo);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $repo->expects(self::once())
            ->method('findParticipantForConversation')
            ->willReturn($participant);


        $result = $this->conversationParticipantManager->getOrCreateParticipantObjectForConversation($conversation);
        self::assertSame($participant, $result);
    }

    public function testGetParticipantObjectForConversation(): void
    {
        $conversation = $this->getEntity(Conversation::class, ['id' => 1]);
        $participant = new ConversationParticipant();
        $user = new User();

        $repo = $this->createMock(ConversationParticipantRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->willReturn($repo);

        $this->tokenAccessor->expects(self::never())
            ->method('getUser')
            ->willReturn($user);

        $repo->expects(self::once())
            ->method('findParticipantForConversation')
            ->willReturn($participant);


        $result = $this->conversationParticipantManager->getOrCreateParticipantObjectForConversation(
            $conversation,
            $user
        );
        self::assertSame($participant, $result);
    }

    public function testGetLastConversationsDataForUser(): void
    {
        $user = new User();
        $participantTarget = new \stdClass();
        $createdAt = new \DateTime('2024-01-01 10:00:00');
        $body = sprintf('<p>%s</p>', str_repeat('A', 60));

        $participant = $this->getMockBuilder(ConversationParticipant::class)
            ->addMethods(['getConversationParticipantTarget'])
            ->getMock();
        $participant->expects(self::once())
            ->method('getConversationParticipantTarget')
            ->willReturn($participantTarget);

        $message = $this->createMock(ConversationMessage::class);
        $message->expects(self::once())
            ->method('getParticipant')
            ->willReturn($participant);
        $message->expects(self::once())
            ->method('getId')
            ->willReturn(101);
        $message->expects(self::once())
            ->method('getBody')
            ->willReturn($body);
        $message->expects(self::once())
            ->method('getCreatedAt')
            ->willReturn($createdAt);

        $conversation = $this->createMock(Conversation::class);
        $conversation->expects(self::once())
            ->method('getMessages')
            ->willReturn(new ArrayCollection([$message]));
        $conversation->expects(self::once())
            ->method('getId')
            ->willReturn(42);
        $conversation->expects(self::once())
            ->method('getName')
            ->willReturn('Test Conversation');

        $repo = $this->createMock(ConversationParticipantRepository::class);
        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(ConversationParticipant::class)
            ->willReturn($repo);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $repo->expects(self::once())
            ->method('getLastConversationsForParticipant')
            ->with($user, $this->associationManager, $this->aclHelper, 2)
            ->willReturn([$conversation]);

        $this->participantInfoInfoProvider->expects(self::once())
            ->method('getParticipantInfo')
            ->with($participantTarget)
            ->willReturn(['title' => 'Participant Name']);

        $this->dateTimeFormatter->expects(self::once())
            ->method('format')
            ->with($createdAt)
            ->willReturn('Jan 1, 2024');

        self::assertEquals(
            [
                [
                    'id' => 101,
                    'conversationId' => 42,
                    'conversationName' => 'Test Conversation',
                    'message' => str_repeat('A', 50),
                    'fromName' => 'Participant Name',
                    'messageTime' => 'Jan 1, 2024',
                ]
            ],
            $this->conversationParticipantManager->getLastConversationsDataForUser(2)
        );
    }

    public function testGetLastConversationsDataForUserReturnsEmptyArray(): void
    {
        $user = new User();
        $repo = $this->createMock(ConversationParticipantRepository::class);

        $this->doctrine->expects(self::once())
            ->method('getRepository')
            ->with(ConversationParticipant::class)
            ->willReturn($repo);

        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn($user);

        $repo->expects(self::once())
            ->method('getLastConversationsForParticipant')
            ->with($user, $this->associationManager, $this->aclHelper, 4)
            ->willReturn([]);

        $this->participantInfoInfoProvider->expects(self::never())
            ->method('getParticipantInfo');
        $this->dateTimeFormatter->expects(self::never())
            ->method('format');

        self::assertEquals([], $this->conversationParticipantManager->getLastConversationsDataForUser());
    }
}
