<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\Entity\ConversationParticipant;
use Oro\Bundle\ConversationBundle\EventListener\ParticipantLastMessageListener;
use PHPUnit\Framework\TestCase;

class ParticipantLastMessageListenerTest extends TestCase
{
    public function testOnFlush(): void
    {
        $participant = new ConversationParticipant();
        $message = new ConversationMessage();
        $message->setIndex(3);
        $participant->setLastReadMessage($message);

        $em = $this->createMock(EntityManagerInterface::class);
        $uow = $this->createMock(UnitOfWork::class);

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);

        $uow->expects(self::once())
            ->method('getScheduledEntityUpdates')
            ->willReturn([$participant]);

        $uow->expects(self::once())
            ->method('getEntityChangeSet')
            ->with($participant)
            ->willReturn(['lastReadMessage' => []]);

        $event = new OnFlushEventArgs($em);

        $listener = new ParticipantLastMessageListener();
        $listener->onFlush($event);

        self::assertEquals(3, $participant->getLastReadMessageIndex());
        self::assertInstanceOf(\DateTime::class, $participant->getLastReadDate());
    }
}
