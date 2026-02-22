<?php

namespace Oro\Bundle\ConversationBundle\Twig;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;
use Oro\Bundle\UserBundle\Entity\User;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Provides the following Twig
 *   - filters:
 *      - oro_conversation_participant_type
 *      - oro_conversation_entity_type
 *   - functions:
 *      - oro_get_conversation_ws_event
 */
class ConversationExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('oro_conversation_participant_type', [$this, 'getParticipantType']),
            new TwigFilter('oro_conversation_entity_type', [$this, 'getEntityType'])
        ];
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('oro_get_conversation_ws_event', [$this, 'getConversationWSChannel'])
        ];
    }

    public function getParticipantType(object $participant): string
    {
        return $this->getParticipantInfoProvider()->getTypeString($participant);
    }

    public function getEntityType(object $entity): string
    {
        return $this->getEntityConfigHelper()->getLabel($entity);
    }

    /**
     * Return unique identifier for websocket event. This identification
     * is used in notification widget to show message about new conversation messages
     */
    public function getConversationWSChannel(): string
    {
        $tokenAccessor = $this->getTokenAccessor();
        $currentUser = $tokenAccessor->getUser();
        if (!$currentUser instanceof User) {
            return '';
        }

        return WebSocketSendProcessor::getUserTopic(
            $tokenAccessor->getUserId(),
            $tokenAccessor->getOrganizationId()
        );
    }

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            ParticipantInfoProvider::class,
            EntityConfigHelper::class,
            TokenAccessorInterface::class
        ];
    }

    private function getParticipantInfoProvider(): ParticipantInfoProvider
    {
        return $this->container->get(ParticipantInfoProvider::class);
    }

    private function getEntityConfigHelper(): EntityConfigHelper
    {
        return $this->container->get(EntityConfigHelper::class);
    }

    private function getTokenAccessor(): TokenAccessorInterface
    {
        return $this->container->get(TokenAccessorInterface::class);
    }
}
