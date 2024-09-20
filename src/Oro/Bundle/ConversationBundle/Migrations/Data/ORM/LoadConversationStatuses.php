<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load conversation statuses data.
 */
class LoadConversationStatuses extends AbstractEnumFixture
{
    protected function getData(): array
    {
        return [
            Conversation::STATUS_ACTIVE => 'Active',
            Conversation::STATUS_INACTIVE => 'Inactive',
            Conversation::STATUS_CLOSED => 'Closed',
        ];
    }

    protected function getDefaultValue(): ?string
    {
        return Conversation::STATUS_ACTIVE;
    }

    protected function getEnumCode(): string
    {
        return Conversation::STATUS_CODE;
    }
}
