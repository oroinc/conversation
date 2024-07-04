<?php

namespace Oro\Bundle\ConversationBundle\Twig;

use Oro\Bundle\ConversationBundle\Helper\EntityConfigHelper;
use Oro\Bundle\ConversationBundle\Participant\ParticipantInfoProvider;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Provides the following Twig filters:
 *   - oro_conversation_participant_type
 *   - oro_conversation_entity_type
 */
class ConversationExtension extends AbstractExtension implements ServiceSubscriberInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    #[\Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('oro_conversation_participant_type', [$this, 'getParticipantType']),
            new TwigFilter('oro_conversation_entity_type', [$this, 'getEntityType'])
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

    #[\Override]
    public static function getSubscribedServices(): array
    {
        return [
            'oro_conversation.participant_info.provider' => ParticipantInfoProvider::class,
            'oro_conversation.helper.entity_config_helper' => EntityConfigHelper::class
        ];
    }
}
