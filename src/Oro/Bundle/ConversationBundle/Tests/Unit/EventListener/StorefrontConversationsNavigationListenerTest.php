<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Knp\Menu\FactoryInterface;
use Oro\Bundle\ConversationBundle\EventListener\StorefrontConversationsNavigationListener;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Tests\Unit\Entity\Stub\MenuItemStub;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class StorefrontConversationsNavigationListenerTest extends TestCase
{
    private ConversationParticipantManager&MockObject $manager;
    private RequestStack&MockObject $requestStack;
    private StorefrontConversationsNavigationListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->manager = $this->createMock(ConversationParticipantManager::class);
        $this->requestStack = $this->createMock(RequestStack::class);

        $this->listener = new StorefrontConversationsNavigationListener(
            $this->manager,
            $this->requestStack
        );
    }

    public function testOnNavigationConfigureOnMenuWithoutMenuItem(): void
    {
        $menu = new MenuItemStub();
        $menu->setName('test');

        $this->manager->expects(self::never())
            ->method('getLastConversationsCountForUser');

        $this->listener->onNavigationConfigure(
            new ConfigureMenuEvent($this->createMock(FactoryInterface::class), $menu)
        );
    }

    public function testOnNavigationConfigureOnMenuOnConversationPage(): void
    {
        $menu = new MenuItemStub();
        $menu->setName('test');

        $conversationMenuItem = new MenuItemStub();
        $conversationMenuItem->setName('frontend_conversation_list_quick_access');
        $menu->addChild($conversationMenuItem);

        $request = new Request();
        $request->attributes->set('_route', 'oro_conversation_frontend_conversation_index');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->manager->expects(self::never())
            ->method('getLastConversationsCountForUser');

        $this->listener->onNavigationConfigure(
            new ConfigureMenuEvent($this->createMock(FactoryInterface::class), $menu)
        );

        self::assertEmpty($conversationMenuItem->getExtras());
    }

    public function testOnNavigationConfigureWhenThereIsNoNewConversations(): void
    {
        $menu = new MenuItemStub();
        $menu->setName('test');

        $conversationMenuItem = new MenuItemStub();
        $conversationMenuItem->setName('frontend_conversation_list_quick_access');
        $menu->addChild($conversationMenuItem);

        $request = new Request();
        $request->attributes->set('_route', 'test');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->manager->expects(self::once())
            ->method('getLastConversationsCountForUser')
            ->willReturn(0);

        $this->listener->onNavigationConfigure(
            new ConfigureMenuEvent($this->createMock(FactoryInterface::class), $menu)
        );

        self::assertEmpty($conversationMenuItem->getExtras());
    }

    public function testOnNavigationConfigure(): void
    {
        $menu = new MenuItemStub();
        $menu->setName('test');

        $conversationMenuItem = new MenuItemStub();
        $conversationMenuItem->setName('frontend_conversation_list_quick_access');
        $menu->addChild($conversationMenuItem);

        $request = new Request();
        $request->attributes->set('_route', 'test');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->manager->expects(self::once())
            ->method('getLastConversationsCountForUser')
            ->willReturn(5);

        $this->listener->onNavigationConfigure(
            new ConfigureMenuEvent($this->createMock(FactoryInterface::class), $menu)
        );

        self::assertEquals(
            ['iconDot' => true, 'iconDotData' => 5],
            $conversationMenuItem->getExtras()
        );
    }

    public function testOnNavigationConfigureOn99plusConversations(): void
    {
        $menu = new MenuItemStub();
        $menu->setName('test');

        $conversationMenuItem = new MenuItemStub();
        $conversationMenuItem->setName('frontend_conversation_list_quick_access');
        $menu->addChild($conversationMenuItem);

        $request = new Request();
        $request->attributes->set('_route', 'test');

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->manager->expects(self::once())
            ->method('getLastConversationsCountForUser')
            ->willReturn(105);

        $this->listener->onNavigationConfigure(
            new ConfigureMenuEvent($this->createMock(FactoryInterface::class), $menu)
        );

        self::assertEquals(
            ['iconDot' => true, 'iconDotData' => 99],
            $conversationMenuItem->getExtras()
        );
    }
}
