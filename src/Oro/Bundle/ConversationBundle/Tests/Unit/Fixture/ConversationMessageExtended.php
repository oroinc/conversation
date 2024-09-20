<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOptionInterface;

class ConversationMessageExtended extends ConversationMessage
{
    private ?EnumOptionInterface $type = null;

    public function getType(): ?EnumOptionInterface
    {
        return $this->type;
    }

    public function setType(EnumOptionInterface $type): void
    {
        $this->type = $type;
    }
}
