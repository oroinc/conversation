<?php

namespace Oro\Bundle\ConversationBundle\Migration\Extension;

/**
 * The trait that can be used by migrations that implement {@see ConversationParticipantExtensionAwareInterface}.
 */
trait ConversationParticipantExtensionAwareTrait
{
    private ConversationParticipantExtension $conversationParticipantExtension;

    #[\Override]
    public function setConversationParticipantExtension(
        ConversationParticipantExtension $conversationParticipantExtension
    ): void {
        $this->conversationParticipantExtension = $conversationParticipantExtension;
    }
}
