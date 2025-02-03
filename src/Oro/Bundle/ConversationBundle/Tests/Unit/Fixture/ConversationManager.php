<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager as BaseConversationManager;

class ConversationManager extends BaseConversationManager
{
    #[\Override]
    protected function getNewConversation(): Conversation
    {
        return new ConversationExtended();
    }
}
