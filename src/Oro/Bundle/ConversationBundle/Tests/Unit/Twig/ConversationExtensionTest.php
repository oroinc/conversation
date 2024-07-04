<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Twig;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\ConversationBundle\Twig\ConversationExtension;
use Oro\Bundle\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class ConversationExtensionTest extends TestCase
{
    private ContainerInterface $container;
    private ConversationExtension $extension;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
        $this->extension = new ConversationExtension($this->container);
    }

    public function testGetFilters(): void
    {
        $result = $this->extension->getFilters();
        $this->assertCount(2, $result);
        self::assertEquals('oro_conversation_participant_type', $result[0]->getName());
        self::assertEquals('oro_conversation_entity_type', $result[1]->getName());
    }

    public function testGetSubscribedServices(): void
    {
        self::assertEquals(
            [
                'oro_conversation.participant_info.provider' => ParticipantInfoProvider::class,
                'oro_conversation.helper.entity_config_helper' => EntityConfigHelper::class
            ],
            $this->extension->getSubscribedServices()
        );
    }

    public function testGetParticipantType(): void
    {
        $participant = new User();
        $provider = $this->createMock(ParticipantInfoProvider::class);

        $this->container->expects(self::once())
            ->method('get')
            ->with('oro_conversation.participant_info.provider')
            ->willReturn($provider);

        $provider->expects(self::once())
            ->method('getTypeString')
            ->with($participant)
            ->willReturn('User');

        self::assertEquals('User', $this->extension->getParticipantType($participant));
    }

    public function testGetEntityType(): void
    {
        $entity = new User();
        $helper = $this->createMock(EntityConfigHelper::class);

        $this->container->expects(self::once())
            ->method('get')
            ->with('oro_conversation.helper.entity_config_helper')
            ->willReturn($helper);

        $helper->expects(self::once())
            ->method('getLabel')
            ->with($entity)
            ->willReturn('User1');

        self::assertEquals('User1', $this->extension->getEntityType($entity));
    }
}
