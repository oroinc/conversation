<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

class ConversationExtended extends Conversation
{
    private ?AbstractEnumValue $status = null;

    public function getStatus(): ?AbstractEnumValue
    {
        return $this->status;
    }

    public function setStatus(AbstractEnumValue $status): void
    {
        $this->status = $status;
    }
}
