<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Fixture;

use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityInterface;

class ConversationStatus extends EnumOption implements ExtendEntityInterface
{
    use \Oro\Bundle\EntityExtendBundle\Entity\ExtendEntityTrait;
}
