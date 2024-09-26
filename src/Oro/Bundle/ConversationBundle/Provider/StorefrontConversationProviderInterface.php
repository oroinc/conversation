<?php

namespace Oro\Bundle\ConversationBundle\Provider;

/**
 * Provider that returns storefront routes where the add conversation button should be added
 * and returns link to the source entity.
 */
interface StorefrontConversationProviderInterface
{
    public function getAllowedRoutes(): array;
    public function getSourceUrl(string $sourceClassName, int $sourceId): string;
}
