<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;

class ConversationExtended extends Conversation
{
    private ?EnumOptionInterface $status = null;

    public function getStatus(): ?EnumOptionInterface
    {
        return $this->status;
    }

    public function setStatus(EnumOptionInterface $status): void
    {
        $this->status = $status;
    }
}
