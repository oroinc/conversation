<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EntityExtend;

use Oro\Bundle\ActivityListBundle\EntityExtend\ActivityListEntityFieldExtension;
use Oro\Bundle\ActivityListBundle\Tools\ActivityListEntityConfigDumperExtension;
use Oro\Bundle\EntityExtendBundle\EntityExtend\EntityFieldProcessTransport;
use Oro\Bundle\EntityExtendBundle\Extend\RelationType;
use PHPUnit\Framework\TestCase;

class ActivityListEntityFieldExtensionTest extends TestCase
{
    private ActivityListEntityFieldExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->extension = new ActivityListEntityFieldExtension();
    }

    public function testIsApplicableWithNotSupportedClass(): void
    {
        $transport = new EntityFieldProcessTransport();
        $transport->setClass(\stdClass::class);

        self::assertFalse($this->extension->isApplicable($transport));
    }

    public function testIsApplicableWithNotSupportedAssociationKind(): void
    {
        $transport = new EntityFieldProcessTransport();
        $transport->setClass(ActivityListEntityConfigDumperExtension::ENTITY_CLASS);
        $transport->setName('test');

        self::assertFalse($this->extension->isApplicable($transport));
    }

    public function testIsApplicable(): void
    {
        $transport = new EntityFieldProcessTransport();
        $transport->setClass(ActivityListEntityConfigDumperExtension::ENTITY_CLASS);
        $transport->setName(ActivityListEntityConfigDumperExtension::ASSOCIATION_KIND);

        self::assertFalse($this->extension->isApplicable($transport));
    }

    public function testGetRelationKind(): void
    {
        self::assertEquals(
            ActivityListEntityConfigDumperExtension::ASSOCIATION_KIND,
            $this->extension->getRelationKind()
        );
    }

    public function testGetRelationType(): void
    {
        self::assertEquals(RelationType::MANY_TO_MANY, $this->extension->getRelationType());
    }
}
