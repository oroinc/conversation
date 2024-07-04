<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;

class ConversationMessageExtended extends ConversationMessage
{
    private ?AbstractEnumValue $type = null;

    public function getType(): ?AbstractEnumValue
    {
        return $this->type;
    }

    public function setType(AbstractEnumValue $type): void
    {
        $this->type = $type;
    }
}
