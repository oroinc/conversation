<?php

namespace Oro\Bundle\ConversationBundle\Tests\Functional\Api\DataFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ConversationBundle\Entity\Conversation;
use Oro\Bundle\ConversationBundle\Entity\ConversationMessage;
use Oro\Bundle\OrderBundle\Tests\Functional\DataFixtures\LoadOrders;
use Oro\Bundle\TestFrameworkBundle\Tests\Functional\DataFixtures\LoadOrganization;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadMessages extends AbstractFixture implements DependentFixtureInterface, ContainerAwareInterface
{
    private ContainerInterface $container;

    #[\Override]
    public function setContainer(?ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    #[\Override]
    public function getDependencies()
    {
        return [
            LoadOrganization::class,
            LoadOrders::class,
            LoadConversations::class
        ];
    }

    #[\Override]
    public function load(ObjectManager $manager)
    {
        $conversationParticipantManager = $this->container->get('oro_conversation.manager.conversation_participant');

        for ($i = 1; $i <= 10; $i++) {
            /** @var Conversation $conversation */
            $conversation = $this->getReference('conversation-' . $i);
            $user = $conversation->getOwner();
            $customerUser = $conversation->getCustomerUser();

            for ($j = 1; $j <= 10; $j++) {
                $participant = $conversationParticipantManager->getOrCreateParticipantObjectForConversation(
                    $conversation,
                    ($j % 2 == 0) ? $customerUser : $user
                );
                $participantReference = 'participant-'
                    . $i
                    . '-'
                    . $participant->getConversationParticipantTarget()->getUserIdentifier();
                if (!$this->hasReference($participantReference)) {
                    $this->addReference($participantReference, $participant);
                }

                $message = new ConversationMessage();
                $message->setConversation($conversation);
                $message->setBody('Message ' . $j);
                $message->setParticipant($participant);

                $this->addReference('conversationmessage-' . $i . '-' . $j, $message);

                $manager->persist($message);
            }
        }

        $manager->flush();
    }
}
