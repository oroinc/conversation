<?php

namespace Oro\Bundle\ConversationBundle\Layout\DataProvider;

use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides methods to get additional conversation data for layouts.
 */
class ConversationDataProvider
{
    private const CONVERSATION_ROUTE_NAME = 'oro_conversation_frontend_conversation_index';

    public function __construct(
        private ConversationParticipantManager $manager,
        private RequestStack $requestStack
    ) {
    }

    public function isHaveNotReadConversations(): bool
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request || $request->attributes->get('_route') === self::CONVERSATION_ROUTE_NAME) {
            return false;
        }

        return (bool)$this->manager->getLastConversationsCountForUser();
    }
}
