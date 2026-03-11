<?php

namespace Oro\Bundle\ConversationBundle\Participant;

use Doctrine\Common\Util\ClassUtils;
use Psr\Container\ContainerInterface;

/**
 * Provides information about given participant object.
 */
class ParticipantInfoProvider
{
    public const MESSAGE_POSITION_LEFT = 'left';
    public const MESSAGE_POSITION_RIGHT = 'right';

    public function __construct(
        private ContainerInterface $participantInfoProviders,
        private ParticipantInfoInterface $commonProvider
    ) {
    }

    public function getParticipantInfo(?object $participant): array
    {
        if (null === $participant) {
            return [
                'isOwnMessage' => false,
                'title' =>  '',
                'titleAcronym' =>  '',
                'avatarImage' =>  [],
                'avatarIcon' =>  '',
                'position' => ParticipantInfoProvider::MESSAGE_POSITION_LEFT,
                'type' =>  ''
            ];
        }

        $provider = $this->getParticipantProvider($participant);

        return [
            'isOwnMessage' => $provider->isItMe($participant),
            'title' =>  $provider->getTitle($participant),
            'titleAcronym' =>  $provider->getAcronym($participant),
            'avatarImage' =>  $provider->getAvatarImage($participant),
            'avatarIcon' =>  $provider->getAvatarIcon($participant),
            'position' => $provider->getPosition($participant),
            'type' =>  $provider->getTypeString($participant)
        ];
    }

    public function getTypeString(object $participant): string
    {
        return $this->getParticipantProvider($participant)->getTypeString($participant);
    }

    public function getTitle(object $participant): string
    {
        return $this->getParticipantProvider($participant)->getTitle($participant);
    }

    private function getParticipantProvider(object $participant): ParticipantInfoInterface
    {
        $participantClass = ClassUtils::getClass($participant);

        if (!$this->participantInfoProviders->has($participantClass)) {
            return $this->commonProvider;
        }

        return $this->participantInfoProviders->get($participantClass);
    }
}
