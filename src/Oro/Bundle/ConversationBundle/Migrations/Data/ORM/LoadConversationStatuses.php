<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load conversation statuses data.
 */
class LoadConversationStatuses extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            Conversation::STATUS_ACTIVE => 'Active',
            Conversation::STATUS_CLOSED => 'Closed',
        ];
    }

    #[\Override]
    protected function getDefaultValue(): ?string
    {
        return Conversation::STATUS_ACTIVE;
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return Conversation::STATUS_CODE;
    }
}
