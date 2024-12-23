<?php

namespace Oro\Bundle\ConversationBundle\Twig;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Model\WebSocket\WebSocketSendProcessor;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessor;
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
    public function __construct(private ContainerInterface $container)
    {
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
        return $this->container->get('oro_conversation.participant_info.provider')->getTypeString($participant);
    }

    public function getEntityType(object $entity): string
    {
        return $this->container->get('oro_conversation.helper.entity_config_helper')->getLabel($entity);
    }

    /**
     * Return unique identificator for websocket event. This identification
     * is used in notification widget to show message about new conversation messages
     */
    public function getConversationWSChannel(): string
    {
        $tokenAccessor = $this->container->get('oro_security.token_accessor');
        $currentUser = $tokenAccessor->getUser();
        if (!$currentUser || !$currentUser instanceof User) {
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
            'oro_conversation.participant_info.provider' => ParticipantInfoProvider::class,
            'oro_conversation.helper.entity_config_helper' => EntityConfigHelper::class,
            'oro_security.token_accessor' => TokenAccessor::class,
        ];
    }
}
