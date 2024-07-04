<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\EntityExtendBundle\Entity\AbstractEnumValue;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;

class ConversationStatus extends AbstractEnumValue implements ExtendEntityInterface
{
    use \Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
}
