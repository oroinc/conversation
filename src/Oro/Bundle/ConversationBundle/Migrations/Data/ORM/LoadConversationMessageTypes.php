<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load conversation message types data.
 */
class LoadConversationMessageTypes extends AbstractEnumFixture
{
    #[\Override]
    protected function getData(): array
    {
        return [
            ConversationMessage::TYPE_SYSTEM => 'System',
            ConversationMessage::TYPE_TEXT => 'Text',
        ];
    }

    #[\Override]
    protected function getDefaultValue(): ?string
    {
        return ConversationMessage::TYPE_SYSTEM;
    }

    #[\Override]
    protected function getEnumCode(): string
    {
        return ConversationMessage::TYPE_CODE;
    }
}
