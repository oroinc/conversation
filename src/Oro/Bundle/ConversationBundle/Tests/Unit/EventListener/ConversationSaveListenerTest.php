<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\EventListener;

use Oro\Bundle\ActivityBundle\Manager\ActivityManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\EventListener\ConversationSaveListener;
use Oro\Bundle\ConversationBundle\Manager\ConversationManager;
use Oro\Bundle\ConversationBundle\Manager\ConversationParticipantManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\EntityBundle\Tools\EntityRoutingHelper;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConversationSaveListenerTest extends TestCase
{
    use EntityTrait;

    private EntityRoutingHelper|MockObject $entityRoutingHelper;
    private ActivityManager|MockObject $activityManager;
    private ConversationManager|MockObject $conversationManager;
    private ConversationParticipantManager|MockObject $participantManager;

    private ConversationSaveListener $listener;

    protected function setUp(): void
    {
        $this->entityRoutingHelper = $this->createMock(EntityRoutingHelper::class);
        $this->activityManager = $this->createMock(ActivityManager::class);
        $this->conversationManager = $this->createMock(ConversationManager::class);
        $this->participantManager = $this->createMock(ConversationParticipantManager::class);

        $this->listener = new ConversationSaveListener(
            $this->entityRoutingHelper,
            $this->activityManager,
            $this->conversationManager,
            $this->participantManager
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
}
