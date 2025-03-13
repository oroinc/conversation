<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\EventListener\SourceDeleteListener;
use Oro\Bundle\OrganizationBundle\Tests\Unit\Fixture\Entity\BusinessUnit;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;

class SourceDeleteListenerTest extends TestCase
{
    public function testSourceDeleteListener(): void
    {
        $conversation1 = new Conversation();
        $conversation1->setSourceEntity(User::class, 1);
        $conversation2 = new Conversation();
        $conversation2->setSourceEntity(User::class, 2);

        $activityManager = $this->createMock(ActivityManager::class);

        $em = $this->createMock(EntityManager::class);
        $repo = $this->createMock(EntityRepository::class);
        $uow = $this->createMock(UnitOfWork::class);

        $object1 = new User();
        $object1->setId(1);

        $object2 = new User();
        $object2->setId(2);

        $object3 = new BusinessUnit();
        $object3->setId(3);

        $activityManager->expects(self::once())
            ->method('getActivityTargets')
            ->with(Conversation::class)
            ->willReturn([User::class => 'user', \stdClass::class => 'std']);

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('getScheduledEntityDeletions')
            ->willReturn([$object1, $object2, $object3]);

        $uow->expects(self::exactly(2))
            ->method('scheduleForUpdate')
            ->withConsecutive([$conversation1], [$conversation2]);

        $em->expects(self::exactly(2))
            ->method('getRepository')
            ->willReturn($repo);

        $repo->expects(self::exactly(2))
            ->method('findBy')
            ->willReturnMap([
                [['sourceEntityClass' => User::class, 'sourceEntityId' => 1], null, null, null, [$conversation1]],
                [['sourceEntityClass' => User::class, 'sourceEntityId' => 2], null, null, null, [$conversation2]],
            ]);

        $event = new OnFlushEventArgs($em);
        $listener = new SourceDeleteListener($activityManager);
        $listener->onFlush($event);

        self::assertNull($conversation1->getSourceEntityClass());
        self::assertNull($conversation1->getSourceEntityId());
        self::assertNull($conversation2->getSourceEntityClass());
        self::assertNull($conversation2->getSourceEntityId());
    }
}
