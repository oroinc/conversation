<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\ConversationBundle\Layout\DataProvider\ConversationDataProvider;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ConversationDataProviderTest extends TestCase
{
    private ConversationParticipantManager&MockObject $manager;
    private RequestStack&MockObject $requestStack;
    private ConversationDataProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->manager = $this->createMock(ConversationParticipantManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->provider = new ConversationDataProvider(
            $this->manager,
            $this->requestStack
        );
    }

    public function testIsHaveNotReadConversationsWithoutRequest(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $this->manager->expects(self::never())
            ->method('getLastConversationsCountForUser');

        self::assertFalse($this->provider->isHaveNotReadConversations());
    }

    public function testIsHaveNotReadConversationsOnConversationPage(): void
    {
        $request = new Request();
        $request->attributes->set('_route', 'oro_conversation_frontend_conversation_index');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->manager->expects(self::never())
            ->method('getLastConversationsCountForUser');

        self::assertFalse($this->provider->isHaveNotReadConversations());
    }

    public function testIsHaveNotReadConversationsIfUserHaveNoNewConversations(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(new Request());

        $this->manager->expects(self::once())
            ->method('getLastConversationsCountForUser')
            ->willReturn(0);

        self::assertFalse($this->provider->isHaveNotReadConversations());
    }

    public function testIsHaveNotReadConversations(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(new Request());

        $this->manager->expects(self::once())
            ->method('getLastConversationsCountForUser')
            ->willReturn(2);

        self::assertTrue($this->provider->isHaveNotReadConversations());
    }
}
