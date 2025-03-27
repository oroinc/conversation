<?php

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;

class LoadConversations extends AbstractFixture implements DependentFixtureInterface
{
    #[\Override]
    public function getDependencies(): array
    {
        return [
            LoadOrganization::class,
            LoadOrders::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager): void
    {
        $owner = $this->getReference('user');
        $organization = $this->getReference('organization');
        $customerUser = $this->getReference('grzegorz.brzeczyszczykiewicz@example.com');
        $customer = $customerUser->getCustomer();
        $source = $this->getReference('simple_order');

        for ($i = 1; $i <= 10; $i++) {
            $conversation = new Conversation();
            $conversation->setOwner($owner);
            $conversation->setOrganization($organization);
            $conversation->setCustomer($customer);
            $conversation->setCustomerUser($customerUser);
            $conversation->setSourceEntity($source::class, $source->getId());
            $conversation->setName('conversation-' . $i);

            $manager->persist($conversation);
            $this->addReference('conversation-' . $i, $conversation);
        }

        $manager->flush();
    }
}
