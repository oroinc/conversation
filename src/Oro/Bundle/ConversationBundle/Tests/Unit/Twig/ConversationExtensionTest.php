<?php

namespace Oro\Bundle\ConversationBundle\Tests\Unit\Twig;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\ConversationBundle\Twig\ConversationExtension;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Component\Testing\Unit\TwigExtensionTestCaseTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\TwigFunction;

class ConversationExtensionTest extends TestCase
{
    use TwigExtensionTestCaseTrait;

    private ParticipantInfoProvider&MockObject $participantInfoProvider;
    private EntityConfigHelper&MockObject $entityConfigHelper;
    private TokenAccessorInterface&MockObject $tokenAccessor;
    private ConversationExtension $extension;

    #[\Override]
    protected function setUp(): void
    {
        $this->participantInfoProvider = $this->createMock(ParticipantInfoProvider::class);
        $this->entityConfigHelper = $this->createMock(EntityConfigHelper::class);
        $this->tokenAccessor = $this->createMock(TokenAccessorInterface::class);

        $container = self::getContainerBuilder()
            ->add(ParticipantInfoProvider::class, $this->participantInfoProvider)
            ->add(EntityConfigHelper::class, $this->entityConfigHelper)
            ->add(TokenAccessorInterface::class, $this->tokenAccessor)
            ->getContainer($this);

        $this->extension = new ConversationExtension($container);
    }

    public function testGetFilters(): void
    {
        $result = $this->extension->getFilters();
        $this->assertCount(2, $result);
        self::assertEquals('oro_conversation_participant_type', $result[0]->getName());
        self::assertEquals('oro_conversation_entity_type', $result[1]->getName());
    }

    public function testGetFunctions(): void
    {
        self::assertEquals(
            [new TwigFunction('oro_get_conversation_ws_event', [$this->extension, 'getConversationWSChannel'])],
            $this->extension->getFunctions()
        );
    }

    public function testGetParticipantType(): void
    {
        $participant = new User();

        $this->participantInfoProvider->expects(self::once())
            ->method('getTypeString')
            ->with($participant)
            ->willReturn('User');

        self::assertEquals(
            'User',
            self::callTwigFilter($this->extension, 'oro_conversation_participant_type', [$participant])
        );
    }

    public function testGetEntityType(): void
    {
        $entity = new User();

        $this->entityConfigHelper->expects(self::once())
            ->method('getLabel')
            ->with($entity)
            ->willReturn('User1');

        self::assertEquals(
            'User1',
            self::callTwigFilter($this->extension, 'oro_conversation_entity_type', [$entity])
        );
    }

    public function testGetConversationWSChannelWithoutUserInToken(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(null);

        self::assertEmpty(
            self::callTwigFunction($this->extension, 'oro_get_conversation_ws_event', [])
        );
    }

    public function testGetConversationWSChannelWithNonUserInToken(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(new \stdClass());

        self::assertEmpty(
            self::callTwigFunction($this->extension, 'oro_get_conversation_ws_event', [])
        );
    }

    public function testGetConversationWSChannel(): void
    {
        $this->tokenAccessor->expects(self::once())
            ->method('getUser')
            ->willReturn(new User());
        $this->tokenAccessor->expects(self::once())
            ->method('getUserId')
            ->willReturn(3);
        $this->tokenAccessor->expects(self::once())
            ->method('getOrganizationId')
            ->willReturn(22);

        self::assertEquals(
            'oro/conversation_event/3/22',
            self::callTwigFunction($this->extension, 'oro_get_conversation_ws_event', [])
        );
    }
}
