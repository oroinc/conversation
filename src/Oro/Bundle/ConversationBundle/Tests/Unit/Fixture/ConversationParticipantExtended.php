<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;

class ConversationParticipantExtended extends ConversationParticipant
{
    private object $target;

    public function getConversationParticipantTarget(): object
    {
        return $this->target;
    }

    public function setConversationParticipantTarget(object $value): void
    {
        $this->target = $value;
    }
}
