<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Manager;

use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ConversationManagerTest extends TestCase
{
    private EntityRoutingHelper|MockObject $entityRoutingHelper;
    private RequestStack|MockObject $requestStack;
    private EntityNameResolver|MockObject $entityNameResolver;
    private EntityConfigHelper|MockObject $entityConfigHelper;

    private ConversationManager $manager;

    protected function setUp(): void
    {
        $this->entityRoutingHelper = $this->createMock(EntityRoutingHelper::class);
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->entityNameResolver = $this->createMock(EntityNameResolver::class);
        $this->entityConfigHelper = $this->createMock(EntityConfigHelper::class);

        $this->manager = new ConversationManager(
            $this->entityRoutingHelper,
            $this->requestStack,
            $this->entityNameResolver,
            $this->entityConfigHelper
        );
    }

    public function testCreateWithoutRequest(): void
    {
        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn(null);

        $result = $this->manager->create();

        self::assertInstanceOf(Conversation::class, $result);
        self::assertNull($result->getName());
    }

    public function testCreateWithoutParametersInRequest(): void
    {
        $request = new Request();

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntityClassName')
            ->with($request)
            ->willReturn(null);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntityId')
            ->with($request)
            ->willReturn(null);

        $result = $this->manager->create();

        self::assertInstanceOf(Conversation::class, $result);
        self::assertNull($result->getName());
    }

    public function testCreate(): void
    {
        $request = new Request();
        $entity = new \stdClass();

        $this->requestStack->expects(self::once())
            ->method('getCurrentRequest')
            ->willReturn($request);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntityClassName')
            ->with($request)
            ->willReturn(\stdClass::class);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntityId')
            ->with($request)
            ->willReturn(45);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntity')
            ->with(\stdClass::class, 45)
            ->willReturn($entity);

        $this->entityConfigHelper->expects(self::once())
            ->method('getLabel')
            ->with($entity)
            ->willReturn('Test_entity');

        $this->entityNameResolver->expects(self::once())
            ->method('getName')
            ->with($entity)
            ->willReturn('Entity_label');

        $result = $this->manager->create();

        self::assertInstanceOf(Conversation::class, $result);
        self::assertEquals('Test_entity Entity_label', $result->getName());
    }
}
