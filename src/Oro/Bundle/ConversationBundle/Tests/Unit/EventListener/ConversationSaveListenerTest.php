<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\ConversationBundle\EventListener\ConversationSaveListener;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\ConversationBundle\Tests\Unit\Fixture\ConversationParticipantExtended;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\OrganizationBundle\Entity\Organization;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConversationSaveListenerTest extends TestCase
{
    use EntityTrait;

    private EntityRoutingHelper&MockObject $entityRoutingHelper;
    private ActivityManager&MockObject $activityManager;
    private ConversationManager&MockObject $conversationManager;
    private ConversationParticipantManager&MockObject $participantManager;
    private WebSocketSendProcessor&MockObject $webSocketSendProcessor;
    private ConversationSaveListener $listener;

    #[\Override]
    protected function setUp(): void
    {
        $this->entityRoutingHelper = $this->createMock(EntityRoutingHelper::class);
        $this->activityManager = $this->createMock(ActivityManager::class);
        $this->conversationManager = $this->createMock(ConversationManager::class);
        $this->participantManager = $this->createMock(ConversationParticipantManager::class);
        $this->webSocketSendProcessor = $this->createMock(WebSocketSendProcessor::class);

        $this->listener = new ConversationSaveListener(
            $this->entityRoutingHelper,
            $this->activityManager,
            $this->conversationManager,
            $this->participantManager,
            $this->webSocketSendProcessor
        );
    }

    public function testPrePersistOnExistingEntity(): void
    {
        $conversation = $this->getEntity(Conversation::class, ['id' => 1]);

        $this->activityManager->expects(self::never())
            ->method('addActivityTargets');

        $this->listener->prePersist($conversation);
        self::assertNull($conversation->getName());
        self::assertNull($conversation->getCustomer());
    }

    public function testPrePersist(): void
    {
        $owner = new User();
        $source = new \stdClass();
        $customerUser = new CustomerUser();
        $customer = new Customer();
        $customerUser->setCustomer($customer);

        $conversation = new Conversation();
        $conversation->setSourceEntityClass(\stdClass::class);
        $conversation->setSourceEntityId(1);
        $conversation->setOwner($owner);
        $conversation->setCustomerUser($customerUser);

        $this->entityRoutingHelper->expects(self::once())
            ->method('getEntity')
            ->willReturn($source);

        $this->conversationManager->expects(self::once())
            ->method('getConversationName')
            ->with($source)
            ->willReturn('source_name');

        $this->conversationManager->expects(self::once())
            ->method('ensureConversationHaveStatus')
            ->with($conversation);

        $this->activityManager->expects(self::once())
            ->method('addActivityTargets')
            ->with($conversation, [$owner, $source, $customerUser]);

        $this->participantManager->expects(self::exactly(2))
            ->method('getOrCreateParticipantObjectForConversation')
            ->withConsecutive(
                [$conversation, $owner],
                [$conversation, $customerUser]
            );

        $this->listener->prePersist($conversation);

        self::assertEquals('source_name', $conversation->getName());
        self::assertEquals($conversation->getCustomer(), $conversation->getCustomer());
    }

    public function testOnFlush(): void
    {
        $message1 = new ConversationMessage();
        $user11 = new User();
        $user11->setId(11);
        $organization1 = new Organization();
        $organization1->setId(1);
        $participant11 = new ConversationParticipantExtended();
        $participant11->setConversationParticipantTarget($user11);
        $participant12 = new ConversationParticipantExtended();
        $participant12->setConversationParticipantTarget(new CustomerUser());
        $conversation1 = new Conversation();
        $conversation1->setOrganization($organization1);
        $conversation1->addParticipant($participant11);
        $conversation1->addParticipant($participant12);
        $conversation1->addMessage($message1);

        $message2 = new ConversationMessage();
        $user21 = new User();
        $user21->setId(21);
        $organization2 = new Organization();
        $organization2->setId(2);
        $participant21 = new ConversationParticipantExtended();
        $participant21->setConversationParticipantTarget($user21);
        $participant22 = new ConversationParticipantExtended();
        $participant22->setConversationParticipantTarget(new CustomerUser());
        $conversation2 = new Conversation();
        $conversation2->setOrganization($organization2);
        $conversation2->addParticipant($participant21);
        $conversation2->addParticipant($participant22);
        $conversation2->addMessage($message2);

        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManagerInterface::class);

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);
        $uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new \stdClass(), $message1, $message2]);

        $this->webSocketSendProcessor->expects(self::once())
            ->method('send')
            ->with([
                11 => [1],
                21 => [2]
            ]);

        $this->listener->onFlush(new OnFlushEventArgs($em));
    }

    public function testOnFlushWithGlobalOrganization(): void
    {
        if (!class_exists('Oro\Bundle\OrganizationProBundle\Helper\OrganizationProHelper')) {
            $this->markTestSkipped('Test can be executed only on enterprise edition.');
        }

        $message1 = new ConversationMessage();
        $user11 = new User();
        $user11->setId(11);
        $organization1 = new Organization();
        $organization1->setId(1);
        $participant11 = new ConversationParticipantExtended();
        $participant11->setConversationParticipantTarget($user11);
        $participant12 = new ConversationParticipantExtended();
        $participant12->setConversationParticipantTarget(new CustomerUser());
        $conversation1 = new Conversation();
        $conversation1->setOrganization($organization1);
        $conversation1->addParticipant($participant11);
        $conversation1->addParticipant($participant12);
        $conversation1->addMessage($message1);

        $message2 = new ConversationMessage();
        $user21 = new User();
        $user21->setId(21);
        $organization2 = new Organization();
        $organization2->setId(2);
        $participant21 = new ConversationParticipantExtended();
        $participant21->setConversationParticipantTarget($user21);
        $participant22 = new ConversationParticipantExtended();
        $participant22->setConversationParticipantTarget(new CustomerUser());
        $conversation2 = new Conversation();
        $conversation2->setOrganization($organization2);
        $conversation2->addParticipant($participant21);
        $conversation2->addParticipant($participant22);
        $conversation2->addMessage($message2);

        $uow = $this->createMock(UnitOfWork::class);
        $em = $this->createMock(EntityManagerInterface::class);
        $organizationHelper = $this->createMock('Oro\Bundle\OrganizationProBundle\Helper\OrganizationProHelper');
        $organizationHelper->expects(self::exactly(2))
            ->method('isGlobalOrganizationExists')
            ->willReturn(true);
        $organizationHelper->expects(self::exactly(2))
            ->method('getGlobalOrganizationId')
            ->willReturn(30);

        $em->expects(self::once())
            ->method('getUnitOfWork')
            ->willReturn($uow);
        $uow->expects(self::once())
            ->method('getScheduledEntityInsertions')
            ->willReturn([new \stdClass(), $message1, $message2]);

        $this->webSocketSendProcessor->expects(self::once())
            ->method('send')
            ->with([
                11 => [1, 30],
                21 => [2, 30]
            ]);

        $event = new OnFlushEventArgs($em);

        $listener = new ConversationSaveListener(
            $this->entityRoutingHelper,
            $this->activityManager,
            $this->conversationManager,
            $this->participantManager,
            $this->webSocketSendProcessor,
            $organizationHelper
        );
        $listener->onFlush($event);
    }
}
