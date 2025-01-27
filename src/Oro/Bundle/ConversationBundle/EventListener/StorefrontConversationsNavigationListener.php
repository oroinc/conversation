<?php

namespace Oro\Bundle\ConversationBundle\EventListener;

use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\NavigationBundle\Event\ConfigureMenuEvent;
use Oro\Bundle\NavigationBundle\Utils\MenuUpdateUtils;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds dot icon and count of the conversations with new messages to the quick access conversation menu item.
 */
class StorefrontConversationsNavigationListener
{
    private const MENU_ITEM_ID = 'frontend_conversation_list_quick_access';
    private const CONVERSATION_ROUTE_NAME = 'oro_conversation_frontend_conversation_index';

    public function __construct(
        private ConversationParticipantManager $manager,
        private RequestStack $requestStack
    ) {
    }

    public function onNavigationConfigure(ConfigureMenuEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request || $request->attributes->get('_route') === self::CONVERSATION_ROUTE_NAME) {
            return;
        }

        $menuItem = MenuUpdateUtils::findMenuItem($event->getMenu(), self::MENU_ITEM_ID);
        if (!$menuItem) {
            return;
        }

        $conversationsCount = $this->manager->getLastConversationsCountForUser();
        if ($conversationsCount) {
            $menuItem->setExtra('iconDot', true);
            $menuItem->setExtra(
                'iconDotData',
                min($conversationsCount, 99)
            );
        }
    }
}
