<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;

class ConversationMessageType extends AbstractEnumValue implements ExtendEntityInterface
{
    use ExtendEntityTrait;
}
