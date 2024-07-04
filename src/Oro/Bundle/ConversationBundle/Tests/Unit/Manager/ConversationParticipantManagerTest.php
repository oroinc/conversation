<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Manager;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\Entity\Repository\ConversationParticipantRepository;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\EntityExtendBundle\Entity\Manager\AssociationManager;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConversationParticipantManagerTest extends TestCase
{
    private ManagerRegistry|MockObject $doctrine;
    private TokenAccessorInterface|MockObject $tokenAccessor;
    private AssociationManager|MockObject $associationManager;

    private ConversationParticipantManager $conversationParticipantManager;

    protected function setUp(): void
    {
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);
        $this->associationManager = $this->createMock(AssociationManager::class);

        $this->conversationParticipantManager = new ConversationParticipantManager(
            $this->doctrine,
            $this->tokenAccessor,
            $this->associationManager
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

    public function testGetParticipantObjectForConversationWithoutTargetAndUserInToken()
    {
        $conversation = new Conversation();
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(false);

        self::assertNull($this->conversationParticipantManager->getParticipantObjectForConversation($conversation));
    }

    public function testGetParticipantObjectForConversationForTargetFromToken()
    {
        $conversation = new Conversation();
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


        $result = $this->conversationParticipantManager->getParticipantObjectForConversation($conversation);
        self::assertSame($participant, $result);
    }

    public function testGetParticipantObjectForConversation()
    {
        $conversation = new Conversation();
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


        $result = $this->conversationParticipantManager->getParticipantObjectForConversation($conversation, $user);
        self::assertSame($participant, $result);
    }
}
