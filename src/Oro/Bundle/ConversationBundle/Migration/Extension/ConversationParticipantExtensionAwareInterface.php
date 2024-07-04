<?php

namespace Oro\Bundle\ConversationBundle\Migration\Extension;

/**
 * The interface should be implemented by migrations that depends on {@see ConversationParticipantExtension}.
 */
interface ConversationParticipantExtensionAwareInterface
{
    public function setConversationParticipantExtension(
        ConversationParticipantExtension $conversationParticipantExtension
    ): void;
}
