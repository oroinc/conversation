<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ApiBundle\Provider\EntityAliasResolverRegistry;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\CustomerBundle\Owner\Metadata\FrontendOwnershipMetadata;
use Oro\Bundle\EntityBundle\Provider\EntityNameProviderInterface;
use Oro\Bundle\EntityBundle\Provider\EntityNameResolver;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\EntityExtendBundle\Entity\EnumOption;
use Oro\Bundle\SecurityBundle\Owner\Metadata\OwnershipMetadataProviderInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ConversationManagerTest extends TestCase
{
    private EntityRoutingHelper&MockObject $entityRoutingHelper;
    private EntityNameResolver&MockObject $entityNameResolver;
    private EntityConfigHelper&MockObject $entityConfigHelper;
    private OwnershipMetadataProviderInterface&MockObject $metadataProvider;
    private ManagerRegistry&MockObject $doctrine;
    private EntityAliasResolverRegistry&MockObject $aliasResolverRegistry;

    private ConversationManager $manager;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityRoutingHelper = $this->createMock(EntityRoutingHelper::class);
        $this->entityNameResolver = $this->createMock(EntityNameResolver::class);
        $this->entityConfigHelper = $this->createMock(EntityConfigHelper::class);
        $this->metadataProvider = $this->createMock(OwnershipMetadataProviderInterface::class);
        $this->doctrine = $this->createMock(ManagerRegistry::class);
        $this->aliasResolverRegistry = $this->createMock(EntityAliasResolverRegistry::class);

        $this->manager = new ConversationManager(
            $this->entityRoutingHelper,
            $this->entityNameResolver,
            $this->entityConfigHelper,
            $this->metadataProvider,
            PropertyAccess::createPropertyAccessor(),
            $this->doctrine,
            $this->aliasResolverRegistry
        );
    }

    public function testCreateConversationWithoutSourceEntityClassAndSourceEntityId(): void
    {
        $status = new EnumOption('conversation_status', 'active', 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(EnumOption::class)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('find')
            ->with(EnumOption::class, 'conversation_status.active')
            ->willReturn($status);

        $result = $this->manager->createConversation();
        self::assertNull($result->getName());
        self::assertNull($result->getCustomerUser());
        self::assertNull($result->getCustomer());
        self::assertSame($status, $result->getStatus());
    }

    public function testCreateConversationWithoutSourceEntity(): void
    {
        $sourceEntityClass = \stdClass::class;
        $sourceEntityId = 45;

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntity')
            ->with(\stdClass::class, 45)
            ->willReturn(null);

        $status = new EnumOption('conversation_status', 'active', 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(EnumOption::class)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('find')
            ->with(EnumOption::class, 'conversation_status.active')
            ->willReturn($status);

        $result = $this->manager->createConversation($sourceEntityClass, $sourceEntityId);
        self::assertNull($result->getName());
        self::assertNull($result->getCustomerUser());
        self::assertNull($result->getCustomer());
        self::assertSame($status, $result->getStatus());
    }

    public function testCreateConversationWithNonAclProtectedEntity(): void
    {
        $sourceEntityClass = \stdClass::class;
        $sourceEntityId = 45;

        $entity = new \stdClass();

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
            ->with($entity, EntityNameProviderInterface::SHORT)
            ->willReturn('Entity_label');

        $this->metadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($sourceEntityClass)
            ->willReturn(new FrontendOwnershipMetadata());

        $status = new EnumOption('conversation_status', 'active', 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(EnumOption::class)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('find')
            ->with(EnumOption::class, 'conversation_status.active')
            ->willReturn($status);

        $result = $this->manager->createConversation($sourceEntityClass, $sourceEntityId);

        self::assertInstanceOf(Conversation::class, $result);
        self::assertEquals('Test_entity Entity_label', $result->getName());
        self::assertNull($result->getCustomerUser());
        self::assertNull($result->getCustomer());
        self::assertSame($status, $result->getStatus());
    }

    public function testCreateConversationWithAclProtectedEntity(): void
    {
        $sourceEntityClass = \stdClass::class;
        $sourceEntityId = 45;

        $entity = new \stdClass();
        $customer = new Customer();
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);
        $entity->customerUser = $customerUser;

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
            ->with($entity, EntityNameProviderInterface::SHORT)
            ->willReturn('Entity_label');

        $this->metadataProvider->expects(self::once())
            ->method('getMetadata')
            ->with($sourceEntityClass)
            ->willReturn(new FrontendOwnershipMetadata(
                'FRONTEND_USER',
                'customerUser',
                'customerUserId'
            ));

        $status = new EnumOption('conversation_status', 'active', 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(EnumOption::class)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('find')
            ->with(EnumOption::class, 'conversation_status.active')
            ->willReturn($status);

        $result = $this->manager->createConversation($sourceEntityClass, $sourceEntityId);

        self::assertInstanceOf(Conversation::class, $result);
        self::assertEquals('Test_entity Entity_label', $result->getName());
        self::assertSame($customerUser, $result->getCustomerUser());
        self::assertSame($customer, $result->getCustomer());
        self::assertSame($status, $result->getStatus());
    }

    public function testCreateConversationWithCustomerUserSource(): void
    {
        $sourceEntityClass = CustomerUser::class;
        $sourceEntityId = 22;

        $customer = new Customer();
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($customer);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntity')
            ->with(CustomerUser::class, 22)
            ->willReturn($customerUser);

        $this->entityConfigHelper->expects(self::once())
            ->method('getLabel')
            ->with($customerUser)
            ->willReturn('Test_entity');

        $this->entityNameResolver->expects(self::once())
            ->method('getName')
            ->with($customerUser, EntityNameProviderInterface::SHORT)
            ->willReturn('Entity_label');

        $this->metadataProvider->expects(self::never())
            ->method('getMetadata');

        $status = new EnumOption('conversation_status', 'active', 1);
        $em = $this->createMock(EntityManagerInterface::class);
        $this->doctrine->expects(self::once())
            ->method('getManagerForClass')
            ->with(EnumOption::class)
            ->willReturn($em);
        $em->expects(self::once())
            ->method('find')
            ->with(EnumOption::class, 'conversation_status.active')
            ->willReturn($status);

        $result = $this->manager->createConversation($sourceEntityClass, $sourceEntityId);

        self::assertInstanceOf(Conversation::class, $result);
        self::assertEquals('Test_entity Entity_label', $result->getName());
        self::assertSame($customerUser, $result->getCustomerUser());
        self::assertSame($customer, $result->getCustomer());
        self::assertSame($status, $result->getStatus());
    }

    public function testGetConversationName(): void
    {
        $conversation = new Conversation();

        $this->entityConfigHelper->expects(self::once())
            ->method('getLabel')
            ->with($conversation)
            ->willReturn('Label');

        $this->entityNameResolver->expects(self::once())
            ->method('getName')
            ->with($conversation, EntityNameProviderInterface::SHORT)
            ->willReturn('Entity_label');

        self::assertEquals('Label Entity_label', $this->manager->getConversationName($conversation));
    }
}
