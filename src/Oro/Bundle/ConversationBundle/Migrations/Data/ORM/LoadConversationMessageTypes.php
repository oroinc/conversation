<?php

namespace Oro\Bundle\ConversationBundle\Migrations\Data\ORM;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\EntityExtendBundle\Migration\Fixture\AbstractEnumFixture;

/**
 * Load conversation message types data.
 */
class LoadConversationMessageTypes extends AbstractEnumFixture
{
    protected function getData(): array
    {
        return [
            ConversationMessage::TYPE_SYSTEM => 'System',
            ConversationMessage::TYPE_TEXT => 'Text',
        ];
    }

    protected function getDefaultValue(): ?string
    {
        return ConversationMessage::TYPE_SYSTEM;
    }

    protected function getEnumCode(): string
    {
        return ConversationMessage::TYPE_CODE;
    }
}
