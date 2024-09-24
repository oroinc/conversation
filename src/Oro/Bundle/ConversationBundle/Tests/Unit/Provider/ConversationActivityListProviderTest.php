<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Provider;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\ActivityBundle\Tools\ActivityAssociationHelper;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Entity\ActivityOwner;
use Oro\Bundle\ChannelCRMProBundle\Tests\Unit\Fixtures\Organization;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Provider\ConversationActivityListProvider;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationExtended;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationStatus;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConversationActivityListProviderTest extends TestCase
{
    private DoctrineHelper|MockObject $doctrineHelper;
    private ActivityAssociationHelper $activityAssociationHelper;

    private ConversationActivityListProvider $provider;

    #[\Override]
    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->activityAssociationHelper = $this->createMock(ActivityAssociationHelper::class);
        $this->provider = new ConversationActivityListProvider(
            $this->doctrineHelper,
            $this->activityAssociationHelper,
        );
    }

    public function testIsApplicableTarget(): void
    {
        $this->activityAssociationHelper->expects(self::once())
            ->method('isActivityAssociationEnabled')
            ->with(User::class, Conversation::class, true)
            ->willReturn(true);

        self::assertTrue($this->provider->isApplicableTarget(User::class, true));
    }

    public function testGetSubject(): void
    {
        $conversation = new Conversation();
        $conversation->setName('Test conversation');

        self::assertEquals('Test conversation', $this->provider->getSubject($conversation));
    }

    public function testGetDescription(): void
    {
        self::assertEquals('', $this->provider->getDescription(new Conversation()));
    }

    public function testGetOwner(): void
    {
        $owner = new User();
        $conversation = new Conversation();
        $conversation->setOwner($owner);

        self::assertEquals($owner, $this->provider->getOwner($conversation));
    }

    public function testGetCreatedAt(): void
    {
        $created = new \DateTime();
        $conversation = new Conversation();
        $conversation->setCreatedAt($created);

        self::assertEquals($created, $this->provider->getCreatedAt($conversation));
    }

    public function testGetUpdatedAt(): void
    {
        $updated = new \DateTime();
        $conversation = new Conversation();
        $conversation->setUpdatedAt($updated);

        self::assertEquals($updated, $this->provider->getUpdatedAt($conversation));
    }

    public function testGetData(): void
    {
        $status = new ConversationStatus('conversation_status_code', 'Open', 1);
        $conversation = new ConversationExtended();
        $conversation->setStatus($status);

        $activityList = new ActivityList();
        $activityList->setRelatedActivityClass(Conversation::class);
        $activityList->setRelatedActivityId(145);

        $repo = $this->createMock(EntityRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Conversation::class)
            ->willReturn($repo);

        $repo->expects(self::once())
            ->method('find')
            ->with(145)
            ->willReturn($conversation);

        self::assertEquals(
            [
                'statusId' => 'conversation_status_code.1',
                'statusName' => 'Open',
            ],
            $this->provider->getData($activityList)
        );
    }

    public function testGetDataWithoutStatus(): void
    {
        $conversation = new ConversationExtended();
        $activityList = new ActivityList();
        $activityList->setRelatedActivityClass(Conversation::class);
        $activityList->setRelatedActivityId(145);

        $repo = $this->createMock(EntityRepository::class);
        $this->doctrineHelper->expects(self::once())
            ->method('getEntityRepository')
            ->with(Conversation::class)
            ->willReturn($repo);

        $repo->expects(self::once())
            ->method('find')
            ->with(145)
            ->willReturn($conversation);

        self::assertEquals(
            [
                'statusId' => null,
                'statusName' => null,
            ],
            $this->provider->getData($activityList)
        );
    }

    public function testGetOrganization(): void
    {
        $organization = new Organization();
        $entity = new Conversation();
        $entity->setOrganization($organization);

        self::assertEquals($organization, $this->provider->getOrganization($entity));
    }

    public function testGetTemplate(): void
    {
        self::assertEquals(
            '@OroConversation/Conversation/js/activityItemTemplate.html.twig',
            $this->provider->getTemplate()
        );
    }

    public function testGetRoutes(): void
    {
        self::assertEquals(
            [
                'itemView'   => 'oro_conversation_widget_info',
                'itemEdit'   => 'oro_conversation_update'
            ],
            $this->provider->getRoutes(new Conversation())
        );
    }

    public function testGetActivityId(): void
    {
        $entity = new Conversation();
        $this->doctrineHelper->expects($this->any())
            ->method('getSingleEntityIdentifier')
            ->with($entity)
            ->willReturn(555);

        self::assertEquals(555, $this->provider->getActivityId($entity));
    }

    public function testIsApplicableOnNonApplicableObject(): void
    {
        self::assertFalse($this->provider->isApplicable(new \stdClass()));
    }

    public function testIsApplicableOnNonApplicableClassName(): void
    {
        self::assertFalse($this->provider->isApplicable(\stdClass::class));
    }

    public function testIsApplicableOnApplicableObject(): void
    {
        self::assertTrue($this->provider->isApplicable(new Conversation()));
    }

    public function testIsApplicableOnApplicableClassName(): void
    {
        self::assertTrue($this->provider->isApplicable(Conversation::class));
    }

    public function testGetTargetEntities(): void
    {
        $entity = new Conversation();

        self::assertEquals($entity->getActivityTargets(), $this->provider->getTargetEntities($entity));
    }

    public function testGetActivityOwners(): void
    {
        $owner = new User();
        $organization = new Organization();

        $conversation = new Conversation();
        $conversation->setOwner($owner);
        $conversation->setOrganization($organization);

        $activityList = new ActivityList();

        $expectedActivityOwner = new ActivityOwner();
        $expectedActivityOwner->setActivity($activityList);
        $expectedActivityOwner->setOrganization($organization);
        $expectedActivityOwner->setUser($owner);

        self::assertEquals(
            [$expectedActivityOwner],
            $this->provider->getActivityOwners($conversation, $activityList)
        );
    }

    public function testIsActivityListApplicable(): void
    {
        $activityList = new ActivityList();

        self::assertTrue($this->provider->isActivityListApplicable($activityList));
    }
}
